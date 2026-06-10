<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Laporan Registrasi Simpanan</h2>
                <p class="text-slate-500 text-sm mt-1">Seluruh rekening simpanan yang pernah dibuka</p>
            </div>
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all no-print">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Cetak
            </button>
        </section>

        <section class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">No. Rekening</th>
                            <th class="px-6 py-4">Anggota</th>
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-6 py-4">Tanggal Buka</th>
                            <th class="px-6 py-4 text-right">Saldo</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($rekening as $i => $rek)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-3 text-sm text-slate-500">{{ $i + 1 }}</td>
                                <td class="px-6 py-3 font-data text-sm font-semibold text-primary">{{ $rek->no_rekening }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-blue-900">{{ $rek->anggota?->nama_lengkap }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $rek->produk?->nama }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $rek->tanggal_buka?->format('d M Y') }}</td>
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
