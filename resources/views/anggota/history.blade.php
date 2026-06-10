<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">History Transaksi — {{ $anggota->nama_lengkap }}</h2>
            <a href="{{ route('anggota.show', $anggota->id) }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a>
        </div>
    </x-slot>
    <div class="py-6"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rekening</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($history as $trx)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm">{{ $trx->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-3 text-sm font-mono">{{ $trx->rekening?->produk?->nama_produk }}</td>
                            <td class="px-6 py-3"><span class="px-2 py-0.5 text-xs rounded-full {{ in_array($trx->jenis, ['setor', 'pinbuk_masuk']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ ucfirst($trx->jenis) }}</span></td>
                            <td class="px-6 py-3 text-sm">{{ $trx->keterangan }}</td>
                            <td class="px-6 py-3 text-right font-mono font-bold {{ in_array($trx->jenis, ['setor', 'pinbuk_masuk']) ? 'text-green-600' : 'text-red-600' }}">
                                {{ in_array($trx->jenis, ['setor', 'pinbuk_masuk']) ? '+' : '-' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-3 border-t">{{ $history->links() }}</div>
        </div>
    </div></div>
</x-app-layout>
