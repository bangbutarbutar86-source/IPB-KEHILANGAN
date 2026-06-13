<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials['email'] = strtolower($credentials['email']);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        if ($user->is_active === false) {
            return back()->withErrors(['email' => 'Akun kamu sedang tidak aktif.'])->onlyInput('email');
        }

        if ($user->email_verified === false) {
            return back()->withErrors(['email' => 'Akun belum diverifikasi. Silakan registrasi ulang dan masukkan OTP yang dikirim ke email.'])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'admin') {
            $intended = session()->pull('url.intended');
            if ($intended && rtrim($intended, '/') !== rtrim(url('/'), '/')) {
                return redirect($intended);
            }

            return redirect('/admin/dashboard');
        }

        return redirect()->intended('/');
    }

    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $this->removeUnverifiedManualRegistration($request->email);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'regex:/^[^A-Z]+$/', 'max:255', 'unique:users,email'],
            'nim' => ['required', 'string', 'max:20'],
            'phone' => ['required', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.dns' => 'Domain email tidak valid. Gunakan email asli yang bisa menerima OTP.',
            'email.regex' => 'Email tidak boleh menggunakan huruf besar.',
        ]);

        $data['email'] = strtolower($data['email']);
        $otp = $this->generateOtp();

        session([
            'otp_email' => $data['email'],
            'otp_purpose' => 'register',
            'pending_register' => [
                'name' => $data['name'],
                'email' => $data['email'],
                'nim' => $data['nim'],
                'phone' => $data['phone'],
                'password_hash' => Hash::make($data['password']),
                'otp_hash' => Hash::make($otp),
                'otp_expires_at' => now()->addMinutes(5)->toIso8601String(),
            ],
        ]);

        $otpSent = $this->sendOtpEmail($data['email'], $data['name'], 'register', $otp);

        return redirect()->route('otp.show')
            ->with($otpSent ? 'success' : 'mail_error', $otpSent
                ? 'Kode OTP sudah dikirim. Data akun belum disimpan sebelum OTP benar.'
                : 'Kode OTP dibuat, tetapi email gagal dikirim. Data akun belum disimpan.');
    }

    public function showOtp()
    {
        if (session('otp_purpose') === 'register' && session('pending_register')) {
            return view('auth.otp');
        }

        if (!session('otp_user_id') || !session('otp_purpose')) {
            return redirect()->route('login')->withErrors(['email' => 'Sesi OTP tidak valid. Silakan mulai lagi.']);
        }

        return view('auth.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => ['required', 'string', 'size:6']]);

        if (session('otp_purpose') === 'register') {
            return $this->verifyRegisterOtp($request);
        }

        $user = User::find(session('otp_user_id'));
        $purpose = session('otp_purpose');

        if (!$user || !$purpose) {
            return redirect()->route('login')->withErrors(['otp' => 'Sesi OTP tidak valid. Silakan mulai lagi.']);
        }

        if (!$this->otpIsValid($user, $request->otp, $purpose)) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kedaluwarsa.']);
        }

        if ($purpose === 'reset_password') {
            session([
                'reset_user_id' => $user->id,
                'reset_email' => $user->email,
                'reset_otp_verified' => true,
            ]);
            $this->clearOtp($user);
            $this->forgetOtpSession();

            return redirect()->route('password.reset.form')
                ->with('success', 'OTP benar. Silakan buat password baru.');
        }

        return redirect()->route('login')->withErrors(['email' => 'Jenis OTP tidak valid.']);
    }

    public function resendOtp()
    {
        if (session('otp_purpose') === 'register' && session('pending_register')) {
            $pending = session('pending_register');
            $otp = $this->generateOtp();
            $pending['otp_hash'] = Hash::make($otp);
            $pending['otp_expires_at'] = now()->addMinutes(5)->toIso8601String();

            session(['pending_register' => $pending]);
            $otpSent = $this->sendOtpEmail($pending['email'], $pending['name'], 'register', $otp);

            return back()->with($otpSent ? 'success' : 'mail_error', $otpSent
                ? 'Kode OTP baru telah dikirim ke ' . $pending['email']
                : 'Kode OTP baru dibuat, tetapi email gagal dikirim.');
        }

        $user = User::find(session('otp_user_id'));
        $purpose = session('otp_purpose');

        if (!$user || !$purpose) {
            return redirect()->route('login')->withErrors(['email' => 'Sesi OTP tidak valid. Silakan mulai lagi.']);
        }

        $otpSent = $this->startOtpChallenge($user, $purpose);

        return back()->with($otpSent ? 'success' : 'mail_error', $otpSent
            ? 'Kode OTP baru telah dikirim ke ' . $user->email
            : 'Kode OTP baru dibuat, tetapi email gagal dikirim. Periksa konfigurasi SMTP.');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Login Google gagal, coba lagi.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'email_verified' => true,
                ]);
            }

            Auth::login($user);
            return redirect()->intended('/');
        }

        session([
            'google_id' => $googleUser->getId(),
            'google_name' => $googleUser->getName(),
            'google_email' => $googleUser->getEmail(),
            'google_photo' => $googleUser->getAvatar(),
        ]);

        return redirect()->route('google.complete');
    }

    public function showCompleteGoogle()
    {
        if (!session('google_email')) {
            return redirect()->route('login');
        }

        return view('auth.google-complete');
    }

    public function completeGoogleRegister(Request $request)
    {
        $request->validate([
            'nim' => ['required', 'string', 'max:20', 'unique:users,nim'],
            'phone' => ['required', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
        ]);

        $user = User::create([
            'name' => session('google_name'),
            'email' => session('google_email'),
            'google_id' => session('google_id'),
            'profile_photo' => session('google_photo'),
            'nim' => $request->nim,
            'phone' => $request->phone,
            'gender' => 'Laki-Laki',
            'password' => Hash::make(Str::random(24)),
            'role' => 'user',
            'auth_provider' => 'google',
            'email_verified' => true,
            'is_active' => true,
        ]);

        session()->forget(['google_id', 'google_name', 'google_email', 'google_photo']);
        Auth::login($user);

        return redirect()->intended('/');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email:rfc,dns'],
        ], [
            'email.dns' => 'Domain email tidak valid. Gunakan email yang terdaftar dan bisa menerima OTP.',
        ]);

        $user = User::where('email', strtolower($request->email))->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.'])->onlyInput('email');
        }

        $otpSent = $this->startOtpChallenge($user, 'reset_password');

        return redirect()->route('otp.show')
            ->with($otpSent ? 'success' : 'mail_error', $otpSent
                ? 'Kode OTP reset password telah dikirim ke email kamu.'
                : 'Kode OTP reset password dibuat, tetapi email gagal dikirim. Periksa konfigurasi SMTP.');
    }

    public function showResetPassword()
    {
        if (!session('reset_user_id') || !session('reset_otp_verified')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Silakan verifikasi OTP reset password terlebih dahulu.']);
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        if (!session('reset_user_id') || !session('reset_otp_verified')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi reset password tidak valid. Silakan minta OTP baru.']);
        }

        $request->validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $user = User::find(session('reset_user_id'));

        if (!$user) {
            session()->forget(['reset_user_id', 'reset_email', 'reset_otp_verified']);

            return redirect()->route('password.request')
                ->withErrors(['email' => 'Akun tidak ditemukan. Silakan minta OTP baru.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        session()->forget(['reset_user_id', 'reset_email', 'reset_otp_verified']);

        return redirect()->route('login')->with('success', 'Password berhasil diubah! Silakan masuk.');
    }

    public function profile()
    {
        $user = auth()->user();

        return view('profile', compact('user'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
            'gender' => ['required', 'string'],
            'nim' => ['required', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $updateData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'nim' => $request->nim,
        ];

        if ($request->hasFile('profile_photo')) {
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                'url' => ['secure' => true],
            ]);

            $result = (new UploadApi())->upload(
                $request->file('profile_photo')->getRealPath(),
                ['folder' => 'profiles']
            );

            $updateData['profile_photo'] = $result['secure_url'];
        }

        foreach ($updateData as $key => $value) {
            $user->$key = $value;
        }

        $user->save();

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }

    private function verifyRegisterOtp(Request $request)
    {
        $pending = session('pending_register');

        if (!$pending) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi registrasi tidak valid. Silakan daftar ulang.']);
        }

        if (now()->gt(Carbon::parse($pending['otp_expires_at']))) {
            return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Klik Kirim Ulang.']);
        }

        if (!Hash::check($request->otp, $pending['otp_hash'])) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        if (User::where('email', $pending['email'])->exists()) {
            $this->forgetOtpSession();
            return redirect()->route('login')->withErrors(['email' => 'Email sudah terdaftar. Silakan login.']);
        }

        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'nim' => $pending['nim'],
            'phone' => $pending['phone'],
            'gender' => 'Laki-Laki',
            'password' => $pending['password_hash'],
            'role' => 'user',
            'auth_provider' => 'manual',
            'email_verified' => true,
            'is_active' => true,
        ]);

        $this->forgetOtpSession();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Registrasi berhasil. Selamat datang.');
    }

    private function removeUnverifiedManualRegistration(?string $email): void
    {
        if (!$email) {
            return;
        }

        User::where('auth_provider', 'manual')
            ->where('email_verified', false)
            ->where('email', strtolower($email))
            ->delete();
    }

    private function startOtpChallenge(User $user, string $purpose): bool
    {
        $otp = $this->generateOtp();

        $user->update([
            'otp_code' => $otp,
            'otp_purpose' => $purpose,
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        session([
            'otp_user_id' => $user->id,
            'otp_email' => $user->email,
            'otp_purpose' => $purpose,
        ]);

        return $this->sendOtpEmail($user->email, $user->name, $purpose, $otp);
    }

    private function sendOtpEmail(string $email, string $name, string $purpose, string $otp): bool
    {
        try {
            Mail::to($email)->send(new OtpMail($otp, $name, $purpose));

            return true;
        } catch (\Throwable $exception) {
            Log::error('OTP email failed to send.', [
                'email' => $email,
                'purpose' => $purpose,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function otpIsValid(User $user, string $otp, string $purpose): bool
    {
        return $user->otp_code === $otp
            && $user->otp_purpose === $purpose
            && $user->otp_expires_at
            && now()->lte($user->otp_expires_at);
    }

    private function clearOtp(User $user): void
    {
        $user->update([
            'otp_code' => null,
            'otp_purpose' => null,
            'otp_expires_at' => null,
        ]);
    }

    private function forgetOtpSession(): void
    {
        session()->forget(['otp_user_id', 'otp_email', 'otp_purpose', 'pending_register']);
    }
}
