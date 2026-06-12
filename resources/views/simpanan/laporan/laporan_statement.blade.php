<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Laporan Statement Simpanan</h2>
                <p class="text-slate-500 text-sm mt-1">Riwayat mutasi lengkap per rekening</p>
            </div>
            <div class="flex gap-2 no-print">
                <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all no-print">
                    <span class="material-symbols-outlined text-[18px]">print</span>
                    Cetak
                </button>
            </div>
        </section>

        <section class="bg-white p-5 rounded-xl shadow-sm no-print">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-[250px]">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pilih Rekening</label>
                    <select name="rekening_id" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" required>
                        <option value="">-- Pilih Rekening --</option>
                        @foreach($rekeningList as $r)
                            <option value="{{ $r->id }}" {{ request('rekening_id')==$r->id?'selected':'' }}>
                                {{ $r->no_rekening }} — {{ $r->anggota?->nama_lengkap }} ({{ $r->produk?->nama }})
                            </option>
                        @endforeach
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
                <button type="submit" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">Tampilkan</button>
            </form>
        </section>

        @if($selectedRekening)
            <section class="bg-white rounded-xl p-6 shadow-sm border-l-4 border-primary flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-lg">
                        {{ strtoupper(substr($selectedRekening->anggota?->nama_lengkap ?? '?', 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-blue-900 font-headline">{{ $selectedRekening->anggota?->nama_lengkap }}</h3>
                        <p class="text-sm text-slate-500 font-data">{{ $selectedRekening->no_rekening }} &middot; {{ $selectedRekening->produk?->nama }} &middot; {{ $selectedRekening->anggota?->no_anggota }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Saldo Saat Ini</p>
                    <p class="text-2xl font-bold font-data text-primary mt-1">Rp {{ number_format($selectedRekening->saldo, 0, ',', '.') }}</p>
                </div>
            </section>

            @if($transaksi->count())
                <section class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">No. Transaksi</th>
                                    <th class="px-6 py-4">Jenis</th>
                                    <th class="px-6 py-4">Keterangan</th>
                                    <th class="px-6 py-4 text-right">Debet (+)</th>
                                    <th class="px-6 py-4 text-right">Kredit (-)</th>
                                    <th class="px-6 py-4 text-right">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($transaksi as $trx)
                                    @php $isDebit = in_array($trx->jenis, ['setoran', 'pinbuk_masuk', 'bunga']); @endphp
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $trx->created_at->format('d M Y H:i') }}</td>
                                        <td class="px-6 py-4 font-data text-sm font-semibold text-primary">{{ $trx->no_transaksi }}</td>
                                        <td class="px-6 py-4">
                                            @if($isDebit)
                                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary">{{ $trx->label_jenis }}</span>
                                            @else
                                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-danger/10 text-danger">{{ $trx->label_jenis }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500">{{ $trx->keterangan }}</td>
                                        <td class="px-6 py-4 text-right font-data font-semibold text-secondary">{{ $isDebit ? 'Rp ' . number_format($trx->nominal, 0, ',', '.') : '' }}</td>
                                        <td class="px-6 py-4 text-right font-data font-semibold text-danger">{{ !$isDebit ? 'Rp ' . number_format($trx->nominal, 0, ',', '.') : '' }}</td>
                                        <td class="px-6 py-4 text-right font-data font-bold text-blue-900">Rp {{ number_format($trx->saldo_sesudah, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @else
                <section class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-16 text-center">
                        <span class="material-symbols-outlined text-[48px] text-slate-300 mb-3 block">receipt_long</span>
                        <p class="text-sm text-slate-400 font-medium">Belum ada transaksi untuk rekening ini</p>
                    </div>
                </section>
            @endif
        @else
            <section class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-16 text-center">
                    <span class="material-symbols-outlined text-[48px] text-slate-300 mb-3 block">search</span>
                    <p class="text-sm text-slate-400 font-medium">Pilih rekening di atas untuk menampilkan statement</p>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
