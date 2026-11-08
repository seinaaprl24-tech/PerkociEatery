<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Portal Internal Perkoci Eatery</title>

    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex w-full">

        <!-- ==================== PANEL KIRI (FOTO + GRADASI BAYANGAN TEKS) ==================== -->
        <div class="relative hidden lg:flex lg:w-1/2 min-h-screen p-12 flex-col justify-between overflow-hidden">

            <!-- 1. Foto Background Sepenuhnya -->
            <img src="{{ asset('images/perkoci-bg.jpg') }}"
                 alt="Perkoci Eatery"
                 class="absolute inset-0 w-full h-full object-cover object-center" />

            <!-- 2. Overlay Gradasi Gelap Transparan (Agar Foto Utuh Namun Teks Bawah Tetap Tajam) -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-black/10"></div>

            <!-- 3. Konten Teks & Logo -->
            <div class="relative z-10 flex flex-col justify-between h-full">

                <!-- Logo Perkoci (Atas) -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-perkoci-trim.png') }}" alt="Logo" class="w-12 h-12 rounded-full border-2 border-white/80 object-cover shadow-md">
                    <div class="bg-black/30 backdrop-blur-md px-3.5 py-1.5 rounded-xl border border-white/20">
                        <h2 class="font-bold text-white text-base leading-tight">Perkoci Eatery</h2>
                        <p class="text-[10px] text-gray-200 uppercase tracking-widest font-semibold">Artisanal Coffee & Kitchen</p>
                    </div>
                </div>

                <!-- Detail Informasi (Bawah) -->
                <div class="space-y-4 max-w-lg drop-shadow-md">
                    <span class="inline-block text-[10px] font-bold tracking-wider px-3 py-1 bg-white/20 backdrop-blur-md text-white rounded-full uppercase border border-white/20">
                        Portal Internal
                    </span>
                    <h1 class="text-3xl font-extrabold text-white leading-tight drop-shadow-lg">
                        Satu tempat untuk<br>mengelola layanan Perkoci.
                    </h1>
                    <p class="text-sm text-gray-200 leading-relaxed drop-shadow">
                        Pantau pesanan masuk, kelola menu, dan siapkan laporan penjualan langsung dari dashboard.
                    </p>

                    <div class="flex gap-2 pt-2">
                        <span class="text-xs font-bold px-3 py-1 bg-white/20 backdrop-blur-md text-white rounded-lg border border-white/10">KASIR</span>
                        <span class="text-xs font-bold px-3 py-1 bg-white/20 backdrop-blur-md text-white rounded-lg border border-white/10">ADMIN</span>
                        <span class="text-xs font-bold px-3 py-1 bg-white/20 backdrop-blur-md text-white rounded-lg border border-white/10">MANAGER</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- ==================== PANEL KANAN (CARD LOGIN FLOATING SHADOW) ==================== -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 bg-slate-100">

            <!-- Card Login Efek Mengembang (Shadow 3D) -->
            <div class="w-full max-w-md bg-white rounded-3xl p-8 md:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.12)] border border-gray-100 transition-all duration-300 hover:shadow-[0_25px_60px_rgba(0,0,0,0.18)]">

                <div class="space-y-2 mb-8">
                    <span class="text-[10px] font-bold tracking-wider px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full uppercase border border-emerald-200">
                        Portal Internal
                    </span>
                    <h2 class="text-2xl font-bold text-gray-900 pt-2">Masuk ke akun kamu</h2>
                    <p class="text-xs text-gray-500">
                        Gunakan akun yang diberikan manager untuk mengakses dashboard.
                    </p>
                </div>

                <!-- Form Login -->
                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                            Username *
                        </label>
                        <input type="text" name="username" required placeholder="kasir"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition duration-200">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider">
                                Password *
                            </label>
                            <a href="#" class="text-xs font-medium text-emerald-700 hover:underline">Lupa password?</a>
                        </div>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition duration-200">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded border-gray-300 text-emerald-700 focus:ring-emerald-600">
                        <label for="remember" class="ml-2 text-xs text-gray-600 font-medium">Ingat saya di perangkat ini</label>
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 bg-emerald-800 hover:bg-emerald-900 text-white text-sm font-bold rounded-2xl flex items-center justify-center gap-2 transition duration-200 shadow-lg shadow-emerald-900/30 active:scale-[0.98]">
                        Masuk ➔
                    </button>
                </form>

                <div class="text-center text-xs text-gray-400 pt-8 border-t border-gray-100 mt-8">
                    © 2026 Perkoci Eatery — Artisanal Coffee & Kitchen
                </div>

            </div>

        </div>

    </div>
</body>
</html>
