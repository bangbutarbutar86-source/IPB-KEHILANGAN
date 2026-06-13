<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - IPB Kehilangan</title>
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
            </div>
            <h1 class="text-4xl font-serif-ui font-bold leading-tight mb-6">Buat Password<br>Baru!</h1>
            <p class="text-white/80 text-sm leading-relaxed mb-6">
                Buat password baru yang kuat dan mudah kamu ingat. Pastikan berbeda dari password sebelumnya.
            </p>
            <div class="bg-white/10 rounded-xl p-4 space-y-2">
                <p class="text-xs font-semibold text-white/60 uppercase tracking-wide mb-3">Tips password kuat:</p>
                <div class="flex items-center gap-2 text-sm text-white/80">
                    <span class="text-[#FFD700]">✓</span> Minimal 6 karakter
                </div>
                <div class="flex items-center gap-2 text-sm text-white/80">
                    <span class="text-[#FFD700]">✓</span> Kombinasi huruf & angka
                </div>
                <div class="flex items-center gap-2 text-sm text-white/80">
                    <span class="text-[#FFD700]">✓</span> Gunakan karakter spesial (!@#$)
                </div>
            </div>
        </div>
    </div>

    {{-- KANAN --}}
    <div class="w-1/2 flex flex-col justify-center items-center bg-white px-12">
        <div class="w-[380px]">
            <h3 class="text-2xl font-serif-ui font-bold mb-2 text-black text-center">Password Baru</h3>
            <p class="text-sm text-gray-500 text-center mb-8">
                Buat password baru untuk {{ session('reset_email') ?? 'akun kamu' }}
            </p>

            @if($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1.5">Password Baru</label>
                        <div class="relative">
                            <input type="password"
                                   name="password"
                                   id="pw-new"
                                   placeholder="Min. 6 karakter"
                                   required
                                   class="w-full border border-gray-300 px-4 py-3 rounded-md outline-none focus:border-[#314494] focus:ring-1 focus:ring-[#314494] text-sm pr-10">
                            <button type="button" onclick="togglePw('pw-new', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1.5">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <input type="password"
                                   name="password_confirmation"
                                   id="pw-confirm"
                                   placeholder="Ulangi password baru"
                                   required
                                   class="w-full border border-gray-300 px-4 py-3 rounded-md outline-none focus:border-[#314494] focus:ring-1 focus:ring-[#314494] text-sm pr-10">
                            <button type="button" onclick="togglePw('pw-confirm', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-[#314494] text-white py-3 rounded-full hover:bg-blue-800 transition font-medium text-sm">
                    Simpan Password Baru
                </button>
            </form>
        </div>
    </div>

</body>
<script>
    function togglePw(id, btn) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }
</script>
</html>
