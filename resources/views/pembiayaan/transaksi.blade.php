<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Transaksi Pembiayaan</h2></div>
    </x-slot>
    <div class="space-y-4">
        @if(session('success'))<div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]"><input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. transaksi atau nama..." class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[120px]"><select name="jenis" class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">Semua Jenis</option><option value="pencairan" {{ request('jenis')=='pencairan'?'selected':'' }}>Pencairan</option><option value="angsuran" {{ request('jenis')=='angsuran'?'selected':'' }}>Angsuran</option><option value="pelunasan" {{ request('jenis')=='pelunasan'?'selected':'' }}>Pelunasan</option></select></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Filter</button>
                <a href="{{ route('pembiayaan.transaksi') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition">Reset</a>
            </form>
        </div>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Transaksi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggota</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jenis</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pokok</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Channel</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($transaksi as $trx)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-indigo-600">{{ $trx->no_transaksi }}</td>
                            <td class="px-4 py-3 text-sm">{{ $trx->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ $trx->pembiayaan?->anggota?->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($trx->jenis === 'pencairan')<span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 font-bold">Pencairan</span>
                                @elseif($trx->jenis === 'angsuran')<span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 font-bold">Angsuran</span>
                                @else<span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800 font-bold">Pelunasan</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-sm">Rp {{ number_format($trx->nominal_pokok, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-sm">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center text-sm">{{ ucfirst($trx->channel) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $transaksi->links() }}</div>
        </div>
    </div>
</x-app-layout>
