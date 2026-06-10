<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Setup Ledger / Akun (COA)</h2>
            <button onclick="document.getElementById('modalCoa').classList.remove('hidden')" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">+ Tambah Akun</button>
        </div>
    </x-slot>
    <div class="space-y-4">
        @if(session('success'))<div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]"><input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama akun..." class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div class="min-w-[140px]"><select name="kelompok" class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">Semua Kelompok</option><option value="aset" {{ request('kelompok')=='aset'?'selected':'' }}>Aset</option><option value="liabilitas" {{ request('kelompok')=='liabilitas'?'selected':'' }}>Liabilitas</option><option value="ekuitas" {{ request('kelompok')=='ekuitas'?'selected':'' }}>Ekuitas</option><option value="pendapatan" {{ request('kelompok')=='pendapatan'?'selected':'' }}>Pendapatan</option><option value="beban" {{ request('kelompok')=='beban'?'selected':'' }}>Beban</option></select></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">Filter</button>
            </form>
        </div>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Akun</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Kelompok</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Posisi</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tipe</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody class="divide-y">
                    @foreach($coa as $a)
                        <tr class="hover:bg-gray-50 {{ $a->is_header ? 'bg-gray-50 font-bold' : '' }}">
                            <td class="px-4 py-2 text-sm font-mono {{ $a->is_header ? 'text-indigo-600' : '' }}">{{ $a->kode_akun }}</td>
                            <td class="px-4 py-2 text-sm {{ $a->is_header ? 'font-semibold' : '' }}">{{ $a->nama_akun }}</td>
                            <td class="px-4 py-2 text-center text-xs"><span class="px-2 py-0.5 rounded-full {{ match($a->kelompok){'aset'=>'bg-blue-100 text-blue-800','liabilitas'=>'bg-red-100 text-red-800','ekuitas'=>'bg-green-100 text-green-800','pendapatan'=>'bg-purple-100 text-purple-800','beban'=>'bg-orange-100 text-orange-800'} }}">{{ ucfirst($a->kelompok) }}</span></td>
                            <td class="px-4 py-2 text-center text-xs">{{ ucfirst($a->posisi_normal) }}</td>
                            <td class="px-4 py-2 text-center text-xs">{{ $a->is_header ? 'Header' : 'Detail' }}</td>
                            <td class="px-4 py-2 text-center"><span class="px-2 py-0.5 text-xs rounded-full {{ $a->aktif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $a->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="px-4 py-2 text-center">
                                <button onclick="document.getElementById('editModal{{ $a->id }}').classList.remove('hidden')" class="p-1 text-blue-600 hover:text-blue-900" title="Edit">✏️</button>
                            </td>
                        </tr>
                        <!-- Edit Modal -->
                        <div id="editModal{{ $a->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                                <h3 class="font-bold text-lg mb-4">Edit Akun {{ $a->kode_akun }}</h3>
                                <form action="{{ route('akuntansi.coa.update', $a->id) }}" method="POST" class="space-y-3">
                                    @csrf @method('PUT')
                                    <div><label class="block text-xs font-medium text-gray-500 mb-1">Nama Akun</label><input type="text" name="nama_akun" value="{{ $a->nama_akun }}" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                                    <div><label class="block text-xs font-medium text-gray-500 mb-1">Kelompok</label><select name="kelompok" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="aset" {{ $a->kelompok=='aset'?'selected':'' }}>Aset</option><option value="liabilitas" {{ $a->kelompok=='liabilitas'?'selected':'' }}>Liabilitas</option><option value="ekuitas" {{ $a->kelompok=='ekuitas'?'selected':'' }}>Ekuitas</option><option value="pendapatan" {{ $a->kelompok=='pendapatan'?'selected':'' }}>Pendapatan</option><option value="beban" {{ $a->kelompok=='beban'?'selected':'' }}>Beban</option></select></div>
                                    <div><label class="block text-xs font-medium text-gray-500 mb-1">Posisi Normal</label><select name="posisi_normal" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="debet" {{ $a->posisi_normal=='debet'?'selected':'' }}>Debet</option><option value="kredit" {{ $a->posisi_normal=='kredit'?'selected':'' }}>Kredit</option></select></div>
                                    <div class="flex items-center gap-2"><input type="checkbox" name="aktif" id="aktif{{ $a->id }}" value="1" {{ $a->aktif ? 'checked' : '' }} class="rounded"><label for="aktif{{ $a->id }}" class="text-sm">Aktif</label></div>
                                    <div class="flex justify-end gap-2 pt-3 border-t"><button type="button" onclick="document.getElementById('editModal{{ $a->id }}').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg">Batal</button><button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Simpan</button></div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $coa->links() }}</div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="modalCoa" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="font-bold text-lg mb-4">Tambah Akun Baru</h3>
            <form action="{{ route('akuntansi.coa.store') }}" method="POST" class="space-y-3">
                @csrf
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Kode Akun</label><input type="text" name="kode_akun" required class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="1101"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Nama Akun</label><input type="text" name="nama_akun" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Kelompok</label><select name="kelompok" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="aset">Aset</option><option value="liabilitas">Liabilitas</option><option value="ekuitas">Ekuitas</option><option value="pendapatan">Pendapatan</option><option value="beban">Beban</option></select></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Posisi Normal</label><select name="posisi_normal" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="debet">Debet</option><option value="kredit">Kredit</option></select></div>
                <div class="flex items-center gap-2"><input type="checkbox" name="is_header" value="1" class="rounded"><label class="text-sm">Header (Group)</label></div>
                <div class="flex justify-end gap-2 pt-3 border-t"><button type="button" onclick="document.getElementById('modalCoa').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg">Batal</button><button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">Simpan</button></div>
            </form>
        </div>
    </div>
</x-app-layout>
