<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pay Later — Menunggu Approval') }}
            </h2>
            <a href="{{ route('payroll.index') }}" class="text-indigo-600 hover:text-indigo-900">
                ← Kembali ke Payroll
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembiayaan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Angsuran Ke</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payLaterList as $pl)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $pl->anggota->nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">{{ $pl->anggota->no_pegawai ?? $pl->anggota->no_anggota }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ $pl->no_transaksi }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ $pl->pembiayaan?->no_pembiayaan }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">{{ $pl->jadwalAngsuran?->ke ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono font-semibold">
                                            Rp {{ number_format($pl->nominal, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $pl->created_at->format('d M Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($pl->status === 'pending')
                                                <form action="{{ route('payroll.approve_pay_later', $pl->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition"
                                                            onclick="return confirm('Approve pembayaran ini?')">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form action="{{ route('payroll.reject_pay_later', $pl->id) }}" method="POST" class="inline ml-2">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded transition"
                                                            onclick="return confirm('Tolak pembayaran ini?')">
                                                        Reject
                                                    </button>
                                                </form>
                                            @elseif($pl->status === 'approved')
                                                <form action="{{ route('payroll.process_pay_later', $pl->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition"
                                                            onclick="return confirm('Proses pembayaran ini?')">
                                                        Process
                                                    </button>
                                                </form>
                                            @elseif($pl->status === 'lunas')
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Lunas</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">{{ ucfirst($pl->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            Tidak ada permintaan Pay Later yang menunggu approval.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $payLaterList->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
