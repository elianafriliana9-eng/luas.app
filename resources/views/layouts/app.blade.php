<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'KopSaku') }} - Executive Dashboard</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />

    <!-- Vite Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined', sans-serif !important;
            font-weight: normal;
            font-style: normal;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'liga';
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        body { font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .font-headline { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-data { font-family: 'JetBrains Mono', monospace; }

        /* ── Sidebar ── */
        #sidebar {
            width: 272px;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }
        #sidebar.closed { transform: translateX(-272px); }

        #topbar   { left: 272px; transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        #topbar.full   { left: 0; }

        #main     { margin-left: 272px; transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        #main.full     { margin-left: 0; }

        /* ── Accordion ── */
        .accord-body { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .accord-body.open { max-height: 600px; }
        .accord-chevron { transition: transform 0.25s ease; display: inline-flex; }
        .accord-chevron.open { transform: rotate(180deg); }

        /* ── Nav Links ── */
        .nav-link {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem;
            font-size: 0.875rem; font-weight: 500; border-radius: 0.5rem;
            transition: all 0.15s ease; color: #475569; white-space: nowrap;
        }
        .nav-link:hover { background: #f1f5f9; color: #1e40af; }
        .nav-link.active {
            background: rgba(29,78,216,0.1); color: #1D4ED8; font-weight: 600;
        }

        .sub-link {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.4rem 0.75rem 0.4rem 2.75rem;
            font-size: 13px; font-weight: 500; color: #64748B;
            border-radius: 0.375rem; transition: all 0.15s ease; white-space: nowrap;
        }
        .sub-link:hover { color: #1D4ED8; background: rgba(29,78,216,0.05); }
        .sub-link.active { color: #1D4ED8; background: rgba(29,78,216,0.08); font-weight: 600; }

        .sub-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.35rem 0.5rem 0.35rem 2.75rem;
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: #94a3b8; transition: color 0.15s;
            cursor: pointer; border: none; background: none; width: 100%;
        }
        .sub-header:hover { color: #1D4ED8; }
        .sub-header.active { color: #1D4ED8; }

        /* ── Scrollbar ── */
        .nav-scroll::-webkit-scrollbar { width: 3px; }
        .nav-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }
        .nav-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        @media (max-width: 1023px) {
            #sidebar { transform: translateX(-272px); }
            #sidebar.mobile-open { transform: translateX(0); }
            #topbar { left: 0 !important; }
            #main { margin-left: 0 !important; }
        }

        /* ── Print ── */
        @media print {
            .no-print, #sidebar, #topbar, .no-print * { display: none !important; }
            #main { margin-left: 0 !important; padding: 0 !important; }
            .overflow-x-auto { overflow: visible !important; }
            table { page-break-inside: auto; font-size: 10pt; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            body { background: white; }
            .max-w-7xl { max-width: 100% !important; }
            .shadow-sm, .shadow { box-shadow: none !important; }
            .rounded-lg, .rounded-xl { border-radius: 0 !important; }
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-[#0f172a] antialiased" x-cloak
      x-data="{ sidebarOpen: true }"
      @keydown.escape.window="if(window.innerWidth < 1024) sidebarOpen = false">

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         x-transition.opacity
         class="fixed inset-0 bg-black/30 z-40 lg:hidden"></div>

    <!-- ════════ SIDEBAR ════════ -->
    <aside id="sidebar"
           :class="{
               'closed': !sidebarOpen && window.innerWidth >= 1024,
               'mobile-open': sidebarOpen && window.innerWidth < 1024
           }"
           class="fixed left-0 top-0 h-full z-50 bg-white border-r border-slate-200/80 flex flex-col shadow-sm">

        <!-- Logo -->
        <div class="h-16 flex items-center px-4 border-b border-slate-100 flex-shrink-0">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/Desain tanpa judul-4.png') }}" alt="Logo" class="w-9 h-9 object-contain flex-shrink-0">
                <div class="flex-shrink-0">
                    <h1 class="text-base font-extrabold text-blue-900 leading-tight tracking-tight">KopSaku</h1>
                    <p class="text-[9px] uppercase tracking-widest text-slate-400 font-bold">Executive Admin</p>
                </div>
            </div>
        </div>

        @php
            $isSimpanan = request()->routeIs('simpanan.*');
            $isSimpananTrx = request()->routeIs('simpanan.rekening') || request()->routeIs('simpanan.index') || request()->routeIs('simpanan.create') || request()->routeIs('simpanan.pinbuk') || request()->routeIs('simpanan.statement');
            $isSimpananOps = request()->routeIs('simpanan.approval') || request()->routeIs('simpanan.upload') || request()->routeIs('simpanan.blokir*') || request()->routeIs('simpanan.tutup*') || request()->routeIs('simpanan.cancel*') || request()->routeIs('simpanan.pinbuk.approval*');
            $isSimpananLap = request()->routeIs('simpanan.laporan.*');
            $pendingApproval = \Illuminate\Support\Facades\Cache::remember('badge.pending_approval', 300, fn() =>
                \App\Models\TransaksiSimpanan::where('status_approval', 'pending')->where('dibatalkan', false)->count()
            );
            $pendingPinbuk = \Illuminate\Support\Facades\Cache::remember('badge.pending_pinbuk', 300, fn() =>
                \App\Models\Pinbuk::where('status_approval', 'pending')->count()
            );
            
            $jenisSimp = request()->query('jenis_simpanan');
            $isPokok = $isSimpanan && $jenisSimp === 'pokok';
            $isWajib = $isSimpanan && $jenisSimp === 'wajib';
            $isSukarela = $isSimpanan && $jenisSimp === 'sukarela';
        @endphp

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto nav-scroll py-3 px-2.5 space-y-0.5">

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-[20px]" style="{{ request()->routeIs('dashboard') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">dashboard</span>
                Dashboard
            </a>

            @php
                $pendingKeluar = \Illuminate\Support\Facades\Cache::remember('badge.pending_keluar', 300, fn() =>
                \App\Models\Anggota::where('status', 'pengajuan_keluar')->count()
            );
                $pendingAktif = \Illuminate\Support\Facades\Cache::remember('badge.pending_aktif', 300, fn() =>
                \App\Models\Anggota::where('status', 'pending_aktif')->count()
            );
            @endphp
            <!-- ═══ MASTER DATA ACCORDION ═══ -->
                            <div x-data="{ open: {{ request()->routeIs('anggota.*') || request()->routeIs('perusahaan.*') ? 'true' : 'false' }}, subAnggota: {{ request()->routeIs('anggota.*') ? 'true' : 'false' }}, lapAnggota: {{ request()->routeIs('anggota.laporan.*') || request()->routeIs('anggota.saldo') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="nav-link w-full flex justify-between {{ request()->routeIs('anggota.*') || request()->routeIs('perusahaan.*') ? 'active' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" style="{{ request()->routeIs('anggota.*') || request()->routeIs('perusahaan.*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">database</span>
                        <span>Master Data</span>
                    </div>
                    <span class="material-symbols-outlined text-[16px] accord-chevron" :class="open && 'open'">expand_more</span>
                </button>
                <div class="accord-body" :class="open && 'open'">
                    <div class="flex flex-col py-1 space-y-0.5">
                        <a href="{{ route('perusahaan.index') }}" class="sub-link {{ request()->routeIs('perusahaan.*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">business</span>
                            <span>Perusahaan (PT)</span>
                        </a>

                        {{-- ▸ Anggota --}}
                        <button @click="subAnggota = !subAnggota" type="button" class="sub-header {{ request()->routeIs('anggota.*') ? 'active' : '' }}">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">group</span>
                                Anggota
                            </span>
                            <span class="material-symbols-outlined text-[12px] accord-chevron" :class="subAnggota && 'open'">expand_more</span>
                        </button>
                        <div class="accord-body" :class="subAnggota && 'open'">
                            <a href="{{ route('anggota.index') }}" class="sub-link {{ request()->routeIs('anggota.index') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px] flex-shrink-0">list_alt</span>
                                <span>Daftar Anggota</span>
                            </a>
                            @if(auth()->user()->role === 'super_admin')
                            <a href="{{ route('anggota.approval_keluar') }}" class="sub-link {{ request()->routeIs('anggota.approval_keluar') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px] flex-shrink-0">exit_to_app</span>
                                <span>Approval Keluar</span>
                                @if($pendingKeluar > 0)
                                    <span class="ml-auto px-1.5 py-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full leading-none">{{ $pendingKeluar }}</span>
                                @endif
                            </a>
                            @endif
                            @if(auth()->user()->role === 'super_admin')
                            <a href="{{ route('anggota.pending_approval') }}" class="sub-link {{ request()->routeIs('anggota.pending_approval') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px] flex-shrink-0">how_to_reg</span>
                                <span>Approval Anggota Baru</span>
                                @if($pendingAktif > 0)
                                    <span class="ml-auto px-1.5 py-0.5 bg-amber-500 text-white text-[10px] font-bold rounded-full leading-none">{{ $pendingAktif }}</span>
                                @endif
                            </a>
                            @endif
                            <button @click="lapAnggota = !lapAnggota" type="button" class="sub-header {{ request()->routeIs('anggota.laporan.*') || request()->routeIs('anggota.saldo') ? 'active' : '' }}">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                                    Laporan
                                </span>
                                <span class="material-symbols-outlined text-[12px] accord-chevron" :class="lapAnggota && 'open'">expand_more</span>
                            </button>
                            <div class="accord-body" :class="lapAnggota && 'open'">
                                <a href="{{ route('anggota.saldo') }}" class="sub-link {{ request()->routeIs('anggota.saldo') ? 'active' : '' }}">
                                    <span class="material-symbols-outlined text-[16px] flex-shrink-0">account_balance_wallet</span>
                                    <span>Saldo Anggota</span>
                                </a>
                                <a href="{{ route('anggota.laporan.masuk') }}" class="sub-link {{ request()->routeIs('anggota.laporan.masuk') ? 'active' : '' }}">
                                    <span class="material-symbols-outlined text-[16px] flex-shrink-0">person_add</span>
                                    <span>Anggota Masuk</span>
                                </a>
                                <a href="{{ route('anggota.laporan.profil') }}" class="sub-link {{ request()->routeIs('anggota.laporan.profil') ? 'active' : '' }}">
                                    <span class="material-symbols-outlined text-[16px] flex-shrink-0">badge</span>
                                    <span>Laporan Profil</span>
                                </a>
                                <a href="{{ route('anggota.laporan.rekap') }}" class="sub-link {{ request()->routeIs('anggota.laporan.rekap') ? 'active' : '' }}">
                                    <span class="material-symbols-outlined text-[16px] flex-shrink-0">summarize</span>
                                    <span>Laporan Rekap</span>
                                </a>
                                <a href="{{ route('anggota.laporan.keluar') }}" class="sub-link {{ request()->routeIs('anggota.laporan.keluar') ? 'active' : '' }}">
                                    <span class="material-symbols-outlined text-[16px] flex-shrink-0">person_remove</span>
                                    <span>Anggota Keluar</span>
                                </a>
                            </div>
                            <a href="{{ route('anggota.import') }}" class="sub-link {{ request()->routeIs('anggota.import') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px] flex-shrink-0">upload_file</span>
                                <span>Import Anggota</span>
                            </a>
                            @if(auth()->user()->role === 'super_admin')
                            <a href="{{ route('anggota.import.master') }}" class="sub-link {{ request()->routeIs('anggota.import.master') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px] flex-shrink-0">dataset</span>
                                <span>Import Master Data</span>
                            </a>
                            @endif
                            @if(auth()->user()->role === 'super_admin')
                            <a href="{{ route('konfigurasi-coa.index') }}" class="sub-link {{ request()->routeIs('konfigurasi-coa.*') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px] flex-shrink-0">account_tree</span>
                                <span>Konfigurasi COA</span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ SIMPANAN (PARENT) ═══ -->
            <div x-data="{
                open: {{ $isSimpanan ? 'true' : 'false' }},
                pokok: {{ $isPokok ? 'true' : 'false' }},
                wajib: {{ $isWajib ? 'true' : 'false' }},
                sukarela: {{ $isSukarela ? 'true' : 'false' }},
                ops: {{ $isSimpananOps || request()->routeIs('simpanan.pinbuk') ? 'true' : 'false' }},
                lap: {{ $isSimpananLap ? 'true' : 'false' }}
            }">
                <button @click="open = !open" type="button" class="nav-link w-full justify-between {{ $isSimpanan ? 'active' : '' }}">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" style="{{ $isSimpanan ? 'font-variation-settings: \'FILL\' 1;' : '' }}">savings</span>
                        Simpanan
                    </span>
                    <span class="material-symbols-outlined text-[16px] accord-chevron" :class="open && 'open'">expand_more</span>
                </button>
                <div class="accord-body" :class="open && 'open'">
                    <div class="pt-1 pb-0.5 space-y-0.5">

                        {{-- ▸ Simpanan Pokok --}}
                        <button @click="pokok = !pokok" type="button" class="sub-header {{ $isPokok ? 'active' : '' }}">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">lock</span>
                                Simpanan Pokok
                            </span>
                            <span class="material-symbols-outlined text-[12px] accord-chevron" :class="pokok && 'open'">expand_more</span>
                        </button>
                        <div class="accord-body" :class="pokok && 'open'">
                            <a href="{{ route('simpanan.rekening', ['jenis_simpanan' => 'pokok']) }}" class="sub-link {{ $isPokok && request()->routeIs('simpanan.rekening') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span> Rekening Pokok
                            </a>
                            <a href="{{ route('simpanan.index', ['jenis_simpanan' => 'pokok']) }}" class="sub-link {{ $isPokok && request()->routeIs('simpanan.index') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">swap_horiz</span> Riwayat Transaksi
                            </a>
                        </div>

                        {{-- ▸ Simpanan Wajib --}}
                        <button @click="wajib = !wajib" type="button" class="sub-header {{ $isWajib ? 'active' : '' }}">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">trending_up</span>
                                Simpanan Wajib
                            </span>
                            <span class="material-symbols-outlined text-[12px] accord-chevron" :class="wajib && 'open'">expand_more</span>
                        </button>
                        <div class="accord-body" :class="wajib && 'open'">
                            <a href="{{ route('simpanan.rekening', ['jenis_simpanan' => 'wajib']) }}" class="sub-link {{ $isWajib && request()->routeIs('simpanan.rekening') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span> Rekening Wajib
                            </a>
                            <a href="{{ route('simpanan.index', ['jenis_simpanan' => 'wajib']) }}" class="sub-link {{ $isWajib && request()->routeIs('simpanan.index') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">swap_horiz</span> Riwayat Transaksi
                            </a>
                            <a href="{{ route('simpanan.create', ['jenis' => 'setoran', 'jenis_simpanan' => 'wajib']) }}" class="sub-link {{ $isWajib && request()->routeIs('simpanan.create') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">add_circle</span> Setoran Baru
                            </a>
                        </div>

                        {{-- ▸ Simpanan Sukarela --}}
                        <button @click="sukarela = !sukarela" type="button" class="sub-header {{ $isSukarela ? 'active' : '' }}">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">savings</span>
                                Simpanan Sukarela
                            </span>
                            <span class="material-symbols-outlined text-[12px] accord-chevron" :class="sukarela && 'open'">expand_more</span>
                        </button>
                        <div class="accord-body" :class="sukarela && 'open'">
                            <a href="{{ route('simpanan.rekening', ['jenis_simpanan' => 'sukarela']) }}" class="sub-link {{ $isSukarela && request()->routeIs('simpanan.rekening') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span> Rekening Sukarela
                            </a>
                            <a href="{{ route('simpanan.index', ['jenis_simpanan' => 'sukarela']) }}" class="sub-link {{ $isSukarela && request()->routeIs('simpanan.index') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">swap_horiz</span> Riwayat Transaksi
                            </a>
                            <a href="{{ route('simpanan.create', ['jenis' => 'setoran', 'jenis_simpanan' => 'sukarela']) }}" class="sub-link {{ $isSukarela && request()->routeIs('simpanan.create') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">add_circle</span> Transaksi Baru
                            </a>
                        </div>

                        {{-- Separator --}}
                        <div class="border-t border-slate-100 my-1.5 mx-2.5"></div>

                        {{-- ▸ Operasional --}}
                        <button @click="ops = !ops" type="button" class="sub-header {{ $isSimpananOps || request()->routeIs('simpanan.pinbuk') ? 'active' : '' }}">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">settings</span>
                                Operasional
                            </span>
                            <span class="material-symbols-outlined text-[12px] accord-chevron" :class="ops && 'open'">expand_more</span>
                        </button>
                        <div class="accord-body" :class="ops && 'open'">
                            @if(auth()->user()->role === 'super_admin')
                            <a href="{{ route('simpanan.approval') }}" class="sub-link {{ request()->routeIs('simpanan.approval') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">pending_actions</span>
                                Approval
                                @if($pendingApproval > 0)
                                    <span class="ml-auto px-1.5 py-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full leading-none">{{ $pendingApproval }}</span>
                                @endif
                            </a>
                            @endif
                            <a href="{{ route('simpanan.pinbuk') }}" class="sub-link {{ request()->routeIs('simpanan.pinbuk') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">compare_arrows</span>
                                Pemindahbukuan
                            </a>
                            @if(auth()->user()->role === 'super_admin')
                            <a href="{{ route('simpanan.pinbuk.approval') }}" class="sub-link {{ request()->routeIs('simpanan.pinbuk.approval') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">pending_actions</span>
                                Approval Pinbuk
                                @if($pendingPinbuk > 0)
                                    <span class="ml-auto px-1.5 py-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full leading-none">{{ $pendingPinbuk }}</span>
                                @endif
                            </a>
                            @endif
                            <a href="{{ route('simpanan.rekening_baru') }}" class="sub-link {{ request()->routeIs('simpanan.rekening_baru') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span>
                                Buka Rekening Baru
                            </a>
                            <a href="{{ route('simpanan.upload') }}" class="sub-link {{ request()->routeIs('simpanan.upload') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">upload_file</span>
                                Upload Excel
                            </a>
                        </div>

                        {{-- ▸ Laporan --}}
                        <button @click="lap = !lap" type="button" class="sub-header {{ $isSimpananLap ? 'active' : '' }}">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                                Laporan
                            </span>
                            <span class="material-symbols-outlined text-[12px] accord-chevron" :class="lap && 'open'">expand_more</span>
                        </button>
                        <div class="accord-body" :class="lap && 'open'">
                            <a href="{{ route('simpanan.laporan.rekap') }}" class="sub-link {{ request()->routeIs('simpanan.laporan.rekap') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">summarize</span> Rekap Simpanan
                            </a>
                            <a href="{{ route('simpanan.laporan.setoran') }}" class="sub-link {{ request()->routeIs('simpanan.laporan.setoran') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">north_east</span> Laporan Setoran
                            </a>
                            <a href="{{ route('simpanan.laporan.penarikan') }}" class="sub-link {{ request()->routeIs('simpanan.laporan.penarikan') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">south_west</span> Laporan Penarikan
                            </a>
                            <a href="{{ route('simpanan.laporan.regist') }}" class="sub-link {{ request()->routeIs('simpanan.laporan.regist') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">playlist_add</span> Registrasi
                            </a>
                            <a href="{{ route('simpanan.laporan.pinbuk') }}" class="sub-link {{ request()->routeIs('simpanan.laporan.pinbuk') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">swap_calls</span> Laporan Pinbuk
                            </a>
                            <a href="{{ route('simpanan.laporan.saldo') }}" class="sub-link {{ request()->routeIs('simpanan.laporan.saldo') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">account_balance</span> Saldo Simpanan
                            </a>
                            <a href="{{ route('simpanan.laporan.statement') }}" class="sub-link {{ request()->routeIs('simpanan.laporan.statement') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">receipt_long</span> Statement
                            </a>
                            <a href="{{ route('simpanan.laporan.blokir') }}" class="sub-link {{ request()->routeIs('simpanan.laporan.blokir') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">lock</span> Rekening Blokir
                            </a>
                            <a href="{{ route('simpanan.laporan.tutup') }}" class="sub-link {{ request()->routeIs('simpanan.laporan.tutup') ? 'active' : '' }}">
                                <span class="material-symbols-outlined text-[16px]">archive</span> Rekening Tutup
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ═══ PEMBIAYAAN ACCORDION ═══ -->
            <div x-data="{ open: {{ request()->routeIs('pembiayaan.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="nav-link w-full flex justify-between {{ request()->routeIs('pembiayaan.*') ? 'active' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" style="{{ request()->routeIs('pembiayaan.*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">payments</span>
                        <span>Pembiayaan</span>
                    </div>
                    <span class="material-symbols-outlined text-[16px] accord-chevron" :class="open && 'open'">expand_more</span>
                </button>
                <div class="accord-body" :class="open && 'open'">
                    <div class="flex flex-col py-1 space-y-0.5">
                        <a href="{{ route('pembiayaan.simulasi') }}" class="sub-link {{ request()->routeIs('pembiayaan.simulasi') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">calculate</span>
                            <span>Simulasi</span>
                        </a>
                        <a href="{{ route('pembiayaan.pengajuan.create') }}" class="sub-link {{ request()->routeIs('pembiayaan.pengajuan.create') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">note_add</span>
                            <span>Pengajuan</span>
                        </a>
                        @if(auth()->user()->role === 'super_admin')
                        <a href="{{ route('pembiayaan.registrasi') }}" class="sub-link {{ request()->routeIs('pembiayaan.registrasi') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">fact_check</span>
                            <span>Registrasi</span>
                        </a>
                        @endif
                        <a href="{{ route('pembiayaan.index') }}" class="sub-link {{ request()->routeIs('pembiayaan.index') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">list_alt</span>
                            <span>Daftar Pembiayaan</span>
                        </a>
                        <a href="{{ route('pembiayaan.transaksi') }}" class="sub-link {{ request()->routeIs('pembiayaan.transaksi') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">swap_horiz</span>
                            <span>Transaksi</span>
                        </a>
                        <a href="{{ route('pembiayaan.laporan.pengajuan') }}" class="sub-link {{ request()->routeIs('pembiayaan.laporan.*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">receipt_long</span>
                            <span>Laporan</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ═══ AKUNTANSI ACCORDION ═══ -->
            <div x-data="{ open: {{ request()->routeIs('akuntansi.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="nav-link w-full flex justify-between {{ request()->routeIs('akuntansi.*') ? 'active' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[20px]" style="{{ request()->routeIs('akuntansi.*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">account_balance</span>
                        <span>Akuntansi</span>
                    </div>
                    <span class="material-symbols-outlined text-[16px] accord-chevron" :class="open && 'open'">expand_more</span>
                </button>
                <div class="accord-body" :class="open && 'open'">
                    <div class="flex flex-col py-1 space-y-0.5">
                        <a href="{{ route('akuntansi.index') }}" class="sub-link {{ request()->routeIs('akuntansi.index') || request()->routeIs('akuntansi.jurnal*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">swap_horiz</span>
                            <span>Transaksi Jurnal</span>
                        </a>
                        @if(auth()->user()->role === 'super_admin')
                        <a href="{{ route('akuntansi.coa') }}" class="sub-link {{ request()->routeIs('akuntansi.coa') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">list_alt</span>
                            <span>Setup Akun (COA)</span>
                        </a>
                        <a href="{{ route('akuntansi.kas') }}" class="sub-link {{ request()->routeIs('akuntansi.kas') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">savings</span>
                            <span>Setup Kas</span>
                        </a>
                        @endif
                        <a href="{{ route('akuntansi.buku_besar') }}" class="sub-link {{ request()->routeIs('akuntansi.buku_besar') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">menu_book</span>
                            <span>Buku Besar</span>
                        </a>
                        <a href="{{ route('akuntansi.laporan.kas') }}" class="sub-link {{ request()->routeIs('akuntansi.laporan.*') ? 'active' : '' }}">
                            <span class="material-symbols-outlined text-[16px] flex-shrink-0">receipt_long</span>
                            <span>Laporan</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payroll -->
            <a href="{{ route('payroll.index') }}" class="nav-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-[20px]" style="{{ request()->routeIs('payroll.*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">receipt_long</span>
                Payroll
            </a>
        </nav>

        <!-- Bottom -->
        <div class="border-t border-slate-100 px-2.5 py-2 space-y-0.5 flex-shrink-0">
            <a href="{{ route('profile.edit') }}" class="nav-link">
                <span class="material-symbols-outlined text-[20px]">settings</span>
                Pengaturan
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" onclick="return confirm('Yakin ingin keluar?')" class="nav-link w-full" style="color: #dc2626;">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- ════════ TOP BAR ════════ -->
    <header id="topbar"
            :class="{ 'full': !sidebarOpen }"
            class="fixed top-0 right-0 z-40 bg-white/90 backdrop-blur-md h-16 flex items-center justify-between px-4 lg:px-6 border-b border-slate-200/80">

        {{-- Burger toggle --}}
        <button @click="sidebarOpen = !sidebarOpen" type="button"
                class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors flex-shrink-0 mr-2">
            <span class="material-symbols-outlined text-[22px]" x-text="sidebarOpen ? 'menu_open' : 'menu'"></span>
        </button>

        <div class="flex-1">
            @if (isset($header))
                {{ $header }}
            @else
                <div class="max-w-xl relative group ml-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] group-focus-within:text-blue-600 transition-colors">search</span>
                    <input type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-10 pr-4 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-300 placeholder:text-slate-400" placeholder="Cari data, anggota, atau laporan...">
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 lg:gap-4 ml-2">
            <button class="p-2 text-slate-500 hover:bg-slate-100 rounded-full relative transition-colors hidden sm:flex" type="button">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
            </button>
            <div class="h-6 w-[1px] bg-slate-200 hidden sm:block"></div>
            <div class="flex items-center gap-3 cursor-pointer group">
                <div class="text-right hidden lg:block">
                    <p class="text-sm font-bold text-slate-800 group-hover:text-blue-700 transition-colors">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 font-medium uppercase">{{ Str::replace('_', ' ', Auth::user()->role) }}</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            </div>
        </div>
    </header>

    <!-- Toast Notification -->
    <x-toast-notification />

    <!-- ════════ MAIN CONTENT ════════ -->
    <main id="main" :class="{ 'full': !sidebarOpen }" class="pt-16 min-h-screen">
        {{ $slot }}
    </main>

    <script>
    function formatRupiah(el) {
        var val = el.value;
        val = val.replace(/[^0-9,]/g, '');
        var parts = val.split(',');
        if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
        if (parts[0]) parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        el.value = parts.join(',');
    }

    function unformatRupiah(val) {
        return val.replace(/\./g, '').replace(',', '.');
    }

    document.addEventListener('submit', function(e) {
        e.target.querySelectorAll('.input-rupiah').forEach(function(input) {
            input.value = unformatRupiah(input.value);
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.input-rupiah').forEach(function(input) {
            formatRupiah(input);
        });
    });
    </script>

    @if(auth()->check() && auth()->user()->is_demo)
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('a');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href && !href.includes('dashboard') && !href.includes('logout') && href !== '#') {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'info',
                        title: 'Akses Dibatasi',
                        text: 'Anda berada di mode demo, akses terbatas demi keamanan.',
                        confirmButtonColor: '#1d4ed8'
                    });
                });
            }
        });

        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const action = form.getAttribute('action');
            if (!action || !action.includes('logout')) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'info',
                        title: 'Akses Dibatasi',
                        text: 'Anda berada di mode demo, akses terbatas demi keamanan.',
                        confirmButtonColor: '#1d4ed8'
                    });
                });
            }
        });
    });
    </script>
    @endif
</body>
</html>
