<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Saldo Anggota</h2>
            <div class="flex gap-2">
                <a href="{{ route('anggota.export.saldo', request()->query()) }}"
                   class="inline-flex items-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm rounded-lg transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition no-print flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
            </div>
        </div>
    </x-slot>
    <div class="py-6"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-4 rounded-t-xl shadow-sm flex flex-wrap gap-3 items-end no-print">
            <form method="GET" action="{{ route('anggota.saldo') }}" class="flex-1 flex flex-wrap gap-3 items-end">
                <div class="min-w-[150px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="aktif" {{ request('status')=='aktif'?'selected':'' }}>Aktif</option>
                        <option value="keluar" {{ request('status')=='keluar'?'selected':'' }}>Keluar</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau no. anggota..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="min-w-[150px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Cabang</label>
                    <select name="cabang_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach($cabangs as $c) <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option> @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Filter</button>
            </form>
        </div>
        <div class="bg-white overflow-x-auto shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Anggota</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pokok</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Wajib</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sukarela</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($anggota as $a)
                        @php
                            $pokok = $a->rekeningSimpanan->where('produk.kode', 'SP')->sum('saldo');
                            $wajib = $a->rekeningSimpanan->where('produk.kode', 'SW')->sum('saldo');
                            $sukarela = $a->rekeningSimpanan->where('produk.kode', 'SS')->sum('saldo');
                            $total = $a->rekeningSimpanan->sum('saldo');
                        @endphp
                        <tr class="hover:bg-indigo-50">
                            <td class="px-4 py-3 text-sm font-mono text-indigo-600">{{ $a->no_anggota }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ $a->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-sm text-center font-mono">Rp {{ number_format($pokok, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-center font-mono">Rp {{ number_format($wajib, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-center font-mono">Rp {{ number_format($sukarela, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-right font-mono font-bold text-indigo-600">Rp {{ number_format($total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center"><a href="{{ route('anggota.show', $a->id) }}" class="text-indigo-600 text-sm hover:underline">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $anggota->links() }}</div>
        </div>
    </div></div>
</x-app-layout>
