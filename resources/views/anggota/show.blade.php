<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <section class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-end gap-4">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Detail Anggota</h2>
                <nav class="flex items-center gap-1.5 text-sm text-slate-400 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <a href="{{ route('anggota.index') }}" class="hover:text-primary transition-colors">Anggota</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-slate-700 font-semibold">{{ $anggota->nama_lengkap }}</span>
                </nav>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('anggota.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali
                </a>
            </div>
        </section>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-secondary/10 border border-secondary/20 text-secondary-dark rounded-xl">
                <span class="material-symbols-outlined">check_circle</span>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Profile Header Card -->
        <section class="bg-white rounded-xl shadow-sm p-6 md:p-8 border-l-4 border-primary">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-primary/10 border-2 border-primary/20 flex items-center justify-center text-primary text-xl md:text-2xl font-extrabold flex-shrink-0">
                        {{ strtoupper(substr($anggota->nama_lengkap, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1.5">
                            <h1 class="text-xl md:text-2xl font-extrabold text-blue-900 font-headline tracking-tight">{{ $anggota->nama_lengkap }}</h1>
                            @if($anggota->status === 'aktif')
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary uppercase tracking-wider">Aktif</span>
                            @elseif($anggota->status === 'pengajuan_keluar')
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-tertiary/10 text-tertiary-dark uppercase tracking-wider">Pengajuan Keluar</span>
                            @else
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-danger/10 text-danger uppercase tracking-wider">{{ ucfirst($anggota->status) }}</span>
                            @endif
                        </div>
                        <p class="text-slate-500 text-sm mb-3">No. Anggota: <span class="font-data font-semibold text-blue-900">{{ $anggota->no_anggota }}</span></p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-2">
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Bergabung</p>
                                <p class="text-xs font-semibold text-blue-900">{{ $anggota->tanggal_masuk?->format('d M Y') ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Cabang</p>
                                <p class="text-xs font-semibold text-blue-900">{{ $anggota->cabang?->nama ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Departemen</p>
                                <p class="text-xs font-semibold text-blue-900">{{ $anggota->departemen ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">No. HP</p>
                                <p class="text-xs font-semibold text-blue-900">{{ $anggota->no_hp ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 flex-shrink-0">
                    <a href="{{ route('anggota.edit', $anggota->id) }}" class="flex items-center gap-2 px-4 py-2.5 border border-primary text-primary text-sm font-semibold rounded-xl hover:bg-primary/5 transition-all">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                        Edit Data
                    </a>
                    <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl shadow-md shadow-primary/20 hover:bg-primary-dark transition-all no-print">
                        <span class="material-symbols-outlined text-[18px]">print</span>
                        Cetak
                    </button>
                    @if($anggota->status === 'keluar')
                        <a href="{{ route('anggota.export_keluar', $anggota->id) }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-800 text-white text-sm font-semibold rounded-xl shadow-md shadow-slate-800/20 hover:bg-slate-900 transition-all no-print">
                            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                            Download Bukti (PDF)
                        </a>
                    @endif
                    @if($anggota->status === 'aktif')
                        <a href="{{ route('anggota.keluar', $anggota->id) }}" class="flex items-center gap-2 px-4 py-2.5 border border-danger text-danger text-sm font-semibold rounded-xl hover:bg-danger/5 transition-all">
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                            Ajukan Keluar
                        </a>
                    @endif
                </div>
            </div>
        </section>

        @php
            $rekeningPokok = $anggota->rekeningSimpanan->filter(function($rek) {
                return $rek->produk && $rek->produk->jenis === 'pokok';
            })->first();
            $saldoPokok = $rekeningPokok ? $rekeningPokok->saldo : 0;
            $targetPokok = 150000;
            $persenPokok = $targetPokok > 0 ? min(100, round(($saldoPokok / $targetPokok) * 100)) : 0;
        @endphp
        
        @if($anggota->status === 'aktif' && $saldoPokok < $targetPokok)
        <!-- Progress Simpanan Pokok -->
        <section class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-tertiary">
            <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-tertiary-dark">savings</span>
                Progress Simpanan Pokok
            </h3>
            <div class="flex justify-between text-xs text-slate-500 mb-1">
                <span>Terkumpul: <strong class="text-blue-900 font-data">Rp {{ number_format($saldoPokok, 0, ',', '.') }}</strong></span>
                <span>Target: <strong class="font-data">Rp {{ number_format($targetPokok, 0, ',', '.') }}</strong></span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5">
                <div class="bg-tertiary h-2.5 rounded-full transition-all duration-1000" style="width: {{ $persenPokok }}%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px]">info</span>
                Kekurangan Simpanan Pokok akan dipotong otomatis dari jadwal payroll (potongan gaji) bulanan.
            </p>
        </section>
        @endif

        <!-- Data Pribadi -->
        <section class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 mb-5 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-primary">badge</span>
                Data Pribadi
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">NIK</p>
                    <p class="text-sm font-semibold text-blue-900 font-data mt-0.5">{{ $anggota->nik ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">No. Pegawai</p>
                    <p class="text-sm font-semibold text-blue-900 font-data mt-0.5">{{ $anggota->no_pegawai ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jabatan</p>
                    <p class="text-sm font-semibold text-blue-900 mt-0.5">{{ $anggota->jabatan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Gaji Pokok</p>
                    <p class="text-sm font-semibold text-blue-900 font-data mt-0.5">Rp {{ number_format($anggota->gaji_pokok ?? 0, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Alamat</p>
                    <p class="text-sm font-semibold text-blue-900 mt-0.5">{{ $anggota->alamat ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tempat, Tgl Lahir</p>
                    <p class="text-sm font-semibold text-blue-900 mt-0.5">{{ $anggota->tempat_lahir ?? '-' }}, {{ $anggota->tanggal_lahir?->format('d M Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</p>
                    <p class="text-sm font-semibold text-blue-900 mt-0.5">{{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : ($anggota->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Gajian</p>
                    <p class="text-sm font-semibold text-blue-900 font-data mt-0.5">Tanggal {{ $anggota->tanggal_gajian ?? '-' }}</p>
                </div>
            </div>
        </section>

        <!-- Tab Navigation -->
        <div x-data="{ activeTab: 'rekening' }" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-1.5 inline-flex gap-1">
                <button @click="activeTab = 'rekening'" :class="activeTab === 'rekening' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-500 hover:bg-slate-50'"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">
                    <span class="material-symbols-outlined text-[18px]">savings</span>
                    Rekening Simpanan
                </button>
                <button @click="activeTab = 'pembiayaan'" :class="activeTab === 'pembiayaan' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-500 hover:bg-slate-50'"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                    Pembiayaan
                </button>
                <button @click="activeTab = 'riwayat'" :class="activeTab === 'riwayat' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-500 hover:bg-slate-50'"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">
                    <span class="material-symbols-outlined text-[18px]">swap_horiz</span>
                    Riwayat Transaksi
                </button>
                <button @click="activeTab = 'dokumen'" :class="activeTab === 'dokumen' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-500 hover:bg-slate-50'"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">
                    <span class="material-symbols-outlined text-[18px]">folder_shared</span>
                    Dokumen
                </button>
            </div>

            <!-- Tab: Rekening Simpanan -->
            <div x-show="activeTab === 'rekening'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">
                @php
                    $rekPokok = $anggota->rekeningSimpanan->where('produk.kode', 'SP')->first();
                    $rekWajib = $anggota->rekeningSimpanan->where('produk.kode', 'SW')->first();
                    $rekSukarela = $anggota->rekeningSimpanan->where('produk.kode', 'SS')->first();
                    $rekDeposito = $anggota->rekeningSimpanan->where('produk.kode', 'SB')->first();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <!-- Simpanan Pokok -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-slate-400">
                        <div class="flex justify-between items-start mb-3">
                            <span class="p-2 bg-slate-100 text-slate-500 rounded-lg">
                                <span class="material-symbols-outlined text-[20px]">lock</span>
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Wajib Awal</span>
                        </div>
                        <h4 class="text-sm font-bold text-blue-900 mb-1">Simpanan Pokok</h4>
                        <p class="text-lg font-extrabold font-data text-blue-900">Rp {{ number_format($rekPokok?->saldo ?? 0, 0, ',', '.') }}</p>
                        @if($rekPokok)
                            <p class="text-[10px] text-slate-400 font-data mt-1.5">{{ $rekPokok->no_rekening }}</p>
                        @endif
                    </div>

                    <!-- Simpanan Wajib -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-secondary">
                        <div class="flex justify-between items-start mb-3">
                            <span class="p-2 bg-secondary/10 text-secondary rounded-lg">
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">trending_up</span>
                            </span>
                            <span class="text-[10px] font-bold text-secondary uppercase tracking-widest">Aktif Bulanan</span>
                        </div>
                        <h4 class="text-sm font-bold text-blue-900 mb-1">Simpanan Wajib</h4>
                        <p class="text-lg font-extrabold font-data text-blue-900">Rp {{ number_format($rekWajib?->saldo ?? 0, 0, ',', '.') }}</p>
                        @if($rekWajib)
                            <p class="text-[10px] text-slate-400 font-data mt-1.5">{{ $rekWajib->no_rekening }}</p>
                        @endif
                    </div>

                    <!-- Simpanan Sukarela -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-primary relative overflow-hidden">
                        <div class="absolute -right-3 -bottom-3 opacity-5">
                            <span class="material-symbols-outlined text-primary" style="font-size: 72px;">account_balance</span>
                        </div>
                        <div class="flex justify-between items-start mb-3 relative">
                            <span class="p-2 bg-primary/10 text-primary rounded-lg">
                                <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                            </span>
                            <div class="flex gap-1">
                                <a href="{{ route('simpanan.create', ['jenis' => 'setoran', 'anggota' => $anggota->id]) }}" class="p-1.5 bg-secondary text-white rounded-lg hover:bg-secondary-dark transition-colors" title="Setoran">
                                    <span class="material-symbols-outlined text-[14px]">add</span>
                                </a>
                                <a href="{{ route('simpanan.create', ['jenis' => 'penarikan', 'anggota' => $anggota->id]) }}" class="p-1.5 bg-danger text-white rounded-lg hover:bg-red-700 transition-colors" title="Penarikan">
                                    <span class="material-symbols-outlined text-[14px]">remove</span>
                                </a>
                            </div>
                        </div>
                        <h4 class="text-sm font-bold text-blue-900 mb-1">Simpanan Sukarela</h4>
                        <p class="text-lg font-extrabold font-data text-primary">Rp {{ number_format($rekSukarela?->saldo ?? 0, 0, ',', '.') }}</p>
                        @if($rekSukarela)
                            <p class="text-[10px] text-slate-400 font-data mt-1.5">{{ $rekSukarela->no_rekening }}</p>
                        @endif
                    </div>

                    <!-- Deposito / Berjangka -->
                    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-tertiary">
                        <div class="flex justify-between items-start mb-3">
                            <span class="p-2 bg-tertiary/10 text-tertiary-dark rounded-lg">
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">stars</span>
                            </span>
                            <span class="text-[10px] font-bold text-tertiary-dark uppercase tracking-widest">Berjangka</span>
                        </div>
                        <h4 class="text-sm font-bold text-blue-900 mb-1">Deposito / Berjangka</h4>
                        <p class="text-lg font-extrabold font-data text-blue-900">Rp {{ number_format($rekDeposito?->saldo ?? 0, 0, ',', '.') }}</p>
                        @if($rekDeposito)
                            <p class="text-[10px] text-slate-400 font-data mt-1.5">{{ $rekDeposito->no_rekening }}</p>
                        @endif
                    </div>
                </div>

                <!-- Total Simpanan -->
                <div class="bg-primary/5 border border-primary/10 rounded-xl p-5 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Total Semua Simpanan</p>
                        <p class="text-2xl md:text-3xl font-extrabold font-data text-primary mt-1">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-14 h-14 bg-primary/10 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-primary text-3xl">savings</span>
                    </div>
                </div>
            </div>

            <!-- Tab: Pembiayaan -->
            <div x-show="activeTab === 'pembiayaan'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-4">
                @if($pembiayaanAktif->isNotEmpty())
                    @foreach($pembiayaanAktif as $pem)
                        @php
                            $totalAngsuran = $pem->jangka_bulan;
                            $lunasAngsuran = $pem->jadwalAngsuran->where('status', 'lunas')->count();
                            $progressPercent = $totalAngsuran > 0 ? round(($lunasAngsuran / $totalAngsuran) * 100) : 0;
                        @endphp
                        <div class="bg-white rounded-xl p-6 shadow-sm border-l-4 border-primary">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-5">
                                <div>
                                    <p class="font-data text-sm font-semibold text-primary">{{ $pem->no_pembiayaan }}</p>
                                    <p class="text-[10px] text-slate-400 font-data mt-0.5">ID: {{ Str::limit($pem->id, 8) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sisa Pokok</p>
                                    <p class="text-xl font-extrabold font-data text-blue-900 mt-0.5">Rp {{ number_format($pem->saldo_pokok, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
                                <div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Plafon</p>
                                    <p class="text-sm font-semibold font-data text-blue-900 mt-0.5">Rp {{ number_format($pem->nominal_disetujui, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tenor</p>
                                    <p class="text-sm font-semibold text-blue-900 mt-0.5">{{ $pem->jangka_bulan }} Bulan</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Potong Gaji</p>
                                    <p class="text-sm font-semibold mt-0.5">
                                        @if($pem->auto_potong_gaji)
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary">Ya — Rp {{ number_format($pem->nominal_potongan ?? 0, 0, ',', '.') }}/bln</span>
                                        @else
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-slate-100 text-slate-500">Tidak</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</p>
                                    <p class="mt-0.5"><span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary">Aktif</span></p>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-[11px] font-bold text-secondary uppercase tracking-wider">Proses Angsuran</span>
                                    <span class="text-xs font-bold font-data text-blue-900">{{ $lunasAngsuran }}/{{ $totalAngsuran }} Bulan ({{ $progressPercent }}%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-secondary transition-all" style="width: {{ $progressPercent }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="bg-white rounded-xl shadow-sm p-16 text-center">
                        <span class="material-symbols-outlined text-[48px] text-slate-200 mb-3 block">payments</span>
                        <p class="text-sm text-slate-400 font-medium">Tidak ada pembiayaan aktif</p>
                    </div>
                @endif
            </div>

            <!-- Tab: Riwayat Transaksi -->
            <div x-show="activeTab === 'riwayat'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <section class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Rekening</th>
                                    <th class="px-6 py-4 text-center">Jenis</th>
                                    <th class="px-6 py-4">Keterangan</th>
                                    <th class="px-6 py-4 text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($historyTransaksi as $trx)
                                    @php $isCredit = in_array($trx->jenis, ['setoran', 'pinbuk_masuk', 'bunga']); @endphp
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $trx->created_at->format('d M Y') }}</td>
                                        <td class="px-6 py-4 text-sm font-semibold text-blue-900">{{ $trx->rekening?->produk?->nama ?? '-' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            @if($isCredit)
                                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary">{{ $trx->label_jenis }}</span>
                                            @else
                                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-danger/10 text-danger">{{ $trx->label_jenis }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500">{{ $trx->keterangan }}</td>
                                        <td class="px-6 py-4 text-right font-data font-bold text-sm {{ $isCredit ? 'text-secondary' : 'text-danger' }}">
                                            {{ $isCredit ? '+' : '-' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center">
                                            <span class="material-symbols-outlined text-[48px] text-slate-200 mb-3 block">swap_horiz</span>
                                            <p class="text-sm text-slate-400 font-medium">Belum ada transaksi</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- Tab: Dokumen -->
            <div x-show="activeTab === 'dokumen'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <section class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-700 mb-5 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">folder_shared</span>
                        Verifikasi Dokumen
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- KTP -->
                        @if($anggota->foto_ktp)
                            <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-2">
                                <img src="{{ asset('storage/' . $anggota->foto_ktp) }}" class="w-full aspect-[3/2] object-cover rounded-lg group-hover:scale-105 transition-transform" alt="Foto KTP">
                                <div class="mt-2 px-1 flex justify-between items-center">
                                    <p class="text-[11px] font-bold text-slate-700">Foto KTP</p>
                                    <span class="material-symbols-outlined text-secondary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-8">
                                <span class="material-symbols-outlined text-slate-300 text-[32px] mb-2">image_not_supported</span>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Foto KTP</p>
                                <p class="text-[10px] text-slate-400 mt-1">Belum diupload</p>
                            </div>
                        @endif

                        <!-- Selfie -->
                        @if($anggota->foto_selfie)
                            <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-2">
                                <img src="{{ asset('storage/' . $anggota->foto_selfie) }}" class="w-full aspect-[3/2] object-cover rounded-lg group-hover:scale-105 transition-transform" alt="Foto Selfie">
                                <div class="mt-2 px-1 flex justify-between items-center">
                                    <p class="text-[11px] font-bold text-slate-700">Foto Selfie</p>
                                    <span class="material-symbols-outlined text-secondary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-8">
                                <span class="material-symbols-outlined text-slate-300 text-[32px] mb-2">image_not_supported</span>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Foto Selfie</p>
                                <p class="text-[10px] text-slate-400 mt-1">Belum diupload</p>
                            </div>
                        @endif

                        <!-- Tambah Dokumen -->
                        <a href="{{ route('anggota.edit', $anggota->id) }}" class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-8 hover:bg-primary/5 hover:border-primary/30 transition-all cursor-pointer group">
                            <span class="material-symbols-outlined text-slate-300 group-hover:text-primary text-[32px] mb-2 transition-colors">add_a_photo</span>
                            <p class="text-[11px] font-bold text-slate-400 group-hover:text-primary uppercase tracking-wider transition-colors">Tambah Dokumen</p>
                        </a>
                    </div>
                </section>
            </div>
        </div>

    </div>
</x-app-layout>
