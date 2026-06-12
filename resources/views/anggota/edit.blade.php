<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Anggota: {{ $anggota->nama_lengkap }}</h2>
            <a href="{{ route('anggota.show', $anggota->id) }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                            <ul class="list-disc ml-5 text-sm">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route('anggota.update', $anggota->id) }}" method="POST" enctype="multipart/form-data"
                          x-data="{ loading: false }" x-on:submit="loading = true">
                        @csrf @method('PUT')

                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Data Pribadi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">NIK *</label><input type="text" name="nik" value="{{ old('nik', $anggota->nik) }}" required class="w-full border rounded-lg px-3 py-2 text-sm @error('nik') border-red-500 @enderror">@error('nik') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label><input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $anggota->nama_lengkap) }}" required class="w-full border rounded-lg px-3 py-2 text-sm @error('nama_lengkap') border-red-500 @enderror">@error('nama_lengkap') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir *</label><input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $anggota->tempat_lahir) }}" required class="w-full border rounded-lg px-3 py-2 text-sm @error('tempat_lahir') border-red-500 @enderror">@error('tempat_lahir') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir *</label><input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $anggota->tanggal_lahir?->format('Y-m-d')) }}" required class="w-full border rounded-lg px-3 py-2 text-sm @error('tanggal_lahir') border-red-500 @enderror">@error('tanggal_lahir') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin *</label>
                                <select name="jenis_kelamin" required class="w-full border rounded-lg px-3 py-2 text-sm @error('jenis_kelamin') border-red-500 @enderror">
                                    <option value="L" {{ $anggota->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ $anggota->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">No. HP *</label><input type="text" name="no_hp" value="{{ old('no_hp', $anggota->no_hp) }}" required class="w-full border rounded-lg px-3 py-2 text-sm @error('no_hp') border-red-500 @enderror">@error('no_hp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                            <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Alamat *</label><textarea name="alamat" rows="2" required class="w-full border rounded-lg px-3 py-2 text-sm @error('alamat') border-red-500 @enderror">{{ old('alamat', $anggota->alamat) }}</textarea>@error('alamat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                            <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" name="email" value="{{ old('email', $anggota->email) }}" class="w-full border rounded-lg px-3 py-2 text-sm @error('email') border-red-500 @enderror">@error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Data Karyawan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cabang</label>
                                <select name="cabang_id" class="w-full border rounded-lg px-3 py-2 text-sm @error('cabang_id') border-red-500 @enderror">
                                    <option value="">Pilih</option>
                                    @foreach($cabangs as $c) <option value="{{ $c->id }}" {{ $anggota->cabang_id == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option> @endforeach
                                </select>
                                @error('cabang_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">No. Pegawai</label><input type="text" name="no_pegawai" value="{{ old('no_pegawai', $anggota->no_pegawai) }}" class="w-full border rounded-lg px-3 py-2 text-sm @error('no_pegawai') border-red-500 @enderror">@error('no_pegawai') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Perusahaan</label>
                                <select name="perusahaan_id" class="w-full border rounded-lg px-3 py-2 text-sm @error('perusahaan_id') border-red-500 @enderror">
                                    <option value="">Pilih Perusahaan</option>
                                    @foreach($perusahaans as $p)
                                        <option value="{{ $p->id }}" {{ old('perusahaan_id', $anggota->perusahaan_id) == $p->id ? 'selected' : '' }}>{{ $p->nama }} ({{ $p->kode }})</option>
                                    @endforeach
                                </select>
                                @error('perusahaan_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Gajian</label><input type="number" name="tanggal_gajian" value="{{ old('tanggal_gajian', $anggota->tanggal_gajian) }}" min="1" max="31" class="w-full border rounded-lg px-3 py-2 text-sm @error('tanggal_gajian') border-red-500 @enderror">@error('tanggal_gajian') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai Kerja</label><input type="date" name="tanggal_mulai_kerja" value="{{ old('tanggal_mulai_kerja', $anggota->tanggal_mulai_kerja?->format('Y-m-d')) }}" class="w-full border rounded-lg px-3 py-2 text-sm @error('tanggal_mulai_kerja') border-red-500 @enderror">@error('tanggal_mulai_kerja') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label><input type="text" name="departemen" value="{{ old('departemen', $anggota->departemen) }}" class="w-full border rounded-lg px-3 py-2 text-sm @error('departemen') border-red-500 @enderror">@error('departemen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label><input type="text" name="jabatan" value="{{ old('jabatan', $anggota->jabatan) }}" class="w-full border rounded-lg px-3 py-2 text-sm @error('jabatan') border-red-500 @enderror">@error('jabatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <a href="{{ route('anggota.show', $anggota->id) }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">Batal</a>
                            <button type="submit" :disabled="loading" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2">
                                <span x-show="loading" class="inline-block w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span x-text="loading ? 'Menyimpan...' : 'Update'">Update</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
