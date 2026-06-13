<!DOCTYPE html>
<html lang="id" id="htmlElement">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

    <!-- Anti flash dark -->
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300 min-h-screen">

    <!-- NAVBAR -->
    <nav class="bg-[#314494] text-white px-10 py-4 flex justify-between">
        <div class="font-bold text-xl">IPB KEHILANGAN</div>
        <div class="flex gap-6">
            <a href="{{ route('laporan') }}" class="font-semibold hover:text-gray-300">Laporan</a>
            <a href="{{ route('home') }}" class="hover:text-gray-300">Home</a>
            <a href="{{ route('profile') }}" class="hover:text-gray-300">Profile</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto p-10">

        <h1 class="text-2xl font-bold mb-8 text-gray-900 dark:text-white">
            Laporan Saya
        </h1>

        @if($reports->isEmpty())
        <p class="text-gray-500 dark:text-gray-400">Belum ada laporan.</p>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            @foreach($reports as $report)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-xl overflow-hidden transition">

                <a href="{{ route('report.show', $report->id) }}">

                    @if(!empty($report->images))
                    <img src="{{ $report->images[0] }}"
                        class="w-full h-56 object-cover hover:scale-105 transition-transform duration-300"
                        onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                    @else
                    <img src="https://via.placeholder.com/400x300?text=No+Image"
                        class="w-full h-56 object-cover">
                    @endif

                </a>

                <div class="p-4">
                    <h3 class="font-bold text-gray-900 dark:text-white">
                        {{ $report->title }}
                    </h3>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $report->location }}
                    </p>

                    <!-- STATUS -->
                    <div class="mt-2">
                        @if($report->status == 'selesai')
                        <span class="bg-gray-400 text-white px-3 py-1 rounded-full text-xs">
                            Selesai
                        </span>
                        @else
                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs">
                            Aktif
                        </span>
                        @endif
                    </div>

                </div>
            </div>
            @endforeach

        </div>

    </div>

    <!-- DARK MODE SCRIPT -->
    <script>
        function toggleDarkMode() {
            const htmlElement = document.documentElement;
            if (htmlElement.classList.contains('dark')) {
                htmlElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                htmlElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>

</body>

</html>