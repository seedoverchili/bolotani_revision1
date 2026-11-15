/* Input koordinat (latitude & longitude). Kecamatan & desa diisi otomatis lewat Nominatim (OpenStreetMap, gratis). */
window.initCoordinateInput = function (o) {
    var $ = function (id) { return document.getElementById(id); };
    var latEl = $(o.lat), lngEl = $(o.lng), timer;

    function reverse(lat, lng) {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=16&accept-language=id&lat=' + lat + '&lon=' + lng)
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    var a = (d && d.address) || {};
                    $(o.kecamatan).value = a.city_district || a.municipality || a.suburb || '';
                    $(o.desa).value = a.village || a.neighbourhood || a.quarter || '';
                }).catch(function () {});
        }, 800);
    }
    function sync() {
        var lat = parseFloat(String(latEl.value).replace(',', '.')), lng = parseFloat(String(lngEl.value).replace(',', '.'));
        var ok = isFinite(lat) && isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180;
        latEl.classList.toggle('is-bad', !ok && latEl.value !== '');
        lngEl.classList.toggle('is-bad', !ok && lngEl.value !== '');
        if (ok) reverse(lat, lng);
    }
    latEl.addEventListener('input', sync);
    lngEl.addEventListener('input', sync);
};
