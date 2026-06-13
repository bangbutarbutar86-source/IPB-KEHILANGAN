<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthApiController extends Controller
{
    public function register(Request $request)
    {
        $this->removeUnverifiedManualRegistration($request->email);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'regex:/^[^A-Z]+$/', 'unique:users,email'],
            'phone' => ['required', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
            'nim' => ['required', 'string', 'max:20'],
            'password' => ['required', 'min:6'],
        ], [
            'email.dns' => 'Domain email tidak valid. Gunakan email asli yang bisa menerima OTP.',
            'email.regex' => 'Email tidak boleh menggunakan huruf besar.',
            'phone.regex' => 'Nomor WA hanya boleh berisi angka.',
        ]);

        $data['email'] = strtolower($data['email']);
        $otp = $this->generateOtp();

        $registrationToken = Crypt::encryptString(json_encode([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'nim' => $data['nim'],
            'password_hash' => Hash::make($data['password']),
            'otp_hash' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(5)->toIso8601String(),
        ]));

        $otpSent = $this->sendOtpEmail($data['email'], $data['name'], 'register', $otp);

        return response()->json([
            'message' => $otpSent
                ? 'Kode OTP sudah dikirim. Data akun belum disimpan sebelum OTP benar.'
                : 'Kode OTP dibuat, tetapi email gagal dikirim. Data akun belum disimpan.',
            'email' => $data['email'],
            'otp_required' => true,
            'purpose' => 'register',
            'registration_token' => $registrationToken,
            'mail_sent' => $otpSent,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required'],
            'password' => ['required'],
        ]);

        $user = User::where('email', strtolower($request->login))
            ->orWhere('phone', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Login gagal'], 401);
        }

        if ($user->is_active === false) {
            return response()->json(['message' => 'Akun kamu sedang tidak aktif'], 403);
        }

        if ($user->email_verified === false) {
            return response()->json([
                'message' => 'Akun belum diverifikasi. Silakan registrasi ulang dan masukkan OTP yang dikirim ke email.',
            ], 403);
        }

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $this->refreshApiToken($user),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'purpose' => ['required', 'in:register'],
            'registration_token' => ['required', 'string'],
        ]);

        $pending = $this->decryptRegistrationToken($data['registration_token']);

        if (!$pending || strtolower($data['email']) !== $pending['email']) {
            return response()->json(['message' => 'Sesi registrasi tidak valid. Silakan daftar ulang.'], 422);
        }

        if (now()->gt(Carbon::parse($pending['otp_expires_at']))) {
            return response()->json(['message' => 'Kode OTP sudah kedaluwarsa. Minta kode baru.'], 422);
        }

        if (!Hash::check($data['otp'], $pending['otp_hash'])) {
            return response()->json(['message' => 'Kode OTP salah'], 422);
        }

        if (User::where('email', $pending['email'])->exists()) {
            return response()->json(['message' => 'Email sudah terdaftar. Silakan login.'], 422);
        }

        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'phone' => $pending['phone'],
            'nim' => $pending['nim'],
            'password' => $pending['password_hash'],
            'role' => 'user',
            'auth_provider' => 'manual',
            'email_verified' => true,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $this->refreshApiToken($user),
        ]);
    }

    public function resendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'purpose' => ['required', 'in:register,reset_password'],
            'registration_token' => ['nullable', 'string'],
        ]);

        if ($data['purpose'] === 'register') {
            $pending = $this->decryptRegistrationToken($data['registration_token'] ?? '');

            if (!$pending || strtolower($data['email']) !== $pending['email']) {
                return response()->json(['message' => 'Sesi registrasi tidak valid. Silakan daftar ulang.'], 422);
            }

            $otp = $this->generateOtp();
            $pending['otp_hash'] = Hash::make($otp);
            $pending['otp_expires_at'] = now()->addMinutes(5)->toIso8601String();
            $registrationToken = Crypt::encryptString(json_encode($pending));
            $otpSent = $this->sendOtpEmail($pending['email'], $pending['name'], 'register', $otp);

            return response()->json([
                'message' => $otpSent
                    ? 'Kode OTP baru sudah dikirim ke email.'
                    : 'Kode OTP baru dibuat, tetapi email gagal dikirim.',
                'registration_token' => $registrationToken,
                'mail_sent' => $otpSent,
            ]);
        }

        $user = User::where('email', strtolower($data['email']))->first();

        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan'], 404);
        }

        $otpSent = $this->sendOtp($user, 'reset_password');

        return response()->json([
            'message' => $otpSent
                ? 'Kode OTP baru sudah dikirim ke email.'
                : 'Kode OTP baru dibuat, tetapi email gagal dikirim. Periksa konfigurasi SMTP.',
            'mail_sent' => $otpSent,
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email:rfc,dns'],
        ], [
            'email.dns' => 'Domain email tidak valid. Gunakan email yang terdaftar dan bisa menerima OTP.',
        ]);

        $user = User::where('email', strtolower($request->email))->first();

        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan'], 404);
        }

        $otpSent = $this->sendOtp($user, 'reset_password');

        return response()->json([
            'message' => $otpSent
                ? 'Kode OTP reset password sudah dikirim ke email.'
                : 'Kode OTP reset password dibuat, tetapi email gagal dikirim. Periksa konfigurasi SMTP.',
            'email' => $user->email,
            'mail_sent' => $otpSent,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $user = User::where('email', strtolower($data['email']))->first();

        if (!$user || !$this->otpIsValid($user, $data['otp'], 'reset_password')) {
            return response()->json(['message' => 'Kode OTP salah atau sudah kedaluwarsa'], 422);
        }

        $user->password = Hash::make($data['password']);
        $this->clearOtp($user);

        return response()->json(['message' => 'Password berhasil diubah. Silakan login.']);
    }

    public function me()
    {
        return response()->json([
            'message' => 'Data user',
            'data' => auth()->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
            'gender' => ['required', 'string'],
            'nim' => ['required', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
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

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $user,
        ]);
    }

    private function decryptRegistrationToken(string $token): ?array
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true);
            return is_array($payload) ? $payload : null;
        } catch (\Throwable $exception) {
            return null;
        }
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

    private function sendOtp(User $user, string $purpose): bool
    {
        $otp = $this->generateOtp();

        $user->update([
            'otp_code' => $otp,
            'otp_purpose' => $purpose,
            'otp_expires_at' => now()->addMinutes(5),
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
        $user->otp_code = null;
        $user->otp_purpose = null;
        $user->otp_expires_at = null;
        $user->save();
    }

    private function refreshApiToken(User $user): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $user->api_token = hash('sha256', $plainToken);
        $user->save();

        return $plainToken;
    }
}
