<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Laporan Pencairan Pembiayaan</h2><div class="flex gap-2"><a href="{{ route('pembiayaan.export.pencairan', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition no-print flex items-center gap-1"><span class="material-symbols-outlined text-[18px]">download</span> Export Excel</a><button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print">🖨️ Print</button></div></div>
    </x-slot>
    <div class="space-y-4">
        <div class="bg-white p-4 rounded-lg shadow-sm no-print">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-[140px]"><label class="block text-xs font-medium text-gray-500 mb-1">Dari</label><input type="date" name="from" value="{{ request('from') }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[140px]"><label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label><input type="date" name="to" value="{{ request('to') }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Filter</button>
            </form>
        </div>
        <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Transaksi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggota</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pembiayaan</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal Cair</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Channel</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                </tr></thead>
                <tbody class="divide-y">
                    @php $grandTotal = 0; @endphp
                    @foreach($transaksi as $i => $trx)
                        @php $grandTotal += $trx->total; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 text-sm font-mono text-indigo-600">{{ $trx->no_transaksi }}</td>
                            <td class="px-4 py-2 text-sm font-medium">{{ $trx->pembiayaan?->anggota?->nama_lengkap }}</td>
                            <td class="px-4 py-2 text-sm font-mono">{{ $trx->pembiayaan?->no_pembiayaan }}</td>
                            <td class="px-4 py-2 text-right font-mono font-bold text-green-600">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-center text-sm">{{ ucfirst($trx->channel) }}</td>
                            <td class="px-4 py-2 text-sm">{{ $trx->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-green-50 font-bold"><td colspan="4" class="px-4 py-3 text-sm text-right">GRAND TOTAL</td><td class="px-4 py-3 text-right font-mono text-green-700">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td><td colspan="2"></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
