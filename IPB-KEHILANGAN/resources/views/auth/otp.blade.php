<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - IPB Kehilangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        .font-serif-ui { font-family: 'Playfair Display', serif; }
        body { font-family: 'Inter', sans-serif; }
        .otp-input:focus { border-color: #314494; box-shadow: 0 0 0 3px rgba(49,68,148,0.12); outline: none; }
    </style>
</head>
<body class="flex h-screen bg-white">

    {{-- KIRI --}}
    <div class="w-1/2 bg-[#314494] p-16 flex flex-col justify-center text-white">
        <div class="max-w-md mx-auto w-full">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-8">
                <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5H4.5a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                </svg>
            </div>
            <h1 class="text-4xl font-serif-ui font-bold leading-tight mb-6">Cek<br>Email Kamu!</h1>
            <p class="text-white/80 text-sm leading-relaxed mb-6">
                Kami telah mengirimkan kode verifikasi 6 digit ke email kamu. Kode berlaku selama <strong class="text-white">5 menit</strong>.
            </p>
            <div class="bg-white/10 rounded-xl p-4 text-sm text-white/80 leading-relaxed">
                💡 Tidak menemukan email? Cek folder <strong class="text-white">Spam</strong> atau <strong class="text-white">Promosi</strong> di inbox kamu.
            </div>
        </div>
    </div>

    {{-- KANAN --}}
    <div class="w-1/2 flex flex-col justify-center items-center bg-white px-12">
        <div class="w-[380px]">
            <h3 class="text-2xl font-serif-ui font-bold mb-2 text-black text-center">Verifikasi Email</h3>
            <p class="text-sm text-gray-500 text-center mb-8">
                Masukkan kode 6 digit yang dikirim ke<br>
                <span class="font-semibold text-[#314494]">{{ session('otp_email') ?? 'email kamu' }}</span>
            </p>

            {{-- Alert --}}
            @if(session('success'))
            <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg mb-5 text-sm">
                {{ session('success') }}
            </div>
            @endif

            @if(session('mail_error'))
            <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg mb-5 text-sm">
                {{ session('mail_error') }}
            </div>
            @endif

            @if($errors->has('otp'))
            <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
                {{ $errors->first('otp') }}
            </div>
            @endif

            {{-- Form OTP --}}
            <form action="{{ route('otp.verify') }}" method="POST" id="otp-form">
                @csrf
                <input type="hidden" name="otp" id="otp-hidden"/>

                <div class="flex gap-3 justify-center mb-8" id="otp-boxes">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text"
                           maxlength="1"
                           inputmode="numeric"
                           pattern="[0-9]"
                           class="otp-input w-12 h-14 border border-gray-300 rounded-xl text-center text-xl font-bold text-[#314494] transition-all duration-150"
                    />
                    @endfor
                </div>

                <button type="submit"
                    class="w-full bg-[#314494] text-white py-3 rounded-full hover:bg-blue-800 transition font-medium text-sm mb-4">
                    Verifikasi & Masuk
                </button>
            </form>

            {{-- Resend OTP --}}
            <form action="{{ route('otp.resend') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full border border-[#314494] text-[#314494] py-3 rounded-full hover:bg-[#314494] hover:text-white transition font-medium text-sm">
                    Kirim Ulang Kode
                </button>
            </form>

            <p class="text-center text-xs text-gray-400 mt-6">
                Akun salah?
                <a href="{{ route('login') }}" class="text-[#314494] font-medium hover:underline">Kembali ke Login</a>
            </p>
        </div>
    </div>

</body>
<script>
    const boxes  = document.querySelectorAll('.otp-input');
    const hidden = document.getElementById('otp-hidden');

    boxes.forEach((box, i) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '');
            if (box.value && i < 5) boxes[i + 1].focus();
            syncHidden();
        });
        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !box.value && i > 0) {
                boxes[i - 1].focus();
            }
        });
        box.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
            [...text].forEach((char, idx) => {
                if (boxes[idx]) boxes[idx].value = char;
            });
            if (boxes[text.length - 1]) boxes[text.length - 1].focus();
            syncHidden();
        });
    });

    function syncHidden() {
        hidden.value = [...boxes].map(b => b.value).join('');
    }

    // Auto submit saat 6 digit terisi
    document.getElementById('otp-form').addEventListener('input', () => {
        const code = [...boxes].map(b => b.value).join('');
        if (code.length === 6) {
            setTimeout(() => document.getElementById('otp-form').submit(), 300);
        }
    });

    boxes[0].focus();
</script>
</html>
