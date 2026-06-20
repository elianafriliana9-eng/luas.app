<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Detail Jurnal {{ $jurnal->no_jurnal }}</h2><a href="{{ route('akuntansi.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a></div></x-slot>
    <div class="max-w-4xl mx-auto space-y-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $jurnal->no_jurnal }}</h3>
                    <p class="text-sm text-gray-500">{{ $jurnal->tanggal }} | {{ ucfirst($jurnal->jenis) }}</p>
                </div>
                @if($jurnal->is_cancelled)<span class="px-3 py-1 bg-red-100 text-red-800 text-xs rounded-full font-bold">DIBATALKAN</span>
                @else<span class="px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full font-bold">AKTIF</span>@endif
            </div>
            <p class="text-sm text-gray-700 mb-4">{{ $jurnal->keterangan }}</p>
            @if($jurnal->is_cancelled)
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg mb-4">
                    <p class="text-sm text-red-800"><strong>Dibatalkan oleh:</strong> {{ $jurnal->cancelledBy?->name ?? '-' }} pada {{ $jurnal->cancelled_at?->format('d M Y H:i') }}</p>
                    <p class="text-sm text-red-800"><strong>Alasan:</strong> {{ $jurnal->alasan_batal }}</p>
                </div>
            @endif
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Akun</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Debet</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Kredit</th>
                </tr></thead>
                <tbody class="divide-y">
                    @php $td = 0; $tk = 0; @endphp
                    @foreach($jurnal->details as $d)
                        @php $td += $d->debet; $tk += $d->kredit; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm font-mono">{{ $d->akun?->kode_akun }}</td>
                            <td class="px-4 py-2 text-sm">{{ $d->akun?->nama_akun }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ $d->debet > 0 ? 'Rp ' . number_format($d->debet, 0, ',', '.') : '' }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ $d->kredit > 0 ? 'Rp ' . number_format($d->kredit, 0, ',', '.') : '' }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-bold"><td colspan="2" class="px-4 py-2 text-sm text-right">TOTAL</td><td class="px-4 py-2 text-right font-mono text-green-600">Rp {{ number_format($td, 0, ',', '.') }}</td><td class="px-4 py-2 text-right font-mono text-red-600">Rp {{ number_format($tk, 0, ',', '.') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
