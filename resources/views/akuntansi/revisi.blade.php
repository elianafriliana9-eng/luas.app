<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800">Revisi Jurnal {{ $jurnal->no_jurnal }}</h2><a href="{{ route('akuntansi.index') }}" class="text-indigo-600 hover:text-indigo-900">← Kembali</a></div></x-slot>
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6">
            @if($errors->any())<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg"><ul class="list-disc ml-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            <form id="revisiForm" class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Revisi</label><textarea name="keterangan" rows="2" required class="w-full border rounded-lg px-3 py-2 text-sm">{{ $jurnal->keterangan }}</textarea></div>
                <div class="border rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-700">Entry Jurnal</h4>
                        <button type="button" onclick="addRow()" class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">+ Tambah Baris</button>
                    </div>
                    <div id="entries" class="divide-y">
                        @foreach($jurnal->details as $i => $d)
                            <div class="entry-row grid grid-cols-12 gap-2 p-3 items-center">
                                <div class="col-span-5"><select name="entries[{{ $i }}][akun_id]" required class="w-full border rounded-lg px-3 py-2 text-sm">@foreach($accounts as $a)<option value="{{ $a->id }}" {{ $d->akun_id == $a->id ? 'selected' : '' }}>{{ $a->kode_akun }} - {{ $a->nama_akun }}</option>@endforeach</select></div>
                                <div class="col-span-3"><input type="number" name="entries[{{ $i }}][debet]" step="0.01" min="0" value="{{ $d->debet > 0 ? $d->debet : '' }}" placeholder="Debet" class="w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="calcTotal()"></div>
                                <div class="col-span-3"><input type="number" name="entries[{{ $i }}][kredit]" step="0.01" min="0" value="{{ $d->kredit > 0 ? $d->kredit : '' }}" placeholder="Kredit" class="w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="calcTotal()"></div>
                                <div class="col-span-1"><button type="button" onclick="this.parentElement.parentElement.remove();calcTotal()" class="text-red-500 hover:text-red-700 text-lg">&times;</button></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="bg-gray-50 px-4 py-2 flex justify-between text-sm font-semibold">
                        <span>Total Debet: <span id="totalDebet" class="font-mono text-green-600">Rp 0</span></span>
                        <span>Total Kredit: <span id="totalKredit" class="font-mono text-red-600">Rp 0</span></span>
                        <span id="balanceStatus">● Balance</span>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('akuntansi.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition">Batal</a>
                    <button type="submit" id="submitBtn" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Simpan Revisi</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    let rowCount = {{ $jurnal->details->count() }};
    function addRow() {
        const html = `<div class="entry-row grid grid-cols-12 gap-2 p-3 items-center">
            <div class="col-span-5"><select name="entries[${rowCount}][akun_id]" required class="w-full border rounded-lg px-3 py-2 text-sm">@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->kode_akun }} - {{ $a->nama_akun }}</option>@endforeach</select></div>
            <div class="col-span-3"><input type="number" name="entries[${rowCount}][debet]" step="0.01" min="0" placeholder="Debet" class="w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="calcTotal()"></div>
            <div class="col-span-3"><input type="number" name="entries[${rowCount}][kredit]" step="0.01" min="0" placeholder="Kredit" class="w-full border rounded-lg px-3 py-2 text-sm font-mono" oninput="calcTotal()"></div>
            <div class="col-span-1"><button type="button" onclick="this.parentElement.parentElement.remove();calcTotal()" class="text-red-500 hover:text-red-700 text-lg">&times;</button></div>
        </div>`;
        document.getElementById('entries').insertAdjacentHTML('beforeend', html); rowCount++;
    }
    function calcTotal() {
        let td = 0, tk = 0;
        document.querySelectorAll('input[name$="[debet]"]').forEach(i => td += parseFloat(i.value) || 0);
        document.querySelectorAll('input[name$="[kredit]"]').forEach(i => tk += parseFloat(i.value) || 0);
        document.getElementById('totalDebet').textContent = 'Rp ' + td.toLocaleString('id-ID');
        document.getElementById('totalKredit').textContent = 'Rp ' + tk.toLocaleString('id-ID');
        const bal = Math.abs(td - tk) < 0.01;
        document.getElementById('balanceStatus').textContent = bal ? '● Balance' : '● Tidak Balance!';
        document.getElementById('balanceStatus').className = bal ? 'text-green-600' : 'text-red-600 font-bold';
        document.getElementById('submitBtn').disabled = !bal;
        document.getElementById('submitBtn').className = bal ? 'px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition' : 'px-6 py-2 bg-gray-400 text-gray-200 text-sm font-medium rounded-lg cursor-not-allowed';
    }
    calcTotal();
    document.getElementById('revisiForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const entries = [];
        document.querySelectorAll('.entry-row').forEach(row => {
            const akun = row.querySelector('select').value;
            const debet = row.querySelector('input[name$="[debet]"]').value;
            const kredit = row.querySelector('input[name$="[kredit]"]').value;
            if (akun && (debet || kredit)) entries.push({akun_id: akun, debet: debet || 0, kredit: kredit || 0});
        });
        formData.delete('entries');
        entries.forEach((entry, i) => {
            formData.append(`entries[${i}][akun_id]`, entry.akun_id);
            formData.append(`entries[${i}][debet]`, entry.debet);
            formData.append(`entries[${i}][kredit]`, entry.kredit);
        });
        fetch('{{ route("akuntansi.jurnal.revisi.submit", $jurnal->id) }}', { method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: formData })
            .then(r => r.text()).then(html => { document.open(); document.write(html); document.close(); });
    });
    </script>
</x-app-layout>
