<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>KopSaku - Masuk ke Sistem</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700&amp;family=Inter:wght@400;500;600&amp;family=JetBrains+Mono&amp;family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
          tailwind.config = {
            darkMode: "class",
            theme: {
              extend: {
                colors: {
                  "inverse-on-surface": "#ecf1ff",
                  "surface-container": "#e7eeff",
                  "on-secondary": "#ffffff",
                  "tertiary-fixed-dim": "#ffb95f",
                  "error": "#ba1a1a",
                  "primary-container": "#1d4ed8",
                  "error-container": "#ffdad6",
                  "surface-dim": "#cfdaf2",
                  "secondary-container": "#82f5c1",
                  "inverse-primary": "#b7c4ff",
                  "on-tertiary-fixed": "#2a1700",
                  "inverse-surface": "#263143",
                  "secondary-fixed": "#85f8c4",
                  "on-tertiary": "#ffffff",
                  "on-secondary-fixed": "#002114",
                  "surface-container-low": "#f0f3ff",
                  "on-primary-fixed-variant": "#0039b5",
                  "on-surface": "#111c2d",
                  "tertiary-fixed": "#ffddb8",
                  "secondary-fixed-dim": "#68dba9",
                  "primary-fixed-dim": "#b7c4ff",
                  "on-secondary-container": "#00714e",
                  "on-error": "#ffffff",
                  "surface-container-high": "#dee8ff",
                  "on-primary-fixed": "#001551",
                  "on-primary": "#ffffff",
                  "surface-container-lowest": "#ffffff",
                  "on-surface-variant": "#434655",
                  "surface-variant": "#d8e3fb",
                  "on-primary-container": "#cad3ff",
                  "secondary": "#006c4a",
                  "background": "#f9f9ff",
                  "surface-container-highest": "#d8e3fb",
                  "on-tertiary-fixed-variant": "#653e00",
                  "on-error-container": "#93000a",
                  "surface": "#f9f9ff",
                  "tertiary": "#623c00",
                  "primary-fixed": "#dce1ff",
                  "on-tertiary-container": "#ffcb8f",
                  "outline": "#747686",
                  "surface-bright": "#f9f9ff",
                  "on-background": "#111c2d",
                  "primary": "#0037b0",
                  "surface-tint": "#2151da",
                  "tertiary-container": "#825100",
                  "on-secondary-fixed-variant": "#005137",
                  "outline-variant": "#c4c5d7"
                },
                fontFamily: {
                  "headline": ["Plus Jakarta Sans"],
                  "body": ["Inter"],
                  "label": ["Inter"],
                  "data": ["JetBrains Mono"]
                },
                borderRadius: {"DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem"},
              },
            },
          }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9ff;
            color: #111c2d;
        }
        h1, h2, .brand-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .mono-data {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="min-h-screen flex items-stretch">
<!-- Left Side: Brand Illustration & Identity -->
<div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-primary items-center justify-center">
    <!-- Abstract Background Layers -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-br from-primary via-primary to-secondary opacity-90"></div>
        <!-- Decorative Batik Pattern Overlay -->
        <div class="absolute inset-0 opacity-10" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDOcuiw0ISLYmgrJI668gA_EFWlXjo7EG4zf22R0XQQJL-Djczz_EsBU8cjE-9gJjqjDv3dty7VhHeMUwyfpk55OLVauaxYfa66QofaMaPAcHRtPxXZltT9Z4rDNPXXvoYkTw1wNO396tlN-6n8zTUj3TVxTAZzi96EzV1mY45UjVALCz7qgXpKgilEafcump1zXMoulN6NcB0ninWDQUV3q7Lq8DIqHanAobgL-gWP1dEIRS0m0KQ2PhKy_ER0VIpB9GYv6uz-tJk');"></div>
    </div>
    
    <div class="relative z-10 p-16 flex flex-col items-start max-w-2xl">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/Desain tanpa judul-4.png') }}" alt="Logo KopSaku" class="h-16 w-auto object-contain">
            <span class="text-3xl font-bold tracking-tight text-white headline">KopSaku</span>
        </div>
        
        <h2 class="text-5xl font-bold text-white mb-6 leading-tight">Membangun Kesejahteraan Bersama Melalui Ekonomi Digital.</h2>
        <p class="text-primary-fixed text-lg mb-12 max-w-lg leading-relaxed">
            Solusi finansial modern yang berakar pada nilai kekeluargaan dan kemitraan strategis di Indonesia.
        </p>
        
        <!-- Growth Metric Badge -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 border border-white/10 flex items-center gap-6">
            <div class="flex flex-col">
                <span class="text-secondary-fixed text-sm font-medium mb-1">Pertumbuhan Anggota</span>
                <span class="text-3xl font-bold text-white mono-data">+12.4%</span>
            </div>
            <div class="w-px h-12 bg-white/20"></div>
            <div class="flex flex-col">
                <span class="text-secondary-fixed text-sm font-medium mb-1">Suku Bunga Bersahabat</span>
                <span class="text-3xl font-bold text-white mono-data">0.75%</span>
            </div>
        </div>
        
        <div class="absolute bottom-16 left-16 flex items-center gap-4">
            <div class="flex -space-x-3">
                <img class="w-10 h-10 rounded-full border-2 border-primary" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBIf7bZSlSnm96YJY9b1Zib3WGTtZKXVdOZttwmWVFkM1xLCLcHnMLqDmrAy9VMzoi9M0iEj4KbE6rMYKII7UW_K-y_WtQWgroRLylYGFpraDeF3MrnVsmE-c3Zxp9nmrFdFig8WB8FeJ0KUmYxEh8509HyJhjyTZeY91_pmgk9Qb6ly9R0cUhWVe7pynU_XWzJUMtTIAgoDeYt-rQtfuHpaKWq5yzbdxCjqHIJJbqHr3aLJYdweadP4xkG-32To55ZvHMhcYi4GFE"/>
                <img class="w-10 h-10 rounded-full border-2 border-primary" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCmZZEd4wn7p9Chqc5SqpBbZgtrOf8_6LV9Czc7xA4u8cw7nLAKp5DWe1abUVhUEABVTOMLKhPed30Hlec0VShuAmxHCJfsffSPz49HBsxNo7R_RTeaJw5-IMpEHr0PmJ6bD4FmLJxFZ2oK3V6dw0bjEryaWTuaJXIQQAe7MZ0TUNsS1zVrimKvWB7StnZXwd9VaEgSgMx3vYJzxd3XE1McfaMBqqbvzq11uI0UYZL0UxP2i-g0Jdladaswz-UNSY-Hj_qtQjlTmsA"/>
                <img class="w-10 h-10 rounded-full border-2 border-primary" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAUaI5Orr8B4MWYpq4D_f1x_t6LFXwtlhxRjHfFp7fSJ3IdHN8qMqial_iao3huWvS8LXf97baiyP1Se6A39F45vf8Z8dmE-PyhP5pJAPA6wk12CuBaaDm7KXR7Ybt9nuyXenHVNKPr02_hr80wg_Zxf5fRif6CEmYUg5gwc0jmzBdGek_3VeZObhSKiItIVm3KC-FVfijTGVrjzq3FS2Zi-fPPVszfzfi101J8e52eMNwrA8sJLQXRQzThdXHtDnOLubaFVMEoB3I"/>
            </div>
            <span class="text-primary-fixed text-sm font-medium">Bergabung dengan 15.000+ Anggota lainnya</span>
        </div>
    </div>
    
    <!-- Abstract Motif Circles -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-secondary/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-tertiary/20 rounded-full blur-3xl"></div>
</div>

<!-- Right Side: Login Form -->
<main class="w-full lg:w-1/2 flex flex-col justify-center items-center bg-surface p-8 md:p-16 lg:p-24">
    <div class="w-full max-w-md">
        <!-- Form Header -->
        <div class="mb-10 text-center lg:text-left">
            <div class="text-center mb-10">
                <div class="flex items-center justify-center gap-2 mb-4 md:hidden">
                    <img src="{{ asset('images/Desain tanpa judul-4.png') }}" alt="Logo KopSaku" class="h-12 w-auto object-contain">
                    <span class="text-2xl font-bold tracking-tight text-primary headline">KopSaku</span>
                </div>
            </div>
            <h1 class="text-4xl font-bold text-on-surface mb-3 tracking-tight">Selamat Datang</h1>
            <p class="text-on-surface-variant text-lg">Masuk ke sistem koperasi Anda</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Login Card -->
        <div class="bg-surface-container-lowest p-8 rounded-xl shadow-[0_1px_3px_rgba(0,0,0,0.1)] border-none">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <!-- User ID Input -->
                <div>
                    <label class="block text-sm font-medium text-on-surface-variant mb-2" for="email">Email Pengguna</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-outline group-focus-within:text-primary transition-colors" data-icon="person">person</span>
                        </div>
                        <input class="block w-full pl-11 pr-4 py-3 bg-surface-container-low border-none rounded-lg focus:ring-0 focus:border-b-2 focus:border-primary transition-all text-on-surface placeholder:text-outline/60" id="email" name="email" value="{{ old('email') }}" type="email" placeholder="Masukkan email" required autofocus autocomplete="username" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-error text-sm" />
                </div>
                
                <!-- Password Input -->
                <div>
                    <label class="block text-sm font-medium text-on-surface-variant mb-2" for="password">Kata Sandi</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-outline group-focus-within:text-primary transition-colors" data-icon="lock">lock</span>
                        </div>
                        <input class="block w-full pl-11 pr-12 py-3 bg-surface-container-low border-none rounded-lg focus:ring-0 focus:border-b-2 focus:border-primary transition-all text-on-surface placeholder:text-outline/60" id="password" name="password" placeholder="••••••••" type="password" required autocomplete="current-password" />
                        <button class="absolute inset-y-0 right-0 pr-4 flex items-center text-outline hover:text-on-surface transition-colors" type="button" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';">
                            <span class="material-symbols-outlined" data-icon="visibility">visibility</span>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-error text-sm" />
                </div>
                
                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer group" for="remember_me">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded-sm border-outline text-primary focus:ring-primary focus:ring-offset-surface"/>
                        <span class="ml-3 text-sm text-on-surface-variant group-hover:text-on-surface transition-colors">Ingat saya di perangkat ini</span>
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-primary hover:text-primary-container transition-colors" href="{{ route('password.request') }}">Lupa kata sandi?</a>
                    @endif
                </div>
                
                <!-- Submit Button -->
                <button class="w-full bg-primary text-white font-semibold py-4 rounded-lg shadow-lg shadow-primary/20 hover:bg-primary-container active:scale-[0.98] transition-all duration-200" type="submit">
                    Masuk ke Sistem
                </button>
            </form>
            
            <!-- Help Link -->
            <div class="mt-8 pt-8 border-t border-outline-variant/30 text-center">
                <p class="text-sm text-on-surface-variant">
                    Kesulitan masuk? <a class="text-primary font-semibold hover:underline" href="#">Hubungi Admin Koperasi</a>
                </p>
                <div class="mt-4 p-3 bg-surface-container rounded text-xs text-left">
                    <strong>Akun Demo:</strong><br>
                    Admin: <code>admin@kopsaku.com</code><br>
                    Teller: <code>teller@kopsaku.com</code><br>
                    Password: <code>password</code>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Footer -->
    <footer class="mt-auto pt-12 flex flex-col md:flex-row justify-center items-center gap-6 w-full max-w-4xl opacity-70">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-widest mono-data">
            Koperasi Lumbung Artha Sejahtera · v2.0
        </div>
        <div class="hidden md:block h-1 w-1 bg-outline-variant rounded-full"></div>
        <div class="flex gap-4">
            <a class="text-xs font-medium text-slate-600 hover:text-primary transition-colors" href="#">Bantuan</a>
            <a class="text-xs font-medium text-slate-600 hover:text-primary transition-colors" href="#">Privasi</a>
            <a class="text-xs font-medium text-slate-600 hover:text-primary transition-colors" href="#">Syarat &amp; Ketentuan</a>
        </div>
    </footer>
</main>
</body>
</html>
