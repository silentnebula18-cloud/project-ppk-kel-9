function initFormRep2() {
    const form = document.getElementById("form_rep_2");
    if (!form) {
        return;
    }

    const category = document.getElementById("category");
    const description = document.getElementById("rep_desc");
    const image = document.getElementById("rep_img");

    const categoryError = document.getElementById("category_error");
    const descriptionError = document.getElementById("rep_desc_error");
    const imageError = document.getElementById("rep_img_error");
    const compressionStatus = document.getElementById("compression_status");
    const submitBtn = form.querySelector('button[type="submit"]');

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
    let compressPromise = null;

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

    if (category) {
        category.addEventListener("change", function () {
            clearError(categoryError);
        });
    }

    if (description) {
        description.addEventListener("input", function () {
            clearError(descriptionError);
        });
    }

    function compressToWebp(file) {
        return new Promise(function (resolve, reject) {
            if (!file) {
                reject(new Error("Foto laporan tidak ditemukan."));
                return;
            }

            const img = new Image();
            const objectUrl = URL.createObjectURL(file);

            img.onload = function () {
                URL.revokeObjectURL(objectUrl);

                let width = img.naturalWidth || img.width;
                let height = img.naturalHeight || img.height;

                if (!width || !height) {
                    reject(new Error("Dimensi foto tidak valid."));
                    return;
                }

                const maxDimension = 1920;
                if (width > maxDimension || height > maxDimension) {
                    if (width > height) {
                        height = Math.round((height * maxDimension) / width);
                        width = maxDimension;
                    } else {
                        width = Math.round((width * maxDimension) / height);
                        height = maxDimension;
                    }
                }

                const canvas = document.createElement("canvas");
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext("2d");
                if (!ctx) {
                    reject(new Error("Browser tidak mendukung pemrosesan foto."));
                    return;
                }

                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    function (blob) {
                        if (!blob) {
                            reject(new Error("Foto gagal dikompres."));
                            return;
                        }

                        if (blob.type !== "image/webp") {
                            reject(new Error("Browser tidak mendukung konversi ke WEBP."));
                            return;
                        }

                        const baseName = (file.name || "report").replace(/\.[^/.]+$/, "");
                        const webpFile = new File(
                            [blob],
                            baseName + ".webp",
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

    async function processSelectedImage() {
        compressedFile = null;
        originalFile = null;
        compressPromise = null;

        clearError(imageError);
        showError(compressionStatus, "");

        const file = image.files && image.files[0];
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
            return;
        }

        originalFile = file;
        showError(compressionStatus, "Sedang memproses foto ke format WEBP...");

        compressPromise = compressToWebp(file)
            .then(function (result) {
                if (originalFile !== file) {
                    return null;
                }

                if (result.size > MAX_COMPRESSED_SIZE) {
                    compressedFile = null;
                    showError(
                        imageError,
                        "Hasil kompresi melebihi 2 MB. Silakan pilih foto lain."
                    );
                    showError(compressionStatus, "");
                    image.value = "";
                    return null;
                }

                compressedFile = result;

                try {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(result);
                    image.files = dataTransfer.files;
                } catch (e) {
                }

                showError(compressionStatus, "Foto berhasil diproses ke format WEBP.");
                return result;
            })
            .catch(function (error) {
                if (originalFile === file) {
                    compressedFile = null;
                    showError(
                        imageError,
                        error.message || "Foto gagal diproses. Silakan coba lagi."
                    );
                    showError(compressionStatus, "");
                    image.value = "";
                }
                return null;
            });
    }

    if (image) {
        image.addEventListener("change", processSelectedImage);
    }

    form.addEventListener("submit", async function (event) {
        event.preventDefault();

        if (isProcessing) {
            return;
        }

        clearAllErrors();

        if (!category.value) {
            showError(categoryError, "Silakan pilih kategori laporan.");
            category.focus();
            return;
        }

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

        const file = image.files && image.files[0];

        if (!file && !compressedFile) {
            showError(imageError, "Foto laporan wajib diunggah.");
            image.focus();
            return;
        }

        isProcessing = true;
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        try {
            let targetFile = compressedFile;

            if (!targetFile && compressPromise) {
                showError(compressionStatus, "Sedang memproses foto ke format WEBP...");
                targetFile = await compressPromise;
            }

            if (!targetFile && file) {
                if (!allowedTypes.includes(file.type)) {
                    showError(
                        imageError,
                        "Foto harus berformat JPG, PNG, atau WEBP."
                    );
                    image.focus();
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                    isProcessing = false;
                    return;
                }

                if (file.size > MAX_ORIGINAL_SIZE) {
                    showError(
                        imageError,
                        "Ukuran foto sebelum kompresi maksimal 5 MB."
                    );
                    image.focus();
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                    isProcessing = false;
                    return;
                }

                showError(compressionStatus, "Sedang memproses foto ke format WEBP...");
                targetFile = await compressToWebp(file);
            }

            if (!targetFile) {
                showError(imageError, "Foto laporan wajib diunggah.");
                image.focus();
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
                isProcessing = false;
                return;
            }

            if (targetFile.size > MAX_COMPRESSED_SIZE) {
                showError(
                    imageError,
                    "Hasil kompresi melebihi 2 MB. Silakan pilih foto lain."
                );
                image.focus();
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
                isProcessing = false;
                return;
            }

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(targetFile);
            image.files = dataTransfer.files;

            HTMLFormElement.prototype.submit.call(form);
        } catch (error) {
            showError(
                imageError,
                error.message || "Foto gagal diproses. Silakan coba lagi."
            );
            if (submitBtn) {
                submitBtn.disabled = false;
            }
            isProcessing = false;
        }
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initFormRep2);
} else {
    initFormRep2();
}