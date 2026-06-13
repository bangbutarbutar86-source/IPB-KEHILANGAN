<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - IPB Kehilangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .font-serif-ui { font-family: 'Playfair Display', serif; }
        body { font-family: 'Inter', sans-serif; }
        .input-field {
            width: 100%;
            border: 1px solid #e5e7eb;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            outline: none;
            font-size: 0.875rem;
            transition: border-color 0.15s, box-shadow 0.15s;
            background: #fff;
        }
        .input-field:focus {
            border-color: #314494;
            box-shadow: 0 0 0 3px rgba(49,68,148,0.10);
        }
        .input-field::placeholder { color: #9ca3af; }
    </style>
</head>

<body class="flex min-h-screen bg-white">

    {{-- KIRI --}}
    <div class="w-1/2 bg-[#314494] p-16 flex flex-col justify-center text-white">
        <div class="max-w-md mx-auto w-full">
            <h1 class="text-5xl font-serif-ui font-bold leading-tight mb-6">Halo, Selamat<br>Datang!</h1>

            <div class="space-y-1 mb-8">
                <h2 class="text-4xl font-serif-ui font-bold">Temukan</h2>
                <h2 class="text-4xl font-serif-ui font-bold">Barang</h2>
                <h2 class="text-4xl font-serif-ui font-bold text-[#FFD700] italic">Hilangmu</h2>
                <h2 class="text-4xl font-serif-ui font-bold">Lebih</h2>
                <h2 class="text-4xl font-serif-ui font-bold">Cepat</h2>
            </div>

            <p class="text-white/80 text-sm leading-relaxed mt-8">
                Daftarkan akun dan mulai lacak barang hilangmu<br>
                di seluruh area kampus IPB University.
            </p>
        </div>
    </div>

    {{-- KANAN --}}
    <div class="w-1/2 flex flex-col justify-center items-center bg-white py-12 px-12 overflow-y-auto">
        <div class="w-[380px]">
            <h3 class="text-2xl font-serif-ui font-bold mb-1 text-gray-900">Buat Akun Baru</h3>
            <p class="text-sm text-gray-500 mb-7">Lengkapi data di bawah untuk mendaftar</p>

            <form action="{{ route('register') }}" method="POST" class="w-full">
                @csrf

                @if($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
                    <ul class="space-y-0.5 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="space-y-4">

                    {{-- Nama --}}
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="Masukkan nama lengkap"
                            class="input-field">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1.5">Email IPB <span class="text-red-400">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            placeholder="nama@apps.ipb.ac.id"
                            oninput="this.value = this.value.toLowerCase()"
                            class="input-field">
                    </div>

                    {{-- NIM --}}
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1.5">NIM <span class="text-red-400">*</span></label>
                        <input type="text" name="nim" value="{{ old('nim') }}" required
                            placeholder="Contoh: G0401231001"
                            class="input-field">
                    </div>

                    {{-- No WA --}}
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1.5">No. WhatsApp <span class="text-red-400">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                            placeholder="Contoh: 628123456789"
                            pattern="[0-9]+"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="input-field">
                        <p class="text-xs text-gray-400 mt-1">Format: 628xxxxxxxxx (gunakan kode negara 62)</p>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1.5">Password <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="password" name="password" id="pw-reg" required
                                placeholder="Min. 6 karakter"
                                class="input-field pr-10">
                            <button type="button" onclick="togglePw('pw-reg')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>

                <button type="submit"
                    class="w-full bg-[#314494] text-white py-3 rounded-full mt-6 hover:bg-blue-800 transition font-semibold text-sm shadow-sm">
                    Daftar Sekarang
                </button>
            </form>

            <div class="mt-5 text-center">
                <a href="{{ route('login') }}"
                    class="inline-block px-8 py-2.5 border border-[#314494] text-[#314494] rounded-full text-sm font-medium hover:bg-[#314494] hover:text-white transition">
                    Sudah punya akun? Login!
                </a>
            </div>
        </div>
    </div>

</body>

<script>
    function togglePw(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }
</script>

</html>
