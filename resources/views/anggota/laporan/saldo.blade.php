<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Laporan Saldo Anggota</h2>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">🖨️ Print</button>
        </div>
    </x-slot>
    <div class="py-6"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-4 rounded-lg shadow-sm mb-4 flex flex-wrap gap-3 items-end no-print">
            <form method="GET" action="{{ route('anggota.laporan.saldo') }}" class="flex-1 flex flex-wrap gap-3 items-end">
                <div class="min-w-[150px]"><label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua</option><option value="aktif" {{ request('status')=='aktif'?'selected':'' }}>Aktif</option><option value="keluar" {{ request('status')=='keluar'?'selected':'' }}>Keluar</option>
                    </select></div>
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
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pokok</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Wajib</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sukarela</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr></thead>
                <tbody class="divide-y">
                    @php $grandTotal = 0; @endphp
                    @foreach($anggota as $i => $a)
                        @php
                            $pokok = $a->rekeningSimpanan->where('produk.kode_produk', 'SP')->sum('saldo');
                            $wajib = $a->rekeningSimpanan->where('produk.kode_produk', 'SW')->sum('saldo');
                            $sukarela = $a->rekeningSimpanan->where('produk.kode_produk', 'SS')->sum('saldo');
                            $total = $a->rekeningSimpanan->sum('saldo');
                            $grandTotal += $total;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $a->no_anggota }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ $a->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-sm text-center font-mono">Rp {{ number_format($pokok, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-center font-mono">Rp {{ number_format($wajib, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-center font-mono">Rp {{ number_format($sukarela, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-right font-mono font-bold">Rp {{ number_format($total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-indigo-50 font-bold">
                        <td colspan="6" class="px-4 py-3 text-sm text-right">GRAND TOTAL</td>
                        <td class="px-4 py-3 text-sm text-right font-mono text-indigo-700">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div></div>
</x-app-layout>
