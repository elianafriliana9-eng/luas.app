<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Laporan Neraca Saldo</h2><button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print">🖨️ Print</button></div></x-slot>
    <div class="space-y-4">
        <div class="bg-white p-4 rounded-lg shadow-sm no-print">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-[120px]"><label class="block text-xs font-medium text-gray-500 mb-1">Bulan</label><select name="bulan" class="w-full border rounded-lg px-3 py-2 text-sm">@foreach(range(1,12) as $m)<option value="{{ $m }}" {{ $m == $periodeBulan ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>@endforeach</select></div>
                <div class="min-w-[100px]"><label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label><input type="number" name="tahun" value="{{ $periodeTahun }}" min="2020" max="2030" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Generate</button>
            </form>
        </div>
        <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
            <div class="p-4 text-center border-b">
                <h3 class="font-bold text-lg">NERACA SALDO</h3>
                <p class="text-sm text-gray-500">Per {{ \Carbon\Carbon::create()->month($periodeBulan)->format('F') }} {{ $periodeTahun }}</p>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Akun</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debet</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Kredit</th>
                </tr></thead>
                <tbody class="divide-y">
                    @foreach($accounts as $a)
                        @if(abs($a->saldo) > 0.01)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm font-mono">{{ $a->kode_akun }}</td>
                                <td class="px-4 py-2 text-sm">{{ $a->nama_akun }}</td>
                                <td class="px-4 py-2 text-right font-mono">{{ $a->posisi_normal === 'debet' && $a->saldo > 0 ? 'Rp ' . number_format($a->saldo, 0, ',', '.') : '' }}</td>
                                <td class="px-4 py-2 text-right font-mono">{{ $a->posisi_normal === 'kredit' && $a->saldo > 0 ? 'Rp ' . number_format($a->saldo, 0, ',', '.') : '' }}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="bg-indigo-50 font-bold">
                        <td colspan="2" class="px-4 py-3 text-sm text-right">TOTAL</td>
                        <td class="px-4 py-3 text-right font-mono text-green-600">Rp {{ number_format($totalDebet, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono text-red-600">Rp {{ number_format($totalKredit, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
