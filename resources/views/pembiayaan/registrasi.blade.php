<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Registrasi Pembiayaan</h2><a href="{{ route('pembiayaan.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a></div>
    </x-slot>
    <div class="space-y-4">
        @if(session('success'))<div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]"><input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. pengajuan atau nama..." class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[120px]"><select name="status" class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">Semua Status</option><option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option><option value="disetujui" {{ request('status')=='disetujui'?'selected':'' }}>Disetujui</option><option value="ditolak" {{ request('status')=='ditolak'?'selected':'' }}>Ditolak</option></select></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Filter</button>
            </form>
        </div>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pengajuan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anggota</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tenor</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($pengajuan as $pj)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-indigo-600">{{ $pj->no_pengajuan }}</td>
                            <td class="px-4 py-3"><div class="text-sm font-medium">{{ $pj->anggota?->nama_lengkap }}</div><div class="text-xs text-gray-500">{{ $pj->anggota?->perusahaan?->nama ?? '-' }}</div></td>
                            <td class="px-4 py-3 text-sm">{{ $pj->produk?->nama }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-sm">Rp {{ number_format($pj->nominal_diajukan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center text-sm">{{ $pj->jangka_bulan }} bln</td>
                            <td class="px-4 py-3 text-center">
                                @if($pj->status_approval === 'pending')<span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800 font-bold">Pending</span>
                                @elseif($pj->status_approval === 'disetujui')<span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 font-bold">Disetujui</span>
                                @else<span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800 font-bold">Ditolak</span>@endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($pj->status_approval === 'pending')
                                    <button onclick="document.getElementById('modal{{ $pj->id }}').classList.remove('hidden')" class="px-3 py-1 bg-indigo-600 text-white text-xs rounded-lg hover:bg-indigo-700">Review</button>
                                    <!-- Modal -->
                                    <div id="modal{{ $pj->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                                        <div class="bg-white rounded-xl p-6 max-w-lg w-full mx-4">
                                            <h3 class="font-bold text-lg mb-4">Review Pengajuan {{ $pj->no_pengajuan }}</h3>
                                            <div class="space-y-2 text-sm mb-4">
                                                <div class="flex justify-between"><span class="text-gray-500">Anggota</span><span class="font-medium">{{ $pj->anggota?->nama_lengkap }}</span></div>
                                                <div class="flex justify-between"><span class="text-gray-500">Nominal Diajukan</span><span class="font-mono">Rp {{ number_format($pj->nominal_diajukan, 0, ',', '.') }}</span></div>
                                                <div class="flex justify-between"><span class="text-gray-500">Tujuan</span><span>{{ ucfirst($pj->tujuan) }}</span></div>
                                            </div>
                                            <form action="{{ route('pembiayaan.registrasi.approve', $pj->id) }}" method="POST" class="space-y-3">
                                                @csrf
                                                <input type="hidden" name="action" id="action{{ $pj->id }}">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-500 mb-1">Nominal Disetujui</label>
                                                    <input type="text" name="nominal_disetujui" value="{{ $pj->nominal_diajukan }}" inputmode="numeric" class="input-rupiah w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="formatRupiah(this)">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-500 mb-1">Bunga (%/tahun)</label>
                                                    <input type="number" name="bunga_pa" value="{{ $pj->produk?->bunga_pa }}" step="0.5" class="w-full border rounded-lg px-3 py-2 text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                                                    <textarea name="catatan_approval" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                                                </div>
                                                <div class="flex justify-end gap-2 pt-3 border-t">
                                                    <button type="button" onclick="document.getElementById('modal{{ $pj->id }}').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg">Batal</button>
                                                    <button type="button" onclick="document.getElementById('action{{ $pj->id }}').value='reject';this.form.submit()" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">Tolak</button>
                                                    <button type="button" onclick="document.getElementById('action{{ $pj->id }}').value='approve';this.form.submit()" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">Approve</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @elseif($pj->status_approval === 'disetujui' && $pj->pembiayaan)
                                    <a href="{{ route('pembiayaan.pencairan', $pj->pembiayaan->id) }}" class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700">Cairkan</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada pengajuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $pengajuan->links() }}</div>
        </div>
    </div>
</x-app-layout>
