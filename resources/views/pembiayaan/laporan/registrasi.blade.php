<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Laporan Registrasi Pembiayaan</h2><button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print">🖨️ Print</button></div>
    </x-slot>
    <div class="space-y-4">
        <div class="bg-white p-4 rounded-lg shadow-sm no-print">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-[140px]"><label class="block text-xs font-medium text-gray-500 mb-1">Dari</label><input type="date" name="from" value="{{ request('from') }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[140px]"><label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label><input type="date" name="to" value="{{ request('to') }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[120px]"><select name="status" class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">Semua</option><option value="disetujui" {{ request('status')=='disetujui'?'selected':'' }}>Disetujui</option><option value="aktif" {{ request('status')=='aktif'?'selected':'' }}>Aktif</option><option value="lunas" {{ request('status')=='lunas'?'selected':'' }}>Lunas</option></select></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Filter</button>
            </form>
        </div>
        <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pembiayaan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggota</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Plafon</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sisa Pokok</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tenor</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Akad</th>
                </tr></thead>
                <tbody class="divide-y">
                    @php $grandTotal = 0; @endphp
                    @foreach($pembiayaan as $i => $pem)
                        @php $grandTotal += $pem->nominal_disetujui; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 text-sm font-mono text-indigo-600">{{ $pem->no_pembiayaan }}</td>
                            <td class="px-4 py-2 text-sm font-medium">{{ $pem->anggota?->nama_lengkap }}</td>
                            <td class="px-4 py-2 text-right font-mono">Rp {{ number_format($pem->nominal_disetujui, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right font-mono font-bold text-red-600">Rp {{ number_format($pem->saldo_pokok, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-center text-sm">{{ $pem->jangka_bulan }} bln</td>
                            <td class="px-4 py-2 text-center"><span class="px-2 py-0.5 text-xs rounded-full {{ $pem->status==='aktif'?'bg-green-100 text-green-800':($pem->status==='lunas'?'bg-blue-100 text-blue-800':'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($pem->status) }}</span></td>
                            <td class="px-4 py-2 text-sm">{{ $pem->tanggal_akad?->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-indigo-50 font-bold"><td colspan="3" class="px-4 py-3 text-sm text-right">GRAND TOTAL</td><td class="px-4 py-3 text-right font-mono text-indigo-700">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td><td colspan="4"></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
