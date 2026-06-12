<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Laporan Anggota Masuk</h2>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
        </div>
    </x-slot>
    <div class="py-6"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-4 rounded-lg shadow-sm mb-4 flex flex-wrap gap-3 items-end no-print">
            <form method="GET" action="{{ route('anggota.laporan.masuk') }}" class="flex-1 flex flex-wrap gap-3 items-end">
                <div class="min-w-[150px]"><label class="block text-xs font-medium text-gray-500 mb-1">Cabang</label>
                    <select name="cabang_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua</option>@foreach($cabangs as $c) <option value="{{ $c->id }}" {{ request('cabang_id')==$c->id?'selected':'' }}>{{ $c->nama }}</option> @endforeach
                    </select></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Filter</button>
            </form>
        </div>
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Anggota</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cabang</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Masuk</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($anggota as $i => $a)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $a->no_anggota }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ $a->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-sm">{{ $a->cabang?->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                @if($a->status === 'pending_aktif')
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700">Pending</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700">Aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $a->tanggal_masuk?->format('d M Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada data anggota masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
</x-app-layout>