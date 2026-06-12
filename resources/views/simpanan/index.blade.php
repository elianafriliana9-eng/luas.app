<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1 text-xs text-slate-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
            <span class="material-symbols-outlined text-[12px]">chevron_right</span>
            <span class="text-primary font-medium">Simpanan</span>
        </nav>

        <!-- Page Header -->
        <section class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-end gap-4">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Transaksi Simpanan</h2>
                <p class="text-slate-500 text-sm mt-1">Seluruh riwayat transaksi simpanan</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('simpanan.export.transaksi', request()->query()) }}" class="flex items-center gap-2 px-4 py-2.5 bg-secondary text-white text-sm font-semibold rounded-xl hover:bg-secondary-dark transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">file_download</span>
                    Export Excel
                </a>
                @if(auth()->user()->role === 'super_admin')
                @php $pendingCount = Cache::remember('badge.pending_approval', 300, fn() => \App\Models\TransaksiSimpanan::where('status_approval', 'pending')->where('dibatalkan', false)->count()); @endphp
                <a href="{{ route('simpanan.approval') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all relative">
                    <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                    Approval
                    @if($pendingCount > 0)
                        <span class="px-1.5 py-0.5 bg-danger text-white text-[10px] font-bold rounded-full leading-none">{{ $pendingCount }}</span>
                    @endif
                </a>
                @endif
                <a href="{{ route('simpanan.create', ['jenis' => 'setoran']) }}" class="flex items-center gap-2 bg-secondary text-white px-4 py-2.5 rounded-xl font-semibold text-sm shadow-md shadow-secondary/20 hover:bg-secondary-dark transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    Setoran
                </a>
                <a href="{{ route('simpanan.create', ['jenis' => 'penarikan']) }}" class="flex items-center gap-2 bg-danger text-white px-4 py-2.5 rounded-xl font-semibold text-sm shadow-md shadow-danger/20 hover:bg-red-700 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">remove_circle</span>
                    Penarikan
                </a>
            </div>
        </section>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-secondary/10 border border-secondary/20 text-secondary-dark rounded-xl">
                <span class="material-symbols-outlined">check_circle</span>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-danger/10 border border-danger/20 text-danger rounded-xl">
                <span class="material-symbols-outlined">error</span>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filter -->
        <section class="bg-white p-5 rounded-xl shadow-sm">
            <form method="GET" action="{{ route('simpanan.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pencarian</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="No. transaksi atau nama anggota..." class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>
                </div>
                <div class="min-w-[130px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Jenis Simpanan</label>
                    <select name="jenis_simpanan" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua</option>
                        <option value="pokok" {{ request('jenis_simpanan')=='pokok'?'selected':'' }}>Pokok</option>
                        <option value="wajib" {{ request('jenis_simpanan')=='wajib'?'selected':'' }}>Wajib</option>
                        <option value="sukarela" {{ request('jenis_simpanan')=='sukarela'?'selected':'' }}>Sukarela</option>
                    </select>
                </div>
                <div class="min-w-[130px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Jenis</label>
                    <select name="jenis" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua</option>
                        <option value="setoran" {{ request('jenis')=='setoran'?'selected':'' }}>Setoran</option>
                        <option value="penarikan" {{ request('jenis')=='penarikan'?'selected':'' }}>Penarikan</option>
                        <option value="pinbuk_masuk" {{ request('jenis')=='pinbuk_masuk'?'selected':'' }}>Pinbuk Masuk</option>
                        <option value="pinbuk_keluar" {{ request('jenis')=='pinbuk_keluar'?'selected':'' }}>Pinbuk Keluar</option>
                        <option value="bunga" {{ request('jenis')=='bunga'?'selected':'' }}>Bunga</option>
                        <option value="koreksi" {{ request('jenis')=='koreksi'?'selected':'' }}>Koreksi</option>
                    </select>
                </div>
                <div class="min-w-[130px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua</option>
                        <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Disetujui</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                        <option value="dibatalkan" {{ request('status')=='dibatalkan'?'selected':'' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="min-w-[150px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Dari</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div class="min-w-[150px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Sampai</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">Filter</button>
                <a href="{{ route('simpanan.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Reset</a>
            </form>
        </section>

        <!-- Table -->
        <section class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="px-6 py-4">No. Transaksi</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Anggota</th>
                            <th class="px-6 py-4">Rekening</th>
                            <th class="px-6 py-4 text-center">Jenis</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($transaksi as $trx)
                            @php $isCredit = in_array($trx->jenis, ['setoran', 'pinbuk_masuk', 'bunga']); @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-data text-sm font-semibold text-primary">{{ $trx->no_transaksi }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $trx->created_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-blue-900">{{ $trx->rekening?->anggota?->nama_lengkap ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm font-data text-slate-600">{{ $trx->rekening?->produk?->nama ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($isCredit)
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary">{{ $trx->label_jenis }}</span>
                                    @elseif($trx->jenis === 'bunga')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-primary/10 text-primary">Bunga</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-danger/10 text-danger">{{ $trx->label_jenis }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-data font-bold text-sm {{ $isCredit ? 'text-secondary' : 'text-danger' }}">
                                    {{ $isCredit ? '+' : '-' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($trx->dibatalkan)
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-slate-100 text-slate-500">Dibatalkan</span>
                                    @elseif($trx->status_approval === 'pending')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-tertiary/10 text-tertiary-dark">Pending</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary">Disetujui</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if(auth()->user()->role === 'super_admin' && !$trx->dibatalkan && in_array($trx->jenis, ['setoran', 'penarikan', 'pinbuk_masuk', 'pinbuk_keluar']))
                                        <a href="{{ route('simpanan.cancel', $trx->id) }}" class="p-2 text-tertiary-dark hover:bg-tertiary/10 rounded-lg transition-colors inline-flex" title="Batalkan">
                                            <span class="material-symbols-outlined text-[18px]">block</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <span class="material-symbols-outlined text-[48px] text-slate-300 mb-3 block">swap_horiz</span>
                                    <p class="text-sm text-slate-400 font-medium">Tidak ada transaksi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100">{{ $transaksi->links() }}</div>
        </section>
    </div>
</x-app-layout>
