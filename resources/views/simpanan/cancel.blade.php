<x-app-layout>
    <div class="p-8 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header -->
        <section class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold font-headline tracking-tight text-blue-900">Batalkan Transaksi</h2>
                <p class="text-slate-500 text-sm mt-1">Pembatalan akan mengembalikan saldo ke keadaan sebelumnya</p>
            </div>
            <a href="{{ route('simpanan.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
        </section>

        <div class="max-w-2xl space-y-6">

            <!-- Warning -->
            <div class="flex items-start gap-3 p-4 bg-tertiary/10 border border-tertiary/20 rounded-xl">
                <span class="material-symbols-outlined text-tertiary-dark mt-0.5">warning</span>
                <p class="text-sm text-tertiary-dark"><strong>Perhatian:</strong> Membatalkan transaksi akan mengembalikan saldo seperti sebelum transaksi. Aksi ini tidak bisa dibatalkan.</p>
            </div>

            <!-- Transaction Detail -->
            <section class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-tertiary">
                <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-tertiary-dark">receipt</span>
                    Detail Transaksi
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-slate-50"><span class="text-slate-500">No. Transaksi</span><span class="font-data font-semibold text-primary">{{ $transaksi->no_transaksi }}</span></div>
                    <div class="flex justify-between py-2 border-b border-slate-50"><span class="text-slate-500">Tanggal</span><span class="font-medium">{{ $transaksi->created_at->format('d M Y H:i') }}</span></div>
                    <div class="flex justify-between py-2 border-b border-slate-50"><span class="text-slate-500">Anggota</span><span class="font-semibold text-blue-900">{{ $transaksi->rekening?->anggota?->nama_lengkap }}</span></div>
                    <div class="flex justify-between py-2 border-b border-slate-50"><span class="text-slate-500">Rekening</span><span class="font-data">{{ $transaksi->rekening?->produk?->nama }}</span></div>
                    <div class="flex justify-between py-2 border-b border-slate-50"><span class="text-slate-500">Jenis</span><span class="font-bold">{{ $transaksi->label_jenis }}</span></div>
                    <div class="flex justify-between py-2 border-b border-slate-50"><span class="text-slate-500">Nominal</span><span class="font-data font-bold text-lg text-blue-900">Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between py-2 border-b border-slate-50"><span class="text-slate-500">Saldo Sebelum</span><span class="font-data">Rp {{ number_format($transaksi->saldo_sebelum, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between py-2"><span class="text-slate-500">Saldo Sesudah</span><span class="font-data">Rp {{ number_format($transaksi->saldo_sesudah, 0, ',', '.') }}</span></div>
                </div>
            </section>

            <!-- Cancel Form -->
            <section class="bg-white rounded-xl shadow-sm p-6">
                <form action="{{ route('simpanan.cancel.submit', $transaksi->id) }}" method="POST">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alasan Pembatalan <span class="text-danger">*</span></label>
                        <textarea name="alasan" rows="3" required class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="Jelaskan alasan pembatalan..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                        <a href="{{ route('simpanan.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                        <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-tertiary-dark text-white text-sm font-semibold rounded-xl shadow-md shadow-tertiary/20 hover:opacity-90 transition-all active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">block</span>
                            Batalkan Transaksi
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
