<x-layouts.auth title="Kode Verifikasi — Bolo Tani">
    <div class="fg-card otp-card" style="margin-top:36px;width:425px">
        <a class="otp-back" href="{{ $backUrl }}"><x-icon name="arrow-left" :size="12" />{{ $backLabel }}</a>
        <div class="otp-ic"><x-icon name="chat" :size="22" /></div>
        <h2>Masukkan kode verifikasi</h2>
        <p class="sub">Kode 6 digit sudah dikirim ke Email<b>{{ $email }}</b></p>

        @if (session('status'))<div class="alert ok" style="text-align:center">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert bad" style="text-align:center">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ $action }}">
            @csrf
            <input type="hidden" name="code" data-otp-value>
            <div class="otp-boxes" data-otp>
                @for ($i = 0; $i < 6; $i++)<input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="Digit {{ $i + 1 }}">@endfor
            </div>
            <div class="otp-count">
                <span data-countdown-label>Belum menerima kode? Kirim ulang dalam <b data-countdown="{{ $seconds }}">00:00</b></span>
                <button type="submit" form="resend" data-resend style="display:none">Kirim ulang kode</button>
            </div>
            <button type="submit" class="btn-main" style="height:40px">{{ $submitLabel ?? 'Verifikasi & Masuk' }} <x-icon name="arrow-right" :size="14" /></button>
        </form>
        <form id="resend" method="POST" action="{{ $resend }}">@csrf<input type="hidden" name="email" value="{{ $email }}"></form>
        <p class="otp-note">Jangan bagikan kode ini kepada siapa pun, termasuk admin Bolo Tani.</p>
    </div>
    <script src="{{ asset('js/ui.js') }}"></script>
</x-layouts.auth>
