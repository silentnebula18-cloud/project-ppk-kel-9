
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("form_rep_2");
    const category = document.getElementById("category");
    const description = document.getElementById("rep_desc");
    const image = document.getElementById("rep_img");

    if (!form) {
        return;
    }

    form.addEventListener("submit", function (event) {

        // 1. Validasi kategori
        if (!category.value) {
            alert("Silakan pilih kategori laporan.");
            event.preventDefault();
            category.focus();
            return;
        }

        // 2. Validasi deskripsi
        const desc = description.value.trim();

        if (desc === "") {
            alert("Deskripsi laporan tidak boleh kosong.");
            event.preventDefault();
            description.focus();
            return;
        }

        if (desc.length > 1000) {
            alert("Deskripsi laporan maksimal 1000 karakter.");
            event.preventDefault();
            description.focus();
            return;
        }

        // 3. Validasi foto
        if (image.files.length > 0) {
            const file = image.files[0];

            // Format file yang diperbolehkan
            const allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            if (!allowedTypes.includes(file.type)) {
                alert("Foto harus berformat JPG, PNG, atau WEBP.");
                event.preventDefault();
                image.focus();
                return;
            }

            // Ukuran maksimal 2 MB
            const maxSize = 2 * 1024 * 1024;

            if (file.size > maxSize) {
                alert("Ukuran foto maksimal 2 MB.");
                event.preventDefault();
                image.focus();
                return;
            }

        }      
    });

});
