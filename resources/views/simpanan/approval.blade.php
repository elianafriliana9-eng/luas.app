<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Approval Transaksi</h2>
                <p class="text-slate-500 text-sm mt-1">Transaksi yang membutuhkan persetujuan (penarikan &gt; Rp 1.000.000)</p>
            </div>
            <a href="{{ route('simpanan.transaksi') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
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

        <!-- Pending Count -->
        @if($pending->count() > 0)
            <div class="flex items-center gap-3 p-4 bg-tertiary/10 border border-tertiary/20 rounded-xl">
                <span class="material-symbols-outlined text-tertiary-dark">schedule</span>
                <span class="text-sm font-medium text-tertiary-dark"><strong>{{ $pending->count() }}</strong> transaksi menunggu persetujuan</span>
            </div>
        @endif

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
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($pending as $trx)
                            <tr class="hover:bg-tertiary/5 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-data text-sm font-semibold text-primary">{{ $trx->no_transaksi }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $trx->created_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                            {{ strtoupper(substr($trx->rekening?->anggota?->nama_lengkap ?? '?', 0, 2)) }}
                                        </div>
                                        <span class="text-sm font-semibold text-blue-900">{{ $trx->rekening?->anggota?->nama_lengkap }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-data text-slate-600">{{ $trx->rekening?->produk?->nama }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-data font-bold text-sm text-danger">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 max-w-[200px] truncate">{{ $trx->keterangan }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <form action="{{ route('simpanan.approve', $trx->id) }}" method="POST" class="inline">
                                            @csrf <input type="hidden" name="action" value="approve">
                                            <button type="submit" onclick="return confirm('Setujui transaksi ini?')" class="flex items-center gap-1.5 px-3.5 py-2 bg-secondary text-white text-xs font-bold rounded-lg hover:bg-secondary-dark transition-all shadow-sm">
                                                <span class="material-symbols-outlined text-[14px]">check</span>
                                                Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('simpanan.approve', $trx->id) }}" method="POST" class="inline">
                                            @csrf <input type="hidden" name="action" value="reject">
                                            <button type="submit" onclick="return confirm('Tolak transaksi ini? Saldo akan dikembalikan.')" class="flex items-center gap-1.5 px-3.5 py-2 bg-danger text-white text-xs font-bold rounded-lg hover:bg-red-700 transition-all shadow-sm">
                                                <span class="material-symbols-outlined text-[14px]">close</span>
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <span class="material-symbols-outlined text-[48px] text-secondary/30 mb-3 block">task_alt</span>
                                    <p class="text-sm text-slate-400 font-medium">Semua transaksi sudah diproses</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
