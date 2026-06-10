<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Pembatalan Jurnal</h2><a href="{{ route('akuntansi.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a></div></x-slot>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                <p class="text-sm text-orange-800"><strong>⚠️ Pembatalan Jurnal:</strong> {{ $jurnal->no_jurnal }}</p>
                <p class="text-sm text-orange-800 mt-1">Aksi ini akan membuat jurnal reversal otomatis dan mengembalikan saldo kas.</p>
            </div>
            <div class="mb-4">
                <h4 class="text-sm font-semibold mb-2">Detail Jurnal yang akan dibatalkan:</h4>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Akun</th><th class="px-3 py-2 text-right">Debet</th><th class="px-3 py-2 text-right">Kredit</th></tr></thead>
                    <tbody class="divide-y">@foreach($jurnal->details as $d)<tr><td class="px-3 py-2">{{ $d->akun?->kode_akun }} - {{ $d->akun?->nama_akun }}</td><td class="px-3 py-2 text-right font-mono">Rp {{ number_format($d->debet, 0, ',', '.') }}</td><td class="px-3 py-2 text-right font-mono">Rp {{ number_format($d->kredit, 0, ',', '.') }}</td></tr>@endforeach</tbody>
                </table>
            </div>
            @if($errors->any())<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg"><ul class="list-disc ml-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('akuntansi.jurnal.batal.submit', $jurnal->id) }}" method="POST">
                @csrf
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-1">Alasan Pembatalan <span class="text-red-500">*</span></label><textarea name="alasan" rows="3" required class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Jelaskan alasan pembatalan..."></textarea></div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('akuntansi.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">Batal</a>
                    <button type="submit" onclick="return confirm('Yakin membatalkan jurnal ini?')" class="px-6 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">🚫 Batalkan Jurnal</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
