<!DOCTYPE html>
<html lang="id" id="htmlElement">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - IPB Kehilangan</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }

        * { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Search bar: putih transparan dengan teks putih jelas */
        .search-bar {
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.35);
            backdrop-filter: blur(6px);
            transition: all 0.2s ease;
        }
        .search-bar:focus-within {
            background: rgba(255,255,255,0.25);
            border-color: rgba(255,255,255,0.7);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.12);
        }
        .search-bar input {
            color: white;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .search-bar input::placeholder {
            color: rgba(255,255,255,0.65);
        }
        .search-bar input:focus {
            outline: none;
        }

        /* Filter button */
        .filter-btn {
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.3);
            transition: all 0.2s ease;
        }
        .filter-btn:hover {
            background: rgba(255,255,255,0.28);
            border-color: rgba(255,255,255,0.6);
        }

        /* Dropdown filter */
        .filter-dropdown {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(49,68,148,0.18), 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid rgba(49,68,148,0.1);
            overflow: hidden;
        }

        /* Card hover */
        .report-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .report-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(49,68,148,0.13);
        }

        /* Navbar glassmorphism effect on scroll — optional */
        nav {
            background: linear-gradient(135deg, #2B3E99 0%, #314494 60%, #3A50A8 100%);
            box-shadow: 0 2px 16px rgba(49,68,148,0.3);
        }

        /* Add button */
        .add-btn {
            background: linear-gradient(135deg, #314494, #4A5FC4);
            box-shadow: 0 4px 18px rgba(49,68,148,0.35);
            transition: all 0.2s ease;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(49,68,148,0.45);
        }
        .add-btn:active {
            transform: translateY(0);
        }

        /* Search icon color */
        .search-icon {
            opacity: 0.7;
        }
    </style>

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>

<body x-data="{ showAddModal: false }"
    class="bg-[#F0F3FB] dark:bg-gray-900 min-h-screen flex flex-col">

    <!-- NAVBAR -->
    <nav class="text-white px-8 py-3.5 flex items-center justify-between gap-4">

        <!-- LOGO -->
        <div class="text-lg font-extrabold tracking-wide whitespace-nowrap shrink-0">
            IPB KEHILANGAN
        </div>

        <!-- SEARCH + FILTER (tengah, melebar) -->
        <div class="flex items-center gap-2 flex-1 max-w-xl mx-4">

            <!-- Search Bar -->
            <div class="search-bar rounded-full px-4 py-2.5 flex items-center gap-2 flex-1">
                <!-- Search Icon -->
                <svg class="search-icon shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="searchInput"
                    placeholder="Cari barang hilang..."
                    class="bg-transparent w-full">
            </div>

            <!-- FILTER BUTTON -->
            <div x-data="{ open: false }" class="relative shrink-0">
                <button @click="open = !open"
                    class="filter-btn rounded-full w-11 h-11 flex items-center justify-center"
                    title="Filter">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                        stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="7" y1="12" x2="17" y2="12"/>
                        <line x1="10" y1="18" x2="14" y2="18"/>
                    </svg>
                </button>

                <!-- Dropdown -->
                <div x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.outside="open = false"
                    class="filter-dropdown absolute right-0 mt-3 w-48 z-50">

                    <div class="px-4 pt-3 pb-2 border-b border-gray-100">
                        <p class="text-xs font-bold text-[#314494] uppercase tracking-widest">Filter Status</p>
                    </div>

                    <div class="flex flex-col gap-2 p-3">
                        <button onclick="filterData('Dicari')" @click="open = false"
                            class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-semibold text-sm transition-all duration-150">
                            <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                            Dicari
                        </button>

                        <button onclick="filterData('Ditemukan')" @click="open = false"
                            class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-green-50 hover:bg-green-100 text-green-600 font-semibold text-sm transition-all duration-150">
                            <span class="w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                            Ditemukan
                        </button>

                        <button onclick="filterData('all')" @click="open = false"
                            class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-500 font-medium text-sm transition-all duration-150">
                            <span class="w-2 h-2 rounded-full bg-gray-400 shrink-0"></span>
                            Semua
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- NAV LINKS -->
        <div class="flex items-center gap-6 shrink-0">
            <a href="{{ route('laporan') }}"
               class="text-sm font-semibold text-white/80 hover:text-white transition-colors duration-150 hover:underline underline-offset-4">
               Laporan
            </a>
            <a href="{{ route('home') }}"
               class="text-sm font-semibold text-white hover:text-white transition-colors duration-150 underline underline-offset-4">
               Home
            </a>
            <a href="{{ route('profile') }}"
               class="text-sm font-semibold text-white/80 hover:text-white transition-colors duration-150 hover:underline underline-offset-4">
               Profile
            </a>
        </div>
    </nav>

    <!-- CONTENT WRAPPER -->
    <div class="flex-grow">
        <div class="max-w-7xl mx-auto px-6 py-10">

            <div class="text-center mb-6 text-sm text-gray-500 dark:text-gray-300 font-medium">
                Barang hilang? upload di sini biar cepat ketemu 🔍
            </div>

            <!-- ADD BUTTON -->
            <div class="flex justify-center mb-10">
                <button @click="showAddModal = true"
                    class="add-btn text-white px-10 py-3 rounded-full text-sm">
                    + Add Report
                </button>
            </div>

            <!-- GRID -->
            <div id="reportGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                @foreach($reports as $report)
                <div class="report-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden"
                    data-type="{{ $report->type }}"
                    data-title="{{ strtolower($report->title) }}">

                    <div class="flex justify-between items-start p-3.5">
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $report->user->name ?? 'User' }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-400 mt-0.5">
                                {{ $report->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <span class="inline-flex items-center text-xs font-bold px-3 py-1 rounded-full text-white shadow-sm
                            {{ $report->type == 'Ditemukan'
                                ? 'bg-green-500'
                                : 'bg-red-500' }}">
                            {{ $report->type }}
                        </span>
                    </div>

                    <a href="{{ route('report.show', $report->id) }}">
                        @if(!empty($report->images))
                        <img src="{{ $report->images[0] }}"
                            class="w-full h-[200px] object-cover"
                            onerror="this.src='https://via.placeholder.com/400x300'">
                        @else
                        <img src="https://via.placeholder.com/400x300"
                            class="w-full h-[200px] object-cover">
                        @endif
                    </a>

                    <div class="p-4">
                        <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-0.5">
                            {{ $report->title }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-300 mb-1">
                            📍 {{ $report->location }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-400 line-clamp-2">
                            {{ $report->description }}
                        </p>
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div x-show="showAddModal" x-cloak
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center z-50 px-4">

        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl">

            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Report</h2>
                <button @click="showAddModal = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('report.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                @csrf

                <input type="text" name="title" placeholder="Nama barang"
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#314494]/30" required>

                <input type="text" name="location" placeholder="Lokasi"
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#314494]/30" required>

                <textarea name="description" placeholder="Deskripsi"
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#314494]/30" rows="3" required></textarea>

                <input type="file" name="images[]" multiple accept="image/*"
                    onchange="validateFiles(this)"
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:bg-[#314494]/10 file:text-[#314494] file:font-semibold file:text-xs" required>

                <select name="type"
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#314494]/30">
                    <option value="Dicari">Dicari</option>
                    <option value="Ditemukan">Ditemukan</option>
                </select>

                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" @click="showAddModal = false"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-[#314494] hover:bg-[#263880] transition shadow-md">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FOOTER -->
    <footer style="background: linear-gradient(135deg, #2B3E99 0%, #314494 100%);"
        class="text-white py-6 px-10 mt-auto">
        <h2 class="text-lg font-extrabold tracking-wide">IPB KEHILANGAN</h2>
    </footer>

    <!-- SCRIPT -->
    <script>
        let currentFilter = "all";
        let debounceTimer;

        function filterData(type) {
            currentFilter = type;
            searchData();
        }

        function validateFiles(input) {
            if (input.files.length > 3) {
                alert("Maksimal upload 3 gambar!");
                input.value = "";
            }
        }

        document.getElementById("searchInput").addEventListener("keyup", function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => { searchData(); }, 400);
        });

        function searchData() {
            const keyword = document.getElementById("searchInput").value;
            const container = document.getElementById("reportGrid");
            container.innerHTML = `<p class='text-center col-span-4 text-gray-400 py-10'>Loading...</p>`;

            let url = `/api/reports?search=${keyword}`;
            if (currentFilter !== "all") url += `&type=${currentFilter}`;

            fetch(url, {
                headers: { "Authorization": "Bearer " + localStorage.getItem("token") }
            })
            .then(res => res.json())
            .then(data => {
                const reports = data.data?.data || [];
                renderData(reports);
            });
        }

        function renderData(reports) {
            const container = document.getElementById("reportGrid");
            container.innerHTML = "";

            if (reports.length === 0) {
                container.innerHTML = `<p class='col-span-4 text-center text-gray-400 py-10'>Tidak ada data ditemukan</p>`;
                return;
            }

            reports.forEach(report => {
                const badge = report.type === 'Ditemukan'
                    ? 'bg-green-500'
                    : 'bg-red-500';

                container.innerHTML += `
                <div class="report-card bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="flex justify-between items-start p-3.5">
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                ${report.user?.name ?? 'User'}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                ${report.created_at ?? ''}
                            </p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full text-white shadow-sm ${badge}">
                            ${report.type}
                        </span>
                    </div>

                    <img src="${report.images?.[0] ?? 'https://via.placeholder.com/400'}"
                        class="w-full h-[200px] object-cover"
                        onerror="this.src='https://via.placeholder.com/400x300'">

                    <div class="p-4">
                        <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-0.5">
                            ${report.title}
                        </h3>
                        <p class="text-xs text-gray-500 mb-1">📍 ${report.location}</p>
                        <p class="text-xs text-gray-400 line-clamp-2">${report.description}</p>
                    </div>
                </div>`;
            });
        }
    </script>

</body>
</html>