<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Laporan Neraca (Balance Sheet)</h2><button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print">🖨️ Print</button></div></x-slot>
    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- ASET -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="bg-blue-600 text-white px-4 py-3 text-center font-bold">ASET</div>
                <table class="min-w-full divide-y divide-gray-200">
                    <tbody class="divide-y">
                        @foreach($aset as $a)
                            @if(abs($a->saldo) > 0.01)
                                <tr class="hover:bg-gray-50"><td class="px-4 py-2 text-sm">{{ $a->kode_akun }} - {{ $a->nama_akun }}</td><td class="px-4 py-2 text-right font-mono font-bold">Rp {{ number_format($a->saldo, 0, ',', '.') }}</td></tr>
                            @endif
                        @endforeach
                        <tr class="bg-blue-50 font-bold"><td class="px-4 py-3 text-sm text-right">TOTAL ASET</td><td class="px-4 py-3 text-right font-mono text-blue-700">Rp {{ number_format($totalAset, 0, ',', '.') }}</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- LIABILITAS + EKUITAS -->
            <div class="space-y-4">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="bg-red-600 text-white px-4 py-3 text-center font-bold">LIABILITAS</div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="divide-y">
                            @foreach($liabilitas as $a)
                                @if(abs($a->saldo) > 0.01)
                                    <tr class="hover:bg-gray-50"><td class="px-4 py-2 text-sm">{{ $a->kode_akun }} - {{ $a->nama_akun }}</td><td class="px-4 py-2 text-right font-mono font-bold">Rp {{ number_format($a->saldo, 0, ',', '.') }}</td></tr>
                                @endif
                            @endforeach
                            <tr class="bg-red-50 font-bold"><td class="px-4 py-3 text-sm text-right">TOTAL LIABILITAS</td><td class="px-4 py-3 text-right font-mono text-red-700">Rp {{ number_format($totalLiabilitas, 0, ',', '.') }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="bg-green-600 text-white px-4 py-3 text-center font-bold">EKUITAS</div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="divide-y">
                            @foreach($ekuitas as $a)
                                @if(abs($a->saldo) > 0.01)
                                    <tr class="hover:bg-gray-50"><td class="px-4 py-2 text-sm">{{ $a->kode_akun }} - {{ $a->nama_akun }}</td><td class="px-4 py-2 text-right font-mono font-bold">Rp {{ number_format($a->saldo, 0, ',', '.') }}</td></tr>
                                @endif
                            @endforeach
                            <tr class="bg-green-50 font-bold"><td class="px-4 py-3 text-sm text-right">TOTAL EKUITAS</td><td class="px-4 py-3 text-right font-mono text-green-700">Rp {{ number_format($totalEkuitas, 0, ',', '.') }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="bg-indigo-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">Total Liabilitas + Ekuitas</p>
                    <p class="text-xl font-bold font-mono text-indigo-700">Rp {{ number_format($totalLiabilitas + $totalEkuitas, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Verification -->
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            @if(abs($totalAset - ($totalLiabilitas + $totalEkuitas)) < 0.01)
                <p class="text-green-600 font-bold">✅ NERACA BALANCE — Aset = Liabilitas + Ekuitas</p>
            @else
                <p class="text-red-600 font-bold">❌ NERACA TIDAK BALANCE — Selisih: Rp {{ number_format(abs($totalAset - ($totalLiabilitas + $totalEkuitas)), 0, ',', '.') }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
