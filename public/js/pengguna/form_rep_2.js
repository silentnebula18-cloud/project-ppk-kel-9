document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("form_rep_2");
    const category = document.getElementById("category");
    const description = document.getElementById("rep_desc");
    const image = document.getElementById("rep_img");

    const categoryError = document.getElementById("category_error");
    const descriptionError = document.getElementById("rep_desc_error");
    const imageError = document.getElementById("rep_img_error");
    const compressionStatus = document.getElementById("compression_status");

    if (!form) {
        return;
    }

    const MAX_ORIGINAL_SIZE = 5 * 1024 * 1024;
    const MAX_COMPRESSED_SIZE = 2 * 1024 * 1024;

    const allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    let compressedFile = null;
    let originalFile = null;
    let isProcessing = false;

    function showError(element, message) {
        if (element) {
            element.textContent = message;
        }
    }

    function clearError(element) {
        showError(element, "");
    }

    function clearAllErrors() {
        clearError(categoryError);
        clearError(descriptionError);
        clearError(imageError);
        showError(compressionStatus, "");
    }

    image.addEventListener("change", function () {
        compressedFile = null;
        originalFile = null;

        clearError(imageError);
        showError(compressionStatus, "");

        const file = image.files[0];

        if (!file) {
            return;
        }

        if (!allowedTypes.includes(file.type)) {
            showError(
                imageError,
                "Foto harus berformat JPG, PNG, atau WEBP."
            );
            image.value = "";
            return;
        }

        if (file.size > MAX_ORIGINAL_SIZE) {
            showError(
                imageError,
                "Ukuran foto sebelum kompresi maksimal 5 MB."
            );
            image.value = "";
        }
    });

    category.addEventListener("change", function () {
        clearError(categoryError);
    });

    description.addEventListener("input", function () {
        clearError(descriptionError);
    });

    function compressToWebp(file) {
        return new Promise(function (resolve, reject) {
            const img = new Image();
            const objectUrl = URL.createObjectURL(file);

            img.onload = function () {
                URL.revokeObjectURL(objectUrl);

                const canvas = document.createElement("canvas");
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;

                const context = canvas.getContext("2d");

                if (!context) {
                    reject(
                        new Error("Browser tidak mendukung pemrosesan foto.")
                    );
                    return;
                }

                context.drawImage(img, 0, 0);

                canvas.toBlob(
                    function (blob) {
                        if (!blob) {
                            reject(new Error("Foto gagal dikompres."));
                            return;
                        }

                        const webpFile = new File(
                            [blob],
                            "report.webp",
                            {
                                type: "image/webp",
                                lastModified: Date.now()
                            }
                        );

                        resolve(webpFile);
                    },
                    "image/webp",
                    0.8
                );
            };

            img.onerror = function () {
                URL.revokeObjectURL(objectUrl);
                reject(new Error("Foto tidak dapat dibaca."));
            };

            img.src = objectUrl;
        });
    }

    form.addEventListener("submit", async function (event) {
        event.preventDefault();

        if (isProcessing) {
            return;
        }

        clearAllErrors();

        // 1. Validasi kategori
        if (!category.value) {
            showError(categoryError, "Silakan pilih kategori laporan.");
            category.focus();
            return;
        }

        // 2. Validasi deskripsi
        const desc = description.value.trim();

        if (desc === "") {
            showError(
                descriptionError,
                "Deskripsi laporan tidak boleh kosong."
            );
            description.focus();
            return;
        }

        if (desc.length > 1000) {
            showError(
                descriptionError,
                "Deskripsi laporan maksimal 1000 karakter."
            );
            description.focus();
            return;
        }

        // 3. Foto wajib dipilih
        const file = image.files[0];

        if (!file) {
            showError(imageError, "Foto laporan wajib diunggah.");
            image.focus();
            return;
        }

        // 4. Validasi format foto
        if (!allowedTypes.includes(file.type)) {
            showError(
                imageError,
                "Foto harus berformat JPG, PNG, atau WEBP."
            );
            image.focus();
            return;
        }

        // 5. Validasi ukuran foto asli
        if (file.size > MAX_ORIGINAL_SIZE) {
            showError(
                imageError,
                "Ukuran foto sebelum kompresi maksimal 5 MB."
            );
            image.focus();
            return;
        }

        // 6. Jika file yang sama sudah dikompres, kirim tanpa kompresi ulang
        if (compressedFile && originalFile === file) {
            if (compressedFile.size > MAX_COMPRESSED_SIZE) {
                showError(
                    imageError,
                    "Hasil kompresi melebihi 2 MB. Silakan pilih foto lain."
                );
                image.focus();
                return;
            }

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(compressedFile);
            image.files = dataTransfer.files;

            form.submit();
            return;
        }

        // 7. Kompresi foto menjadi WEBP
        isProcessing = true;
        image.disabled = true;

        showError(compressionStatus, "Sedang memproses foto...");

        try {
            const result = await compressToWebp(file);

            // Pastikan pengguna tidak mengganti foto saat kompresi berlangsung
            if (image.files[0] !== file) {
                return;
            }

            // 8. Validasi ukuran hasil kompresi
            if (result.size > MAX_COMPRESSED_SIZE) {
                compressedFile = null;
                originalFile = null;

                showError(
                    imageError,
                    "Hasil kompresi melebihi 2 MB. Silakan pilih foto lain."
                );

                image.value = "";
                image.focus();
                return;
            }

            compressedFile = result;
            originalFile = file;

            // 9. Ganti file input dengan hasil WEBP
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(compressedFile);
            image.files = dataTransfer.files;

            showError(compressionStatus, "Foto berhasil diproses.");

            // Aktifkan kembali input agar file ikut terkirim
            image.disabled = false;

            // Kirim form ke PHP dengan file WEBP
            form.submit();
        
        } catch (error) {
            compressedFile = null;
            originalFile = null;

            showError(
                imageError,
                error.message || "Foto gagal diproses. Silakan coba lagi."
            );
        } finally {
            isProcessing = false;
            image.disabled = false;
        }
    });
});