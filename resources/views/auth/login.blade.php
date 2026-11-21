<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Portal Internal Perkoci Eatery</title>

    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Pill role di panel kiri: penanda visual untuk radio yang dipilih. */
        [data-role-pill].is-active {
            background-color: #047857; /* emerald-700 */
            border-color: #34d399;     /* emerald-400 */
            color: #ffffff;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    @php
        $selectedRole = (string) old('role', '');
        $loginError = $errors->first();
    @endphp

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

                    <div class="pt-2">
                        <p class="mb-2 text-[9px] font-bold uppercase tracking-[0.18em] text-white/50">
                            Pilih role saat login
                        </p>

                        <div class="flex flex-wrap gap-2" data-role-pills>
                            @foreach (['kasir' => 'KASIR', 'admin' => 'ADMIN', 'manager' => 'MANAGER'] as $value => $label)
                                {{-- Label ini mengaktifkan radio role yang ada di form login. --}}
                                <label for="login-role-{{ $value }}" data-role-pill="{{ $value }}"
                                    class="cursor-pointer rounded-lg border border-white/10 bg-white/10 px-3 py-1 text-xs font-bold text-white/70 backdrop-blur-md transition hover:bg-white/20">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ==================== PANEL KANAN (CARD LOGIN FLOATING SHADOW) ==================== -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 bg-slate-100">

            <!-- Card Login Efek Mengembang (Shadow 3D) -->
            <div class="w-full max-w-md bg-white rounded-3xl p-8 md:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.12)] border border-gray-100 transition-all duration-300 hover:shadow-[0_25px_60px_rgba(0,0,0,0.18)]">

                <div class="space-y-2 mb-6">
                    <span class="text-[10px] font-bold tracking-wider px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full uppercase border border-emerald-200">
                        Portal Internal
                    </span>
                    <h2 class="text-2xl font-bold text-gray-900 pt-2">Masuk ke akun kamu</h2>
                    <p class="text-xs text-gray-500">
                        Gunakan akun yang diberikan manager untuk mengakses dashboard.
                    </p>
                </div>

                @if ($loginError)
                    <div role="alert"
                        class="mb-6 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.8" stroke="currentColor" class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $loginError }}</span>
                    </div>
                @endif

                <!-- Form Login -->
                <form id="login-form" action="{{ route('login.attempt') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <span class="block text-xs font-bold text-gray-600 uppercase tracking-wider">
                                Masuk Sebagai
                            </span>
                            <span class="text-[10px] font-medium text-gray-400">Opsional</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['' => 'Otomatis', 'kasir' => 'Kasir', 'admin' => 'Admin', 'manager' => 'Manager'] as $value => $label)
                                <label class="cursor-pointer">
                                    <input id="login-role-{{ $value !== '' ? $value : 'auto' }}" type="radio"
                                        name="role" value="{{ $value }}" class="peer sr-only"
                                        @checked($selectedRole === $value)>

                                    <span
                                        class="flex items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-bold text-gray-500 transition hover:bg-gray-100 peer-checked:border-emerald-700 peer-checked:bg-emerald-800 peer-checked:text-white peer-focus-visible:ring-4 peer-focus-visible:ring-emerald-100">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <p class="mt-1.5 text-[10px] leading-relaxed text-gray-400">
                            Pilih role untuk memastikan akun sesuai. Biarkan <strong>Otomatis</strong>
                            agar role mengikuti akun.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                            Username *
                        </label>
                        <input type="text" name="username" required placeholder="Masukkan username" autocomplete="username"
                            value="{{ old('username') }}"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition duration-200">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider">
                                Password *
                            </label>
                            <a href="#" class="text-xs font-medium text-emerald-700 hover:underline">Lupa password?</a>
                        </div>
                        <div class="relative">
                            <input id="login-password" type="password" name="password" required
                                placeholder="Masukkan password" autocomplete="current-password"
                                class="w-full pl-4 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition duration-200">

                            <button type="button" data-password-toggle="login-password"
                                aria-label="Tampilkan password" aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-gray-400 transition hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-600">
                                {{-- Ikon mata terbuka: password sedang terlihat --}}
                                <svg data-eye-open xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"
                                    class="hidden h-5 w-5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>

                                {{-- Ikon mata dicoret: password sedang tersembunyi --}}
                                <svg data-eye-closed xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"
                                    class="h-5 w-5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /* Sinkronkan pill role di panel kiri dengan radio role di form. */
            var roleInputs = Array.prototype.slice.call(document.querySelectorAll('input[name="role"]'));
            var rolePills = Array.prototype.slice.call(document.querySelectorAll('[data-role-pill]'));

            function syncRolePills() {
                var checked = document.querySelector('input[name="role"]:checked');
                var selected = checked ? checked.value : '';

                rolePills.forEach(function (pill) {
                    pill.classList.toggle('is-active', pill.getAttribute('data-role-pill') === selected);
                });
            }

            roleInputs.forEach(function (roleInput) {
                roleInput.addEventListener('change', syncRolePills);
            });

            syncRolePills();

            /* Toggle tampil/sembunyi password. */
            var toggle = document.querySelector('[data-password-toggle="login-password"]');
            var input = document.getElementById('login-password');

            if (!toggle || !input) return;

            var eyeOpen = toggle.querySelector('[data-eye-open]');
            var eyeClosed = toggle.querySelector('[data-eye-closed]');

            toggle.addEventListener('click', function () {
                var show = input.type === 'password';

                input.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                eyeOpen.classList.toggle('hidden', !show);
                eyeClosed.classList.toggle('hidden', show);

                // Kembalikan fokus ke input dan letakkan kursor di akhir teks.
                input.focus({ preventScroll: true });
                input.setSelectionRange(input.value.length, input.value.length);
            });
        });
    </script>
</body>
</html>
