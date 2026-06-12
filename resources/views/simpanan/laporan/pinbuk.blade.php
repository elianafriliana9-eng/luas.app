<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Laporan Pemindahbukuan</h2>
                <p class="text-slate-500 text-sm mt-1">Riwayat seluruh transaksi pinbuk antar rekening</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('simpanan.export.pinbuk', request()->query()) }}" class="flex items-center gap-2 px-4 py-2.5 bg-secondary text-white text-sm font-semibold rounded-xl hover:bg-secondary-dark transition-all no-print">
                    <span class="material-symbols-outlined text-[18px]">file_download</span>
                    Excel
                </a>
                <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all no-print">
                    <span class="material-symbols-outlined text-[18px]">print</span>
                    Cetak
                </button>
            </div>
        </section>

        <!-- Filter -->
        <section class="bg-white p-5 rounded-xl shadow-sm no-print">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-[150px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Dari</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div class="min-w-[150px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Sampai</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua</option>
                        <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Disetujui</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                        <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Ditolak</option>
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">Filter</button>
            </form>
        </section>

        <section class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">No. Transaksi</th>
                            <th class="px-6 py-4">Sumber</th>
                            <th class="px-6 py-4">Tujuan</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php $grandTotal = 0; @endphp
                        @forelse($pinbukList as $i => $pb)
                            @php $grandTotal += $pb->nominal; @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-3 text-sm text-slate-500">{{ $i + 1 }}</td>
                                <td class="px-6 py-3 font-data text-sm font-semibold text-primary">{{ $pb->no_transaksi }}</td>
                                <td class="px-6 py-3 text-sm">
                                    <span class="font-semibold text-blue-900">{{ $pb->rekeningSumber?->anggota?->nama_lengkap }}</span>
                                    <span class="text-slate-400 font-data text-xs block">{{ $pb->rekeningSumber?->no_rekening }}</span>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <span class="font-semibold text-blue-900">{{ $pb->rekeningTujuan?->anggota?->nama_lengkap }}</span>
                                    <span class="text-slate-400 font-data text-xs block">{{ $pb->rekeningTujuan?->no_rekening }}</span>
                                </td>
                                <td class="px-6 py-3 text-right font-data font-bold text-sm text-blue-900">Rp {{ number_format($pb->nominal, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-center">
                                    @if($pb->status_approval === 'approved')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary">Disetujui</span>
                                    @elseif($pb->status_approval === 'pending')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-tertiary/10 text-tertiary-dark">Pending</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-danger/10 text-danger">Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $pb->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><span class="material-symbols-outlined text-[40px] block mb-2">database_off</span>Tidak ada data pinbuk</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary/5 border-t-2 border-primary/20">
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-bold text-blue-900">GRAND TOTAL</td>
                            <td class="px-6 py-4 text-right font-data font-bold text-primary text-lg">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
