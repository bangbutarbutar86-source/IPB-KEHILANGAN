<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - IPB Kehilangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        .font-serif-ui { font-family: 'Playfair Display', serif; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="flex h-screen bg-white">

    {{-- KIRI --}}
    <div class="w-1/2 bg-[#314494] p-16 flex flex-col justify-center text-white">
        <div class="max-w-md mx-auto w-full">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-8">
                <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
            </div>
            <h1 class="text-4xl font-serif-ui font-bold leading-tight mb-6">Lupa<br>Password?</h1>
            <p class="text-white/80 text-sm leading-relaxed mb-6">
                Tenang! Masukkan email kamu dan kami akan kirimkan kode OTP untuk membuat password baru.
            </p>
            <div class="space-y-3">
                <div class="flex items-center gap-3 bg-white/10 rounded-xl p-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center text-sm font-bold flex-shrink-0">1</div>
                    <p class="text-sm text-white/80">Masukkan email yang terdaftar</p>
                </div>
                <div class="flex items-center gap-3 bg-white/10 rounded-xl p-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center text-sm font-bold flex-shrink-0">2</div>
                    <p class="text-sm text-white/80">Cek inbox email kamu</p>
                </div>
                <div class="flex items-center gap-3 bg-white/10 rounded-xl p-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center text-sm font-bold flex-shrink-0">3</div>
                    <p class="text-sm text-white/80">Masukkan OTP & buat password baru</p>
                </div>
            </div>
        </div>
    </div>

    {{-- KANAN --}}
    <div class="w-1/2 flex flex-col justify-center items-center bg-white px-12">
        <div class="w-[380px]">
            <h3 class="text-2xl font-serif-ui font-bold mb-2 text-black text-center">Reset Password</h3>
            <p class="text-sm text-gray-500 text-center mb-8">
                Masukkan email kamu untuk mendapatkan kode OTP reset password
            </p>

            {{-- Alert success --}}
            @if(session('success'))
            <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg mb-5 text-sm flex items-start gap-2">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="mt-0.5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            {{-- Alert error --}}
            @if($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
                {{ $errors->first('email') }}
            </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="space-y-4 mb-2">
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1.5">Email Terdaftar</label>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="nama@apps.ipb.ac.id"
                               required
                               class="w-full border border-gray-300 px-4 py-3 rounded-md outline-none focus:border-[#314494] focus:ring-1 focus:ring-[#314494] text-sm">
                    </div>
                </div>

                <p class="text-xs text-gray-400 mb-6">
                    Kode OTP reset password berlaku selama <strong>5 menit</strong> setelah dikirim.
                </p>

                <button type="submit"
                    class="w-full bg-[#314494] text-white py-3 rounded-full hover:bg-blue-800 transition font-medium text-sm mb-4">
                    Kirim OTP Reset Password
                </button>
            </form>

            <a href="{{ route('login') }}"
               class="w-full flex items-center justify-center gap-2 border border-gray-300 text-gray-600 py-3 rounded-full hover:bg-gray-50 transition font-medium text-sm">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Kembali ke Login
            </a>
        </div>
    </div>

</body>
</html>
