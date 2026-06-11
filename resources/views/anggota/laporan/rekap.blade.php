<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Laporan Rekap Anggota</h2>
            <div class="flex gap-2">
                <a href="{{ route('anggota.export.rekap') }}" class="px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 transition no-print flex items-center gap-1.5">
                    <span>📥</span> Export Excel
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print">🖨️ Print</button>
            </div>
        </div>
    </x-slot>
    <div class="py-6"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ $totalAnggota }}</div>
                <div class="text-sm text-gray-500 mt-1">Total Anggota</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                <div class="text-3xl font-bold text-green-600">{{ $anggotaAktif }}</div>
                <div class="text-sm text-gray-500 mt-1">Anggota Aktif</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                <div class="text-3xl font-bold text-red-600">{{ $anggotaKeluar }}</div>
                <div class="text-sm text-gray-500 mt-1">Anggota Keluar</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                <div class="text-xl font-bold text-indigo-600">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</div>
                <div class="text-sm text-gray-500 mt-1">Total Simpanan</div>
            </div>
        </div>

        <!-- Per Cabang -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-4 border-b"><h3 class="font-semibold text-gray-900">Rekap per Cabang</h3></div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cabang</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aktif</th>
                </tr></thead>
                <tbody class="divide-y">
                    @foreach($perCabang as $c)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm font-medium">{{ $c->cabang?->nama ?? '-' }}</td>
                            <td class="px-6 py-3 text-sm text-center font-bold">{{ $c->total }}</td>
                            <td class="px-6 py-3 text-sm text-center">{{ $c->aktif }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Per Departemen -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-4 border-b"><h3 class="font-semibold text-gray-900">Rekap per Departemen</h3></div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departemen</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                </tr></thead>
                <tbody class="divide-y">
                    @foreach($perDepartemen as $d)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm font-medium">{{ $d->departemen }}</td>
                            <td class="px-6 py-3 text-sm text-center font-bold">{{ $d->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</x-app-layout>
