<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Buat Transaksi Jurnal</h2><a href="{{ route('akuntansi.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a></div>
    </x-slot>
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6">
            @if($errors->any())<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg"><ul class="list-disc ml-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            <form id="jurnalForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Cabang</label>
                        <select name="cabang_id" required class="w-full border rounded-lg px-3 py-2 text-sm">@foreach($cabangList as $c)<option value="{{ $c->id }}">{{ $c->nama }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label><input type="date" name="tanggal" value="{{ now()->format('Y-m-d') }}" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                        <select name="jenis" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="manual">Manual</option><option value="koreksi">Koreksi</option></select></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label><textarea name="keterangan" rows="2" required class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Deskripsi transaksi..."></textarea></div>

                <!-- Entries -->
                <div class="border rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-700">Detail Jurnal</h4>
                        <button type="button" onclick="addRow()" class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">+ Tambah Baris</button>
                    </div>
                    <div id="entries" class="divide-y">
                        <div class="entry-row grid grid-cols-12 gap-2 p-3 items-center">
                            <div class="col-span-5"><select name="entries[0][akun_id]" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">-- Pilih Akun --</option>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->kode_akun }} - {{ $a->nama_akun }}</option>@endforeach</select></div>
                            <div class="col-span-3"><input type="text" name="entries[0][debet]" inputmode="decimal" placeholder="Debet" class="input-rupiah w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="formatRupiah(this);calcTotal()"></div>
                            <div class="col-span-3"><input type="text" name="entries[0][kredit]" inputmode="decimal" placeholder="Kredit" class="input-rupiah w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="formatRupiah(this);calcTotal()"></div>
                            <div class="col-span-1"><button type="button" onclick="this.parentElement.parentElement.remove();calcTotal()" class="text-red-500 hover:text-red-700 text-lg">&times;</button></div>
                        </div>
                        <div class="entry-row grid grid-cols-12 gap-2 p-3 items-center">
                            <div class="col-span-5"><select name="entries[1][akun_id]" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">-- Pilih Akun --</option>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->kode_akun }} - {{ $a->nama_akun }}</option>@endforeach</select></div>
                            <div class="col-span-3"><input type="text" name="entries[1][debet]" inputmode="decimal" placeholder="Debet" class="input-rupiah w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="formatRupiah(this);calcTotal()"></div>
                            <div class="col-span-3"><input type="text" name="entries[1][kredit]" inputmode="decimal" placeholder="Kredit" class="input-rupiah w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="formatRupiah(this);calcTotal()"></div>
                            <div class="col-span-1"><button type="button" onclick="this.parentElement.parentElement.remove();calcTotal()" class="text-red-500 hover:text-red-700 text-lg">&times;</button></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-2 flex justify-between text-sm font-semibold">
                        <span>Total Debet: <span id="totalDebet" class="font-mono text-green-600">Rp 0</span></span>
                        <span>Total Kredit: <span id="totalKredit" class="font-mono text-red-600">Rp 0</span></span>
                        <span id="balanceStatus" class="text-gray-500">● Balance</span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('akuntansi.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">Batal</a>
                    <button type="submit" id="submitBtn" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Simpan Jurnal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let rowCount = 2;
    function addRow() {
        const html = `<div class="entry-row grid grid-cols-12 gap-2 p-3 items-center">
            <div class="col-span-5"><select name="entries[${rowCount}][akun_id]" required class="w-full border rounded-lg px-3 py-2 text-sm"><option value="">-- Pilih Akun --</option>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->kode_akun }} - {{ $a->nama_akun }}</option>@endforeach</select></div>
            <div class="col-span-3"><input type="text" name="entries[${rowCount}][debet]" inputmode="decimal" placeholder="Debet" class="input-rupiah w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="formatRupiah(this);calcTotal()"></div>
            <div class="col-span-3"><input type="text" name="entries[${rowCount}][kredit]" inputmode="decimal" placeholder="Kredit" class="input-rupiah w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="formatRupiah(this);calcTotal()"></div>
            <div class="col-span-1"><button type="button" onclick="this.parentElement.parentElement.remove();calcTotal()" class="text-red-500 hover:text-red-700 text-lg">&times;</button></div>
        </div>`;
        document.getElementById('entries').insertAdjacentHTML('beforeend', html);
        rowCount++;
    }
    function calcTotal() {
        let td = 0, tk = 0;
        document.querySelectorAll('input[name$="[debet]"]').forEach(i => td += parseFloat(unformatRupiah(i.value)) || 0);
        document.querySelectorAll('input[name$="[kredit]"]').forEach(i => tk += parseFloat(unformatRupiah(i.value)) || 0);
        document.getElementById('totalDebet').textContent = 'Rp ' + td.toLocaleString('id-ID');
        document.getElementById('totalKredit').textContent = 'Rp ' + tk.toLocaleString('id-ID');
        const bal = Math.abs(td - tk) < 0.01;
        document.getElementById('balanceStatus').textContent = bal ? '● Balance' : '● Tidak Balance!';
        document.getElementById('balanceStatus').className = bal ? 'text-green-600' : 'text-red-600 font-bold';
        document.getElementById('submitBtn').disabled = !bal;
        document.getElementById('submitBtn').className = bal ? 'px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition' : 'px-6 py-2 bg-gray-400 text-gray-200 text-sm font-medium rounded-lg cursor-not-allowed';
    }
    document.getElementById('jurnalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const entries = [];
        document.querySelectorAll('.entry-row').forEach(row => {
            const akun = row.querySelector('select').value;
            const debet = unformatRupiah(row.querySelector('input[name$="[debet]"]').value);
            const kredit = unformatRupiah(row.querySelector('input[name$="[kredit]"]').value);
            if (akun && (debet || kredit)) entries.push({akun_id: akun, debet: debet || 0, kredit: kredit || 0});
        });
        if (entries.length < 2) { alert('Minimal 2 baris jurnal!'); return; }
        formData.delete('entries');
        entries.forEach((entry, i) => {
            formData.append(`entries[${i}][akun_id]`, entry.akun_id);
            formData.append(`entries[${i}][debet]`, entry.debet);
            formData.append(`entries[${i}][kredit]`, entry.kredit);
        });
        fetch('{{ route("akuntansi.jurnal.store") }}', { method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: formData })
            .then(r => r.text()).then(html => { document.open(); document.write(html); document.close(); });
    });
    </script>
</x-app-layout>
