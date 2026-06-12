<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <section class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-end gap-4">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Rekening Simpanan</h2>
                <p class="text-slate-500 text-sm mt-1">Kelola seluruh rekening simpanan anggota koperasi</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('simpanan.export.rekening', request()->query()) }}" class="flex items-center gap-2 px-4 py-2.5 bg-secondary text-white text-sm font-semibold rounded-xl hover:bg-secondary-dark transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">file_download</span>
                    Export Excel
                </a>
                <a href="{{ route('simpanan.laporan.rekap') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                    <span class="material-symbols-outlined text-[18px]">summarize</span>
                    Laporan Rekap
                </a>
                <a href="{{ route('simpanan.create', ['jenis' => 'setoran']) }}" class="flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl font-semibold text-sm shadow-md shadow-primary/20 hover:bg-primary-dark transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Transaksi Baru
                </a>
            </div>
        </section>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-secondary/10 border border-secondary/20 text-secondary-dark rounded-xl">
                <span class="material-symbols-outlined">check_circle</span>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- KPI Cards -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl border-l-4 border-primary shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <span class="p-2 bg-primary/10 text-primary rounded-lg">
                        <span class="material-symbols-outlined">account_balance_wallet</span>
                    </span>
                </div>
                <p class="text-sm font-medium text-slate-500">Total Rekening Aktif</p>
                <h3 class="text-2xl font-bold mt-1 font-data text-blue-900">{{ number_format($totalRekening, 0, ',', '.') }}</h3>
            </div>
            <div class="bg-white p-6 rounded-xl border-l-4 border-secondary shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <span class="p-2 bg-secondary/10 text-secondary rounded-lg">
                        <span class="material-symbols-outlined">savings</span>
                    </span>
                </div>
                <p class="text-sm font-medium text-slate-500">Total Saldo</p>
                <h3 class="text-xl font-bold mt-1 font-data text-blue-900">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h3>
            </div>
            <div class="bg-white p-6 rounded-xl border-l-4 border-tertiary shadow-sm flex items-center justify-center">
                <a href="{{ route('simpanan.upload') }}" class="flex items-center gap-2 px-4 py-2.5 bg-tertiary/10 text-tertiary-dark text-sm font-semibold rounded-xl hover:bg-tertiary/20 transition-all">
                    <span class="material-symbols-outlined text-[18px]">upload_file</span>
                    Upload Excel
                </a>
            </div>
        </section>

        <!-- Filter -->
        <section class="bg-white p-5 rounded-xl shadow-sm">
            <form method="GET" action="{{ route('simpanan.rekening') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pencarian</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="No. rekening atau nama anggota..." class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Jenis Simpanan</label>
                    <select name="jenis_simpanan" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Jenis</option>
                        <option value="pokok" {{ request('jenis_simpanan')=='pokok'?'selected':'' }}>Pokok</option>
                        <option value="wajib" {{ request('jenis_simpanan')=='wajib'?'selected':'' }}>Wajib</option>
                        <option value="sukarela" {{ request('jenis_simpanan')=='sukarela'?'selected':'' }}>Sukarela</option>
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status')=='aktif'?'selected':'' }}>Aktif</option>
                        <option value="blokir" {{ request('status')=='blokir'?'selected':'' }}>Blokir</option>
                        <option value="tutup" {{ request('status')=='tutup'?'selected':'' }}>Tutup</option>
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">Filter</button>
                <a href="{{ route('simpanan.rekening') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Reset</a>
            </form>
        </section>

        <!-- Table -->
        <section class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="px-6 py-4">No. Rekening</th>
                            <th class="px-6 py-4">Anggota</th>
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-6 py-4 text-right">Saldo</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($rekening as $rek)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-data text-sm font-semibold text-primary">{{ $rek->no_rekening }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                            {{ strtoupper(substr($rek->anggota?->nama_lengkap ?? '?', 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-blue-900">{{ $rek->anggota?->nama_lengkap }}</p>
                                            <p class="text-[11px] text-slate-400 font-data">{{ $rek->anggota?->no_anggota }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $rek->produk?->nama }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-data font-bold text-sm text-blue-900">Rp {{ number_format($rek->saldo, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($rek->status === 'aktif')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary">Aktif</span>
                                    @elseif($rek->status === 'blokir')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-tertiary/10 text-tertiary-dark">Blokir</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-danger/10 text-danger">Tutup</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-1">
                                        <a href="{{ route('simpanan.statement', $rek->id) }}" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Statement">
                                            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                        </a>
                                        @if(auth()->user()->role === 'super_admin' && $rek->status === 'aktif')
                                            <a href="{{ route('simpanan.blokir', $rek->id) }}" class="p-2 text-tertiary-dark hover:bg-tertiary/10 rounded-lg transition-colors" title="Blokir">
                                                <span class="material-symbols-outlined text-[18px]">lock</span>
                                            </a>
                                            <a href="{{ route('simpanan.tutup', $rek->id) }}" class="p-2 text-danger hover:bg-danger/10 rounded-lg transition-colors" title="Tutup Rekening">
                                                <span class="material-symbols-outlined text-[18px]">cancel</span>
                                            </a>
                                        @elseif(auth()->user()->role === 'super_admin' && $rek->status === 'blokir')
                                            <form action="{{ route('simpanan.buka_blokir', $rek->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-2 text-secondary hover:bg-secondary/10 rounded-lg transition-colors" title="Buka Blokir">
                                                    <span class="material-symbols-outlined text-[18px]">lock_open</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <span class="material-symbols-outlined text-[48px] text-slate-300 mb-3 block">account_balance_wallet</span>
                                    <p class="text-sm text-slate-400 font-medium">Tidak ada rekening ditemukan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100">{{ $rekening->links() }}</div>
        </section>
    </div>
</x-app-layout>
