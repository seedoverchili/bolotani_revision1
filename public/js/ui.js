/* Bolo Tani — small UI helpers (no framework) */
(function () {
    // show/hide password
    document.querySelectorAll('[data-eye]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-eye'));
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('.on').style.display = show ? 'none' : '';
            btn.querySelector('.off').style.display = show ? '' : 'none';
        });
    });

    // OTP boxes
    var boxes = document.querySelectorAll('[data-otp] input');
    var hidden = document.querySelector('[data-otp-value]');
    function sync() {
        if (hidden) hidden.value = Array.prototype.map.call(boxes, function (b) { return b.value; }).join('');
    }
    boxes.forEach(function (box, i) {
        box.addEventListener('input', function () {
            box.value = box.value.replace(/\D/g, '').slice(-1);
            if (box.value && boxes[i + 1]) boxes[i + 1].focus();
            sync();
        });
        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !box.value && boxes[i - 1]) boxes[i - 1].focus();
        });
        box.addEventListener('paste', function (e) {
            var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, boxes.length);
            if (!text) return;
            e.preventDefault();
            text.split('').forEach(function (ch, k) { if (boxes[k]) boxes[k].value = ch; });
            (boxes[Math.min(text.length, boxes.length - 1)]).focus();
            sync();
        });
    });
    if (boxes[0]) boxes[0].focus();

    // resend countdown
    var counter = document.querySelector('[data-countdown]');
    if (counter) {
        var left = parseInt(counter.getAttribute('data-countdown'), 10) || 0;
        var resend = document.querySelector('[data-resend]');
        var label = document.querySelector('[data-countdown-label]');
        var tick = function () {
            var m = String(Math.floor(left / 60)).padStart(2, '0');
            var s = String(left % 60).padStart(2, '0');
            counter.textContent = m + ':' + s;
            if (left <= 0) {
                if (label) label.style.display = 'none';
                if (resend) resend.style.display = '';
                return;
            }
            left--;
            setTimeout(tick, 1000);
        };
        tick();
    }
})();

// filter baris tabel dari kolom pencarian dashboard
(function () {
    var input = document.querySelector('[data-filter-input]');
    if (!input) return;
    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        document.querySelectorAll('[data-filter-row]').forEach(function (row) {
            row.style.display = !q || row.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
        });
    });
})();

// dialog konfirmasi: <form data-confirm="pesan"> atau tombol submit dengan data-confirm
(function () {
    var overlay;
    function build() {
        overlay = document.createElement('div');
        overlay.className = 'cf-overlay';
        overlay.innerHTML = '<div class="cf-box" role="alertdialog" aria-modal="true" aria-labelledby="cfTitle">' +
            '<div class="cf-ic"></div><h3 id="cfTitle"></h3><p></p>' +
            '<div class="cf-act"><button type="button" class="cf-no">Batal</button><button type="button" class="cf-yes"></button></div></div>';
        document.body.appendChild(overlay);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && overlay.classList.contains('show')) close(); });
        overlay.querySelector('.cf-no').addEventListener('click', close);
    }
    function close() { overlay.classList.remove('show'); }
    function ask(src, onYes) {
        if (!overlay) build();
        var danger = src.getAttribute('data-confirm-tone') === 'danger';
        overlay.querySelector('h3').textContent = src.getAttribute('data-confirm-title') || 'Yakin?';
        overlay.querySelector('p').textContent = src.getAttribute('data-confirm');
        var ic = overlay.querySelector('.cf-ic');
        ic.className = 'cf-ic ' + (danger ? 'danger' : 'ok');
        ic.textContent = danger ? '!' : '?';
        var yes = overlay.querySelector('.cf-yes');
        yes.textContent = src.getAttribute('data-confirm-ok') || 'Ya';
        yes.className = 'cf-yes ' + (danger ? 'danger' : 'ok');
        var fresh = yes.cloneNode(true); yes.parentNode.replaceChild(fresh, yes);
        fresh.addEventListener('click', function () { close(); onYes(); });
        overlay.classList.add('show');
        fresh.focus();
    }
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.getAttribute('data-confirmed')) return;
        var src = e.submitter && e.submitter.hasAttribute('data-confirm') ? e.submitter : (form.hasAttribute('data-confirm') ? form : null);
        if (!src) return;
        e.preventDefault();
        ask(src, function () {
            form.setAttribute('data-confirmed', '1');
            if (e.submitter && e.submitter.name) {
                var h = document.createElement('input'); h.type = 'hidden'; h.name = e.submitter.name; h.value = e.submitter.value; form.appendChild(h);
            }
            form.submit();
        });
    });
})();
