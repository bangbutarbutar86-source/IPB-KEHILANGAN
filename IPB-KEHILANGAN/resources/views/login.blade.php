<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IPB Kehilangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        .font-serif-ui { font-family: 'Playfair Display', serif; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="flex h-screen bg-white">

    {{-- KIRI: tidak diubah --}}
    <div class="w-1/2 bg-[#314494] p-16 flex flex-col justify-center text-white">
        <div class="max-w-md mx-auto w-full">
            <h1 class="text-5xl font-serif-ui font-bold leading-tight mb-6">Halo, Selamat<br>Datang Kembali!</h1>
            <div class="space-y-1 mb-8">
                <h2 class="text-4xl font-serif-ui font-bold">Temukan</h2>
                <h2 class="text-4xl font-serif-ui font-bold">Barang</h2>
                <h2 class="text-4xl font-serif-ui font-bold text-[#FFD700] italic">Hilangmu</h2>
                <h2 class="text-4xl font-serif-ui font-bold">Lebih</h2>
                <h2 class="text-4xl font-serif-ui font-bold">Cepat</h2>
            </div>
            <p class="text-lg font-medium tracking-wide mt-12">
                Masuk dan lacak barang hilangmu<br>di seluruh area kampus IPB<br>University.
            </p>
        </div>
    </div>

    {{-- KANAN: ada 3 perubahan --}}
    <div class="w-1/2 flex flex-col justify-center items-center bg-white relative">
        <h3 class="text-3xl font-serif-ui font-bold mb-10 text-black">IPB KEHILANGAN</h3>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm w-[380px]">
            {{ session('success') }}
        </div>
        @endif

        @if(session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg mb-4 text-sm w-[380px]">
            {{ session('info') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm w-[380px]">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="w-[380px]">
            @csrf
            <div class="space-y-4">
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email IPB" required
                    class="w-full border border-gray-300 px-4 py-3 rounded-md outline-none focus:border-[#314494] focus:ring-1 focus:ring-[#314494] text-sm">

                <input type="password" name="password" placeholder="Password" required
                    class="w-full border border-gray-300 px-4 py-3 rounded-md outline-none focus:border-[#314494] focus:ring-1 focus:ring-[#314494] text-sm">
            </div>

            {{-- ✏️ PERUBAHAN 1: href lupa password diisi route yang benar --}}
            <div class="text-right mt-2 mb-6">
                <a href="{{ route('password.request') }}" class="text-xs text-[#314494] hover:underline font-medium">
                    Lupa password?
                </a>
            </div>

            <button type="submit"
                class="w-full bg-[#314494] text-white py-3 rounded-full hover:bg-blue-800 transition font-medium text-sm">
                Log in
            </button>

            {{-- ✏️ PERUBAHAN 2: tambah divider + tombol Google setelah tombol Log in --}}
            <div class="flex items-center my-5">
                <div class="flex-1 border-t border-gray-200"></div>
                <span class="px-3 text-xs text-gray-400">atau</span>
                <div class="flex-1 border-t border-gray-200"></div>
            </div>

            <a href="{{ route('google.redirect') }}"
                class="w-full flex items-center justify-center gap-3 border border-gray-300 rounded-full py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                    <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                    <path d="M3.964 10.707A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z" fill="#FBBC05"/>
                    <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.96L3.964 7.293C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                </svg>
                Lanjutkan dengan Google
            </a>
        </form>

        {{-- ✏️ PERUBAHAN 3: tidak diubah, tetap di bawah --}}
        <div class="absolute bottom-12">
            <a href="{{ route('register') }}"
                class="px-8 py-2 border border-[#314494] text-[#314494] rounded-full text-sm font-medium hover:bg-[#314494] hover:text-white transition">
                Belum punya akun? Buat akun!
            </a>
        </div>
    </div>

</body>
</html>