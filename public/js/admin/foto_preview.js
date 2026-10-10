// Pratinjau dan pengecekan awal foto fasilitas di browser.
// Pengecekan sebenarnya tetap dilakukan di server (upload_helper.php).
(function () {
    var input = document.getElementById('fac_photo');
    if (!input) return;

    var preview = document.getElementById('foto_preview');
    var errorEl = document.getElementById('foto_error');
    var maxBytes = 2 * 1024 * 1024;
    var allowed = ['image/jpeg', 'image/png', 'image/webp'];

    input.addEventListener('change', function () {
        errorEl.textContent = '';
        preview.hidden = true;

        var file = input.files[0];
        if (!file) return;

        if (allowed.indexOf(file.type) === -1) {
            errorEl.textContent = 'Format foto harus JPG, PNG, atau WebP.';
            input.value = '';
            return;
        }
        if (file.size > maxBytes) {
            errorEl.textContent = 'Ukuran foto maksimal 2 MB.';
            input.value = '';
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
    });
})();