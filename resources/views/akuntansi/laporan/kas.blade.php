<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Laporan Kas</h2><button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print">🖨️ Print</button></div></x-slot>
    <div class="space-y-4">
        <div class="bg-white p-4 rounded-lg shadow-sm no-print">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Filter</button>
            </form>
        </div>
        <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Kas</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kas</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Akun</th>
                    
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
                </tr></thead>
                <tbody class="divide-y">
                    @foreach($kasList as $i => $k)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 text-sm font-mono text-indigo-600">{{ $k->kode_kas }}</td>
                            <td class="px-4 py-2 text-sm font-medium">{{ $k->nama_kas }}</td>
                            <td class="px-4 py-2 text-sm">{{ $k->akun?->kode_akun }} - {{ $k->akun?->nama_akun }}</td>
                            
                            <td class="px-4 py-2 text-right font-mono font-bold">Rp {{ number_format($k->saldo, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-indigo-50 font-bold"><td colspan="5" class="px-4 py-3 text-sm text-right">TOTAL KAS</td><td class="px-4 py-3 text-right font-mono text-indigo-700">Rp {{ number_format($totalKas, 0, ',', '.') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
