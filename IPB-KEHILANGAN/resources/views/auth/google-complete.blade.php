<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Data - IPB Kehilangan</title>
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
            {{-- Google badge --}}
            <div class="flex items-center gap-3 mb-8">
                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center">
                    <svg width="32" height="32" viewBox="0 0 18 18" fill="none">
                        <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                        <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                        <path d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z" fill="#FBBC05"/>
                        <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.96L3.964 7.293C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-white/60 uppercase tracking-wide">Terhubung via</p>
                    <p class="font-semibold text-lg">Google</p>
                </div>
            </div>

            <h1 class="text-4xl font-serif-ui font-bold leading-tight mb-4">Hampir<br>Selesai!</h1>
            <p class="text-white/80 text-sm leading-relaxed mb-6">
                Akun Google kamu berhasil terhubung. Lengkapi data di bawah untuk menyelesaikan pendaftaran di IPB Kehilangan.
            </p>

            {{-- Data dari Google --}}
            <div class="bg-white/10 rounded-xl p-4">
                <p class="text-xs font-semibold text-white/50 uppercase tracking-wide mb-3">Data dari Google</p>
                <div class="flex items-center gap-3">
                    @if(session('google_photo'))
                    <img src="{{ session('google_photo') }}" class="w-10 h-10 rounded-full border-2 border-white/30" alt="foto">
                    @else
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold">
                        {{ strtoupper(substr(session('google_name', 'U'), 0, 1)) }}
                    </div>
                    @endif
                    <div>
                        <p class="text-sm font-semibold">{{ session('google_name', '-') }}</p>
                        <p class="text-xs text-white/60">{{ session('google_email', '-') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KANAN --}}
    <div class="w-1/2 flex flex-col justify-center items-center bg-white px-12">
        <div class="w-[380px]">
            <h3 class="text-2xl font-serif-ui font-bold mb-2 text-black text-center">Lengkapi Data</h3>
            <p class="text-sm text-gray-500 text-center mb-8">
                Masukkan NIM dan nomor HP kamu untuk melanjutkan
            </p>

            {{-- Progress step --}}
            <div class="flex items-center gap-2 mb-8">
                <div class="flex items-center justify-center w-7 h-7 rounded-full bg-[#314494] text-white text-xs font-semibold">✓</div>
                <div class="flex-1 h-0.5 bg-[#314494]"></div>
                <div class="flex items-center justify-center w-7 h-7 rounded-full bg-[#314494] text-white text-xs font-semibold">✓</div>
                <div class="flex-1 h-0.5 bg-[#314494]"></div>
                <div class="flex items-center justify-center w-7 h-7 rounded-full bg-[#314494] text-white text-xs font-semibold">3</div>
            </div>
            <div class="flex justify-between text-xs text-gray-400 mb-8 -mt-6">
                <span>Pilih Google</span>
                <span>Izinkan akses</span>
                <span class="text-[#314494] font-medium">Lengkapi data</span>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('google.store') }}" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1.5">NIM <span class="text-red-400">*</span></label>
                        <input type="text"
                               name="nim"
                               value="{{ old('nim') }}"
                               placeholder="Contoh: G0401231001"
                               required
                               class="w-full border border-gray-300 px-4 py-3 rounded-md outline-none focus:border-[#314494] focus:ring-1 focus:ring-[#314494] text-sm">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1.5">Nomor HP <span class="text-red-400">*</span></label>
                        <input type="tel"
                               name="phone"
                               value="{{ old('phone') }}"
                               placeholder="Contoh: 08123456789"
                               required
                               class="w-full border border-gray-300 px-4 py-3 rounded-md outline-none focus:border-[#314494] focus:ring-1 focus:ring-[#314494] text-sm">
                        <p class="text-xs text-gray-400 mt-1">Format: 08xxxxxxxxxx (tanpa tanda +)</p>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-[#314494] text-white py-3 rounded-full hover:bg-blue-800 transition font-medium text-sm mb-4">
                    Selesaikan Pendaftaran
                </button>
            </form>

            <a href="{{ route('login') }}"
               class="w-full flex items-center justify-center gap-2 border border-gray-300 text-gray-500 py-3 rounded-full hover:bg-gray-50 transition text-sm">
                Batal, kembali ke Login
            </a>
        </div>
    </div>

</body>
</html>