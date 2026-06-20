<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Setup Kas</h2>
            <button onclick="document.getElementById('modalKas').classList.remove('hidden')" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">+ Tambah Kas</button>
        </div>
    </x-slot>
    <div class="space-y-4">
        @if(session('success'))<div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Filter</button>
            </form>
        </div>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kas</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Akun</th>
                    
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody class="divide-y">
                    @php $totalSaldo = 0; @endphp
                    @foreach($kasList as $k)
                        @php $totalSaldo += $k->saldo; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm font-mono text-indigo-600">{{ $k->kode_kas }}</td>
                            <td class="px-4 py-2 text-sm font-medium">{{ $k->nama_kas }}</td>
                            <td class="px-4 py-2 text-sm">{{ $k->akun?->kode_akun }} - {{ $k->akun?->nama_akun }}</td>
                            
                            <td class="px-4 py-2 text-right font-mono font-bold">Rp {{ number_format($k->saldo, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-center"><span class="px-2 py-0.5 text-xs rounded-full {{ $k->aktif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $k->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="px-4 py-2 text-center">
                                <button onclick="document.getElementById('saldoModal{{ $k->id }}').classList.remove('hidden')" class="p-1 text-blue-600 hover:text-blue-900" title="Update Saldo">💰</button>
                            </td>
                        </tr>
                        <!-- Saldo Modal -->
                        <div id="saldoModal{{ $k->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                            <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4">
                                <h3 class="font-bold text-lg mb-4">Update Saldo: {{ $k->nama_kas }}</h3>
                                <form action="{{ route('akuntansi.kas.saldo', $k->id) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div><label class="block text-xs font-medium text-gray-500 mb-1">Saldo</label><input type="text" name="saldo" value="{{ $k->saldo }}" inputmode="decimal" required class="input-rupiah w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="formatRupiah(this)"></div>
                                    <div class="flex justify-end gap-2 pt-3 border-t"><button type="button" onclick="document.getElementById('saldoModal{{ $k->id }}').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg">Batal</button><button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Update</button></div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    <tr class="bg-indigo-50 font-bold"><td colspan="4" class="px-4 py-3 text-sm text-right">TOTAL SALDO KAS</td><td class="px-4 py-3 text-right font-mono text-indigo-700">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</td><td colspan="2"></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Kas Modal -->
    <div id="modalKas" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="font-bold text-lg mb-4">Tambah Kas Baru</h3>
            <form action="{{ route('akuntansi.kas.store') }}" method="POST" class="space-y-3">
                @csrf
                
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Kode Kas</label><input type="text" name="kode_kas" required class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="KAS-001"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Nama Kas</label><input type="text" name="nama_kas" required class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Kas Kecil"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Akun</label><select name="akun_id" required class="w-full border rounded-lg px-3 py-2 text-sm">@foreach($kasAccounts as $a)<option value="{{ $a->id }}">{{ $a->kode_akun }} - {{ $a->nama_akun }}</option>@endforeach</select></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Keterangan</label><textarea name="keterangan" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea></div>
                <div class="flex justify-end gap-2 pt-3 border-t"><button type="button" onclick="document.getElementById('modalKas').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg">Batal</button><button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Simpan</button></div>
            </form>
        </div>
    </div>
</x-app-layout>
