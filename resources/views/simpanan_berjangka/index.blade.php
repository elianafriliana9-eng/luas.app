<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Simpanan Berjangka</h2>
                <p class="text-slate-500 text-sm mt-1">Daftar deposito anggota</p>
            </div>
            <a href="{{ route('simpanan-berjangka.create') }}" class="flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Deposito Baru
            </a>
        </section>

        <section class="bg-white p-5 rounded-xl shadow-sm">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="min-w-[200px] flex-1">
                    <input type="text" name="search" placeholder="Cari no. deposito atau anggota..." value="{{ request('search') }}"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div class="min-w-[140px]">
                    <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status')=='aktif'?'selected':'' }}>Aktif</option>
                        <option value="jatuh_tempo" {{ request('status')=='jatuh_tempo'?'selected':'' }}>Jatuh Tempo</option>
                        <option value="cair" {{ request('status')=='cair'?'selected':'' }}>Dicairkan</option>
                        <option value="perpanjang" {{ request('status')=='perpanjang'?'selected':'' }}>Perpanjang</option>
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">Filter</button>
            </form>
        </section>

        <section class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">No. Deposito</th>
                            <th class="px-6 py-4">Anggota</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4 text-right">Bunga</th>
                            <th class="px-6 py-4">Jangka</th>
                            <th class="px-6 py-4">Mulai</th>
                            <th class="px-6 py-4">Jatuh Tempo</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($deposito as $i => $d)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-3 text-sm text-slate-500">{{ $deposito->firstItem() + $i }}</td>
                                <td class="px-6 py-3 font-data text-sm font-semibold text-primary">{{ $d->no_deposito }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-blue-900">{{ $d->anggota?->nama_lengkap }}</td>
                                <td class="px-6 py-3 text-right font-data font-bold text-sm text-blue-900">Rp {{ number_format($d->nominal, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right font-data text-sm text-slate-600">{{ $d->bunga_pa }}%</td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $d->jangka_bulan }} bln</td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $d->tanggal_mulai?->format('d M Y') }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $d->tanggal_jatuh_tempo?->format('d M Y') }}</td>
                                <td class="px-6 py-3 text-center">
                                    @if($d->status === 'aktif')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-secondary/10 text-secondary">Aktif</span>
                                    @elseif($d->status === 'jatuh_tempo')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-tertiary/10 text-tertiary-dark">Jatuh Tempo</span>
                                    @elseif($d->status === 'cair')
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-danger/10 text-danger">Dicairkan</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-primary/10 text-primary">Perpanjang</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <a href="{{ route('simpanan-berjangka.show', $d->id) }}" class="text-primary hover:underline text-sm font-semibold">Detail</a>
                                    @if(in_array($d->status, ['jatuh_tempo', 'aktif']))
                                        <a href="{{ route('simpanan-berjangka.cair', $d->id) }}" class="text-tertiary-dark hover:underline text-sm font-semibold ml-2">Cairkan</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-16 text-center">
                                    <span class="material-symbols-outlined text-[48px] text-slate-300 mb-3 block">savings</span>
                                    <p class="text-sm text-slate-400 font-medium">Belum ada deposito</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                {{ $deposito->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
