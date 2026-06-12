<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Transaksi Akuntansi</h2>
            <div class="flex gap-2">
                <a href="{{ route('akuntansi.jurnal.create') }}" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">+ Buat Jurnal</a>
            </div>
        </div>
    </x-slot>
    <div class="space-y-4">
        @if(session('success'))<div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif

        <!-- Filter -->
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]"><input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. jurnal atau keterangan..." class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[120px]"><select name="jenis" class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">Semua Jenis</option><option value="manual" {{ request('jenis')=='manual'?'selected':'' }}>Manual</option><option value="otomatis" {{ request('jenis')=='otomatis'?'selected':'' }}>Otomatis</option><option value="koreksi" {{ request('jenis')=='koreksi'?'selected':'' }}>Koreksi</option><option value="eliminasi" {{ request('jenis')=='eliminasi'?'selected':'' }}>Eliminasi</option></select></div>
                <div class="min-w-[140px]"><input type="date" name="from" value="{{ request('from') }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[140px]"><input type="date" name="to" value="{{ request('to') }}" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Filter</button>
                <a href="{{ route('akuntansi.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition">Reset</a>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Jurnal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jenis</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($jurnals as $j)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-indigo-600">{{ $j->no_jurnal }}</td>
                            <td class="px-4 py-3 text-sm">{{ $j->tanggal }}</td>
                            <td class="px-4 py-3 text-sm max-w-xs truncate">{{ $j->keterangan }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($j->jenis === 'otomatis')<span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">Otomatis</span>
                                @elseif($j->jenis === 'koreksi')<span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Koreksi</span>
                                @elseif($j->jenis === 'eliminasi')<span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800">Eliminasi</span>
                                @else<span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800">Manual</span>@endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($j->is_cancelled)<span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800 font-bold">DIBATALKAN</span>
                                @elseif($j->is_reversed)<span class="px-2 py-0.5 text-xs rounded-full bg-orange-100 text-orange-800">DIREVERSE</span>
                                @else<span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>@endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-1">
                                    <a href="{{ route('akuntansi.jurnal.detail', $j->id) }}" class="p-1 text-indigo-600 hover:text-indigo-900" title="Detail"></a>
                                    @if(auth()->user()->role === 'super_admin' && !$j->is_cancelled && $j->jenis === 'manual')
                                        <a href="{{ route('akuntansi.jurnal.revisi', $j->id) }}" class="p-1 text-blue-600 hover:text-blue-900" title="Revisi">✏️</a>
                                        <a href="{{ route('akuntansi.jurnal.batal', $j->id) }}" class="p-1 text-red-600 hover:text-red-900" title="Batalkan">🚫</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada transaksi akuntansi.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $jurnals->links() }}</div>
        </div>
    </div>
</x-app-layout>
