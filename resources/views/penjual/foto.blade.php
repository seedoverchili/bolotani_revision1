<x-layouts.dash title="Ubah Foto Sampul & Profil — Bolo Tani" role="penjual" active="ringkasan">
    <div class="p-top"><a href="{{ route('penjual.dashboard') }}"><x-icon name="arrow-left" :size="12" />Kembali ke Profil Usaha</a></div>
    <h1 class="p-title">Ubah Foto Sampul &amp; Profil</h1>
    <p class="p-sub">Atur foto lapak agar mudah dikenali pembeli.</p>

    @if ($errors->any())<div class="d-alert bad" style="margin-top:14px"><x-icon name="warning" :size="16" /><span>{{ $errors->first() }}</span></div>@endif

    <form method="POST" action="{{ route('penjual.foto.update') }}" enctype="multipart/form-data" id="fotoForm">
        @csrf @method('PUT')
        <input type="hidden" name="cover_pos" id="coverPos" value="{{ $tenant->cover_pos }}">

        <section class="d-card" style="margin-top:14px">
            <h2 style="font-size:14px">Pratinjau Lapak</h2>
            <div class="pv">
                <div class="cover"><img id="pvCover" src="{{ $tenant->coverUrl() }}" alt="" style="object-position:50% {{ $tenant->cover_pos }}%"></div>
                <div class="av"><img id="pvAvatar" src="{{ $tenant->avatarUrl() ?? asset('images/brand/logo-bolotani.png') }}" alt=""></div>
                <div class="meta"><b>{{ $tenant->name }}</b><small>{{ $tenant->main_category }}</small></div>
            </div>
        </section>

        <div class="up-grid">
            <section class="up-card">
                <h3>Foto Sampul</h3>
                <p class="s">Foto latar belakang di bagian atas lapak.</p>
                <div class="drop" data-drop="cover">
                    <x-icon name="cloud-upload" :size="34" :stroke="1.4" />
                    <p>Seret foto ke sini atau <a data-pick="cover">pilih dari perangkat</a></p>
                    <button type="button" class="d-btn" style="background:#0f5d36" data-pick="cover"><x-icon name="upload" :size="12" />Unggah Foto Sampul</button>
                    <input type="file" name="cover" id="cover" accept="image/png,image/jpeg" hidden>
                </div>
                <p class="up-note">JPG atau PNG, maksimal 5 MB.<br>Ukuran disarankan 1600 × 600 piksel.</p>
                <button type="button" class="wide-btn" id="posBtn"><x-icon name="move" :size="12" />Atur Posisi <span id="posLabel" style="color:#98a2b3;font-weight:500">({{ ['0' => 'Atas', '50' => 'Tengah', '100' => 'Bawah'][(string) $tenant->cover_pos] ?? 'Tengah' }})</span></button>
            </section>
            <section class="up-card">
                <h3>Foto Profil</h3>
                <p class="s">Logo atau foto utama lapak Anda.</p>
                <div class="drop" data-drop="avatar">
                    <x-icon name="cloud-upload" :size="34" :stroke="1.4" />
                    <p>Seret foto ke sini atau <a data-pick="avatar">pilih dari perangkat</a></p>
                    <button type="button" class="d-btn" style="background:#0f5d36" data-pick="avatar"><x-icon name="upload" :size="12" />Unggah Foto Profil</button>
                    <input type="file" name="avatar" id="avatar" accept="image/png,image/jpeg" hidden>
                </div>
                <p class="up-note">JPG atau PNG, maksimal 2 MB.<br>Ukuran disarankan 400 × 400 piksel.</p>
                <button type="button" class="wide-btn" data-pick="avatar"><x-icon name="crop" :size="12" />Potong Foto</button>
            </section>
        </div>

        <div class="save-bar">
            <span>Klik Simpan Perubahan untuk memperbarui foto lapak.</span>
            <div class="r">
                <a class="d-btn o" href="{{ route('penjual.dashboard') }}">Batal</a>
                <button type="submit" class="d-btn"><x-icon name="archive" :size="13" />Simpan Perubahan</button>
            </div>
        </div>
    </form>

    <script>
        (function () {
            var limits = { cover: 5 * 1024 * 1024, avatar: 2 * 1024 * 1024 };
            var previews = { cover: document.getElementById('pvCover'), avatar: document.getElementById('pvAvatar') };
            function take(kind, file) {
                if (!file) return;
                if (!/^image\/(png|jpe?g)$/.test(file.type)) { alert('Gunakan gambar JPG atau PNG.'); return; }
                if (file.size > limits[kind]) { alert('Ukuran file melebihi batas ' + (limits[kind] / 1048576) + ' MB.'); return; }
                var dt = new DataTransfer(); dt.items.add(file);
                document.getElementById(kind).files = dt.files;
                var r = new FileReader(); r.onload = function (e) { previews[kind].src = e.target.result; }; r.readAsDataURL(file);
            }
            document.querySelectorAll('[data-pick]').forEach(function (el) {
                el.addEventListener('click', function () { document.getElementById(el.getAttribute('data-pick')).click(); });
            });
            ['cover', 'avatar'].forEach(function (k) {
                document.getElementById(k).addEventListener('change', function () { take(k, this.files[0]); });
                var drop = document.querySelector('[data-drop="' + k + '"]');
                ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('hot'); }); });
                ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('hot'); }); });
                drop.addEventListener('drop', function (e) { take(k, e.dataTransfer.files[0]); });
            });
            var steps = [0, 50, 100], names = { 0: 'Atas', 50: 'Tengah', 100: 'Bawah' };
            document.getElementById('posBtn').addEventListener('click', function () {
                var cur = parseInt(document.getElementById('coverPos').value, 10);
                var next = steps[(steps.indexOf(cur) + 1) % steps.length];
                document.getElementById('coverPos').value = next;
                previews.cover.style.objectPosition = '50% ' + next + '%';
                document.getElementById('posLabel').textContent = '(' + names[next] + ')';
            });
        })();
    </script>
</x-layouts.dash>
