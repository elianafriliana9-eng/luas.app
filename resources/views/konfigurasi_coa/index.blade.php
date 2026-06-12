<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Konfigurasi COA') }}</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 bg-gray-50 border-b text-sm text-gray-600">
                    Mapping kode akun untuk jurnal otomatis modul simpanan.
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Key</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Label</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Akun</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($configs as $c)
                            <tr class="hover:bg-gray-50">
                                <form action="{{ route('konfigurasi-coa.update', $c->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <td class="px-4 py-3 text-sm font-mono text-gray-500">{{ $c->key }}</td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="label" value="{{ old('label', $c->label) }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="kode_akun" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                            @foreach($coaList as $akun)
                                                <option value="{{ $akun->kode_akun }}" {{ $c->kode_akun == $akun->kode_akun ? 'selected' : '' }}>
                                                    {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="keterangan" value="{{ old('keterangan', $c->keterangan) }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition">Simpan</button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
