<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Laporan Anggota Keluar</h2>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print">🖨️ Print</button>
        </div>
    </x-slot>
    <div class="py-6"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-4 rounded-lg shadow-sm mb-4 flex flex-wrap gap-3 items-end no-print">
            <form method="GET" action="{{ route('anggota.laporan.keluar') }}" class="flex-1 flex flex-wrap gap-3 items-end">
                <div class="min-w-[150px]"><label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label><input type="date" name="from" value="{{ request('from') }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[150px]"><label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label><input type="date" name="to" value="{{ request('to') }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Filter</button>
            </form>
        </div>
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Anggota</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departemen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Keluar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alasan</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($anggota as $i => $a)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm">{{ $i + 1 }}</td>
                            <td class="px-6 py-3 text-sm font-mono">{{ $a->no_anggota }}</td>
                            <td class="px-6 py-3 text-sm font-medium">{{ $a->nama_lengkap }}</td>
                            <td class="px-6 py-3 text-sm">{{ $a->departemen ?? '-' }}</td>
                            <td class="px-6 py-3 text-sm">{{ $a->tanggal_keluar?->format('d M Y') }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $a->alasan_keluar ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada data anggota keluar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
</x-app-layout>
