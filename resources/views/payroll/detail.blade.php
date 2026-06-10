<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Payroll: ') }}{{ $anggota->nama_lengkap }}
            </h2>
            <a href="{{ route('payroll.index') }}" class="text-indigo-600 hover:text-indigo-900">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Employee Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Karyawan</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Nama Lengkap</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $anggota->nama_lengkap }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">No. Pegawai</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $anggota->no_pegawai ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Departemen</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $anggota->departemen ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Jabatan</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $anggota->jabatan ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Masa Kerja</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $anggota->masa_kerja }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Gaji</h3>
                            @php
                                $totalPotongan = $anggota->pembiayaan->where('auto_potong_gaji', true)->sum('nominal_potongan');
                                $gajiDiterima = ($anggota->gaji_pokok ?? 0) - $totalPotongan;
                            @endphp
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Gaji Pokok</dt>
                                    <dd class="text-sm font-medium text-gray-900 font-mono">Rp {{ number_format($anggota->gaji_pokok ?? 0, 0, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Tanggal Gajian</dt>
                                    <dd class="text-sm font-medium text-gray-900">Setiap tanggal {{ $anggota->tanggal_gajian ?? 25 }}</dd>
                                </div>
                                <div class="flex justify-between border-t pt-2">
                                    <dt class="text-sm text-red-600">Total Potongan/Bulan</dt>
                                    <dd class="text-sm font-medium text-red-600 font-mono">- Rp {{ number_format($totalPotongan, 0, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between border-t pt-2 bg-green-50 p-2 rounded">
                                    <dt class="text-sm font-semibold text-green-700">Gaji Diterima</dt>
                                    <dd class="text-sm font-bold text-green-700 font-mono">Rp {{ number_format($gajiDiterima, 0, ',', '.') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Loans -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pembiayaan dengan Potongan Gaji</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pembiayaan</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Plafon</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sisa Pokok</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Potongan/Bln</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sisa Bulan</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sumber</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($anggota->pembiayaan->where('auto_potong_gaji', true) as $pem)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-mono">{{ $pem->no_pembiayaan }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono">Rp {{ number_format($pem->nominal_disetujui, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono">Rp {{ number_format($pem->saldo_pokok, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono text-red-600">Rp {{ number_format($pem->nominal_potongan, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-center text-sm">{{ $pem->bulan_tersisa_potongan ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center text-sm">{{ $pem->label_sumber_pembayaran }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $pem->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($pem->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Potongan History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Potongan Gaji</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembiayaan</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gaji Bruto</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Potongan</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diterima</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($potonganHistory as $pot)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm">{{ $pot->periode->isoFormat('MMMM YYYY') }}</td>
                                        <td class="px-4 py-3 text-sm font-mono">{{ $pot->pembiayaan?->no_pembiayaan }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono">Rp {{ number_format($pot->gaji_bruto, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono text-red-600">- Rp {{ number_format($pot->nominal_potongan, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-green-600">Rp {{ number_format($pot->gaji_diterima, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 text-xs rounded-full
                                                {{ $pot->status === 'diproses' ? 'bg-green-100 text-green-800' :
                                                   ($pot->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ $pot->label_status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            Belum ada riwayat potongan gaji.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $potonganHistory->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
