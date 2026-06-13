<!DOCTYPE html>
<html lang="id" id="htmlElement">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->title }} - IPB Kehilangan</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <script src="https://unpkg.com/alpinejs" defer></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>

<body x-data="{ showEditModal: false, showDeleteModal: false }"
    class="bg-gray-100 dark:bg-gray-900 min-h-screen">

    <!-- NAVBAR -->
    <nav class="bg-[#314494] text-white px-10 py-4 flex justify-between">
        <div class="text-xl font-bold">IPB KEHILANGAN</div>
        <div class="flex gap-8">
            <a href="{{ route('laporan') }}">Laporan</a>
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('profile') }}">Profile</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-10 py-12">

        <div class="grid md:grid-cols-2 gap-10">

            <!-- IMAGE -->
            <div>

                <!-- GAMBAR UTAMA -->
                @if(!empty($report->images))
                <img id="mainImage"
                    src="{{ $report->images[0] }}"
                    class="w-full h-[300px] object-cover rounded-xl shadow-lg">
                @else
                <img src="https://via.placeholder.com/500x500?text=No+Image"
                    class="w-full h-[300px] object-cover rounded-xl shadow-lg">
                @endif

                <!-- THUMBNAIL -->
                <div class="flex gap-2 mt-3">
                    @foreach($report->images ?? [] as $img)
                    <img src="{{ $img }}"
                        onclick="changeImage('{{ $img }}')"
                        class="w-16 h-16 rounded object-cover cursor-pointer opacity-70 hover:opacity-100 transition">
                    @endforeach
                </div>
                <!-- STATUS (PINDAH KE SINI) -->
                @if(auth()->id() == $report->user_id)
                <form action="{{ route('report.toggleStatus', $report->id) }}" method="POST" class="mt-4">
                    @csrf

                    @if($report->status == 'selesai')
                    <button class="w-full bg-red-500 hover:bg-red-600 transition text-white py-3 rounded-full font-semibold">
                        Batalkan Selesai
                    </button>
                    @else
                    <button class="w-full bg-green-500 hover:bg-green-600 transition text-white py-3 rounded-full font-semibold">
                        Tandai Selesai
                    </button>
                    @endif
                </form>
                @endif
            </div>

            <!-- DETAIL -->
            <div>
                <h1 class="text-3xl font-bold border-b pb-2 dark:text-white">
                    {{ $report->title }}
                </h1>

                <div class="mt-4 text-sm dark:text-gray-300">
                    <p class="font-semibold">Deskripsi Barang</p>
                    <p class="break-words whitespace-pre-line">
                        {{ $report->description }}
                    </p>
                </div>

                <div class="mt-4 text-sm text-gray-500">
                    {{ $report->type }} di {{ $report->location }} <br>
                    {{ $report->created_at->diffForHumans() }}
                </div>

                

                <!-- ACTION -->
                <div class="flex gap-4 mt-6">
                    @if(auth()->id() == $report->user_id)
                    <button @click="showEditModal = true"
                        class="bg-yellow-400 px-6 py-2 rounded-full text-white">
                        Edit
                    </button>
                    @endif

                    @if(auth()->user()->role === 'admin' || auth()->id() == $report->user_id)
                    <button @click="showDeleteModal = true"
                        class="bg-red-500 px-6 py-2 rounded-full text-white">
                        Hapus
                    </button>
                    @endif
                </div>

                <!-- WHATSAPP -->
                @if(auth()->id() != $report->user_id && $report->user)
                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $report->user->phone) }}"
                    class="mt-6 inline-block bg-green-500 px-6 py-3 rounded-full text-white">
                    Hubungi via WhatsApp
                </a>
                @endif

            </div>

        </div>

        <!-- RELATED -->
        <div class="mt-16">
            <h2 class="text-xl font-bold mb-6 dark:text-white">Lainnya</h2>

            <div class="grid md:grid-cols-4 gap-6">
                @foreach($relatedReports as $item)
                <a href="{{ route('report.show', $item->id) }}"
                    class="bg-white rounded-xl shadow overflow-hidden">

                    @if(!empty($item->images))
                    <img src="{{ $item->images[0] }}" class="w-full h-40 object-cover">
                    @else
                    <img src="https://via.placeholder.com/300x200" class="w-full h-40 object-cover">
                    @endif

                    <div class="p-3">
                        <h4 class="font-semibold text-sm">{{ $item->title }}</h4>
                        <p class="text-xs text-gray-500">{{ $item->location }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

    </div>

    <!-- EDIT MODAL -->
    <div x-show="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded w-full max-w-lg">

            <form action="{{ route('report.update', $report->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="text" name="title" value="{{ $report->title }}" class="w-full mb-3 p-2 border">
                <input type="text" name="location" value="{{ $report->location }}" class="w-full mb-3 p-2 border">

                <textarea name="description" class="w-full mb-3 p-2 border">{{ $report->description }}</textarea>

                <select name="type" class="w-full mb-3 p-2 border">
                    <option value="Dicari">Dicari</option>
                    <option value="Ditemukan">Ditemukan</option>
                </select>

                <!-- MULTI IMAGE -->
                <input type="file" name="images[]" multiple class="mb-3">

                <button class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
            </form>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div x-show="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded">

            <form action="{{ route('report.destroy', $report->id) }}" method="POST">
                @csrf
                @method('DELETE')

                <p>Yakin hapus?</p>
                <button class="bg-red-500 text-white px-4 py-2 mt-3 rounded">Hapus</button>
            </form>
        </div>
    </div>

    <!-- SCRIPT -->
    <script>
        function changeImage(src) {
            document.getElementById('mainImage').src = src;
        }
    </script>

</body>

</html>