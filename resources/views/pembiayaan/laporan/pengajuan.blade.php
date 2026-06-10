<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Laporan Pengajuan Pembiayaan</h2><button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print">🖨️ Print</button></div>
    </x-slot>
    <div class="space-y-4">
        <div class="bg-white p-4 rounded-lg shadow-sm no-print">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-[140px]"><label class="block text-xs font-medium text-gray-500 mb-1">Dari</label><input type="date" name="from" value="{{ request('from') }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[140px]"><label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label><input type="date" name="to" value="{{ request('to') }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[120px]"><select name="status" class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">Semua</option><option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option><option value="disetujui" {{ request('status')=='disetujui'?'selected':'' }}>Disetujui</option><option value="ditolak" {{ request('status')=='ditolak'?'selected':'' }}>Ditolak</option></select></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Filter</button>
            </form>
        </div>
        <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pengajuan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggota</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tenor</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                </tr></thead>
                <tbody class="divide-y">
                    @php $grandTotal = 0; @endphp
                    @foreach($pengajuan as $i => $pj)
                        @php $grandTotal += $pj->nominal_diajukan; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 text-sm font-mono text-indigo-600">{{ $pj->no_pengajuan }}</td>
                            <td class="px-4 py-2 text-sm font-medium">{{ $pj->anggota?->nama_lengkap }}</td>
                            <td class="px-4 py-2 text-sm">{{ $pj->produk?->nama }}</td>
                            <td class="px-4 py-2 text-right font-mono font-bold">Rp {{ number_format($pj->nominal_diajukan, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-center text-sm">{{ $pj->jangka_bulan }} bln</td>
                            <td class="px-4 py-2 text-center"><span class="px-2 py-0.5 text-xs rounded-full {{ $pj->status_approval==='disetujui'?'bg-green-100 text-green-800':($pj->status_approval==='pending'?'bg-yellow-100 text-yellow-800':'bg-red-100 text-red-800') }}">{{ ucfirst($pj->status_approval) }}</span></td>
                            <td class="px-4 py-2 text-sm">{{ $pj->created_at?->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-indigo-50 font-bold"><td colspan="4" class="px-4 py-3 text-sm text-right">GRAND TOTAL</td><td class="px-4 py-3 text-right font-mono text-indigo-700">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td><td colspan="3"></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
