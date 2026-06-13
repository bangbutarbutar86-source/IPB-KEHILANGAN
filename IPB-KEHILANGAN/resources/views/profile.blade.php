<!DOCTYPE html>
<html lang="id" id="htmlElement">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile User - IPB Kehilangan</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .font-serif-ui {
            font-family: 'Playfair Display', serif;
        }

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>

    {{-- ✅ Anti-flash: harus di <head> sebelum apapun render --}}
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="bg-[#F8F9FA] dark:bg-gray-900 transition-colors duration-300 min-h-screen">

    <!-- NAVBAR -->
    <nav class="bg-[#314494] text-white px-10 py-4 flex items-center justify-between transition-colors duration-300">
        <div class="text-xl font-bold">IPB KEHILANGAN</div>
        <div class="flex-1"></div>
        <div class="flex gap-8">
            <a href="{{ route('laporan') }}" class="hover:text-gray-300 transition">Laporan</a>
            <a href="{{ route('home') }}" class="hover:text-gray-300 transition">Home</a>
            <a href="{{ route('profile') }}" class="font-semibold hover:text-gray-300 transition">Profile</a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-10 pt-24 grid grid-cols-1 md:grid-cols-[25%_auto] gap-20 items-start relative">

        <!-- SIDEBAR KIRI -->
        <div class="flex flex-col items-center">

            @if($user->profile_photo)
            <img src="{{ $user->profile_photo }}"
                class="w-32 h-32 rounded-full object-cover mb-6 border-4 border-white shadow-lg">
            @else
            <div class="w-32 h-32 rounded-full bg-[#E9ECEF] dark:bg-gray-700 flex justify-center items-center text-gray-300 dark:text-gray-500 text-6xl mb-6 shadow-inner transition-colors">
                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
            @endif

            <h1 class="text-4xl font-serif-ui leading-tight text-gray-950 dark:text-white mb-12 text-center transition-colors">
                Hello,<br>{{ $user->name }}.
            </h1>

            <div class="w-full space-y-2.5">
                <a href="#"
                    class="block w-full text-left bg-[#E9ECEF] dark:bg-gray-700 text-[#495057] dark:text-white px-6 py-3 rounded-md font-semibold text-base transition">
                    Informasi Akun
                </a>

                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit"
                        class="w-full text-left bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-6 py-3 rounded-md transition text-base border dark:border-gray-700">
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        <!-- FORM KANAN -->
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
            class="flex flex-col md:flex-row items-center md:items-start gap-12 bg-white dark:bg-gray-800 p-12 border dark:border-gray-700 rounded-3xl shadow-sm relative transition-colors duration-300">
            @csrf

            @if(session('success'))
            <div class="absolute -top-14 left-0 w-full bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
            @endif

            <!-- FOTO PROFIL -->
            <div class="relative group">
                <img id="profile-preview"
                    src="{{ $user->profile_photo ?? 'https://via.placeholder.com/300x300?text=Upload+Foto' }}"
                    class="w-64 h-64 object-cover rounded-xl shadow-md border-4 border-white dark:border-gray-700 group-hover:scale-105 transition">

                <label for="profile_photo"
                    class="absolute -bottom-3 -right-3 bg-black dark:bg-[#314494] text-white p-3 rounded-full shadow-xl hover:bg-gray-800 transition cursor-pointer z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                    </svg>
                    <input type="file" id="profile_photo" name="profile_photo"
                        class="hidden" accept="image/*" onchange="previewImage(event)">
                </label>
            </div>

            <!-- INPUT FIELDS -->
            <div class="flex-grow space-y-6 w-full max-w-lg">

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-950 dark:text-gray-200">Nama</label>
                    <input type="text" name="name" value="{{ $user->name }}" required
                        class="w-full bg-[#E9ECEF] dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-5 py-3.5 rounded-lg text-gray-700 dark:text-gray-100 font-medium outline-none focus:ring-2 focus:ring-[#314494] transition-colors">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-950 dark:text-gray-200">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ $user->phone }}" required
                        class="w-full bg-[#E9ECEF] dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-5 py-3.5 rounded-lg text-gray-700 dark:text-gray-100 font-medium outline-none focus:ring-2 focus:ring-[#314494] transition-colors">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-950 dark:text-gray-200">Email (Tidak bisa diubah)</label>
                    <input type="email" value="{{ $user->email }}" readonly
                        class="w-full bg-gray-200 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 px-5 py-3.5 rounded-lg text-gray-500 dark:text-gray-400 font-medium outline-none cursor-not-allowed">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-950 dark:text-gray-200">Jenis Kelamin</label>
                    <select name="gender"
                        class="w-full bg-[#E9ECEF] dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-5 py-3.5 rounded-lg text-gray-700 dark:text-gray-100 font-medium outline-none focus:ring-2 focus:ring-[#314494] transition-colors">
                        <option value="Laki-Laki" {{ $user->gender == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="Perempuan" {{ $user->gender == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div class="space-y-1.5 mb-10">
                    <label class="block text-sm font-semibold text-gray-950 dark:text-gray-200">NIM</label>
                    <input type="text" name="nim" value="{{ $user->nim }}" required
                        class="w-full bg-[#E9ECEF] dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-5 py-3.5 rounded-lg text-gray-700 dark:text-gray-100 font-medium outline-none focus:ring-2 focus:ring-[#314494] transition-colors">
                </div>

                <!-- TOMBOL DARK MODE + SIMPAN -->
                <div class="flex justify-between items-center mt-12">

                    {{-- ✅ Tombol Toggle Dark Mode --}}
                    <button type="button" onclick="toggleDarkMode()"
                        class="flex items-center space-x-3 bg-[#E9ECEF] dark:bg-gray-700 px-4 py-2 rounded-full hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        <span id="dark-mode-indicator"
                            class="w-6 h-6 bg-white dark:bg-gray-900 rounded-full border border-gray-200 dark:border-gray-600 shadow-inner transform transition-transform">
                        </span>
                        <span class="text-sm text-gray-600 dark:text-gray-200 font-medium" id="dark-mode-text">
                            Dark Mode Off
                        </span>
                    </button>

                    <button type="submit"
                        class="bg-[#314494] hover:bg-blue-800 text-white font-bold py-3 px-8 rounded-full shadow-lg transition transform hover:scale-105">
                        Simpan Perubahan
                    </button>
                </div>

            </div>
        </form>
    </div>

    <script>
        // Preview foto sebelum disimpan
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('profile-preview').src = reader.result;
            }
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }

        // ✅ Dark Mode — sinkronisasi teks tombol saat halaman load
        const htmlElement = document.documentElement;
        const darkModeText = document.getElementById('dark-mode-text');

        // Sinkron teks sesuai localStorage saat halaman dibuka
        window.addEventListener('DOMContentLoaded', () => {
            darkModeText.innerText = localStorage.getItem('theme') === 'dark' ?
                'Dark Mode On' :
                'Dark Mode Off';
        });

        function toggleDarkMode() {
            if (htmlElement.classList.contains('dark')) {
                htmlElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                darkModeText.innerText = 'Dark Mode Off';
            } else {
                htmlElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                darkModeText.innerText = 'Dark Mode On';
            }
        }
    </script>

</body>

</html>