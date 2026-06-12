<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Laporan Rekap Simpanan</h2>
                <p class="text-slate-500 text-sm mt-1">Rekap saldo seluruh rekening simpanan aktif</p>
            </div>
            <div class="flex gap-2 no-print">
                <a href="{{ route('simpanan.export.rekap', request()->query()) }}" class="flex items-center gap-2 px-4 py-2.5 bg-secondary text-white text-sm font-semibold rounded-xl hover:bg-secondary-dark transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">file_download</span>
                    Export Excel
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
                <div class="min-w-[160px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Produk</label>
                    <select name="produk_id" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Produk</option>
                        @foreach($produkList as $p) <option value="{{ $p->id }}" {{ request('produk_id')==$p->id?'selected':'' }}>{{ $p->nama }}</option> @endforeach
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Cabang</label>
                    <select name="cabang_id" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Cabang</option>
                        @foreach($cabangList as $c) <option value="{{ $c->id }}" {{ request('cabang_id')==$c->id?'selected':'' }}>{{ $c->nama }}</option> @endforeach
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">Filter</button>
            </form>
        </section>

        <!-- Table -->
        <section class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">No. Rekening</th>
                            <th class="px-6 py-4">Anggota</th>
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-6 py-4 text-right">Saldo</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php $grandTotal = 0; @endphp
                        @forelse($rekening as $i => $rek)
                            @php $grandTotal += $rek->saldo; @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-3 text-sm text-slate-500">{{ $i + 1 }}</td>
                                <td class="px-6 py-3 font-data text-sm font-semibold text-primary">{{ $rek->no_rekening }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-blue-900">{{ $rek->anggota?->nama_lengkap }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $rek->produk?->nama }}</td>
                                <td class="px-6 py-3 text-right font-data font-bold text-sm text-blue-900">Rp {{ number_format($rek->saldo, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-center">
                                    @if($rek->status === 'aktif')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary">Aktif</span>
                                    @elseif($rek->status === 'blokir')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-tertiary/10 text-tertiary-dark">Blokir</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-danger/10 text-danger">Tutup</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400"><span class="material-symbols-outlined text-[40px] block mb-2">database_off</span>Tidak ada data rekening</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary/5 border-t-2 border-primary/20">
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-bold text-blue-900">GRAND TOTAL</td>
                            <td class="px-6 py-4 text-right font-data font-bold text-primary text-lg">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
