document.addEventListener("DOMContentLoaded", function () {
    const btnBatal = document.getElementById("btn_batal_reservasi");
    const popup = document.getElementById("dtl_rsv_popup_konfirmasi");
    const btnKonfirmasi = document.getElementById("btn_batal_konfirmasi");
    const btnTidak = document.getElementById("btn_batal_batal");
    const inputAlasan = document.getElementById("alasan_batal");
    const errorAlasan = document.getElementById("error_alasan_batal");

    if (!btnBatal) {
        return;
    }

    // Membuka popup setelah validasi awal
    btnBatal.addEventListener("click", function () {
        const status = btnBatal.dataset.status;
        const rsvDate = btnBatal.dataset.rsvDate;
        const startTime = btnBatal.dataset.startTime;

        const statusValid =
            status === "proses pengajuan" ||
            status === "disetujui";

        const waktuReservasi = new Date(rsvDate + "T" + startTime);
        const batasPembatalan = new Date(
            waktuReservasi.getTime() - (24 * 60 * 60 * 1000)
        );

        // 1. Validasi Status
        if (!statusValid) {
            alert("Reservasi tidak dapat dibatalkan.");
            return;
        }

        // 2. Validasi waktu pembatalan
        if (new Date() >= batasPembatalan) {
            alert("Batas waktu pembatalan reservasi telah lewat.");
            return;
        }

        inputAlasan.value = "";
        errorAlasan.classList.add("hidden");
        popup.classList.remove("hidden");
    });

    // Tombol "Tidak"
    btnTidak.addEventListener("click", function () {
        popup.classList.add("hidden");
        inputAlasan.value = "";
        errorAlasan.classList.add("hidden");
    });

    // Tombol "Ya, Batalkan"
    btnKonfirmasi.addEventListener("click", function () {
        const alasan = inputAlasan.value.trim();

        if (alasan === "") {
            errorAlasan.textContent = "Alasan pembatalan wajib diisi.";
            errorAlasan.classList.remove("hidden");
            inputAlasan.focus();
            return;
        }

        if (alasan.length > 300) {
            errorAlasan.textContent = "Alasan maksimal 300 karakter.";
            errorAlasan.classList.remove("hidden");
            inputAlasan.focus();
            return;
        }

        const rsvId = btnBatal.dataset.rsvId;

        const form = document.createElement("form");
        form.method = "POST";
        form.action = "../../index.php";

        const inputAction = document.createElement("input");
        inputAction.type = "hidden";
        inputAction.name = "action";
        inputAction.value = "batalkan_reservasi";

        const inputRsvId = document.createElement("input");
        inputRsvId.type = "hidden";
        inputRsvId.name = "rsv_id";
        inputRsvId.value = rsvId;

        const inputReason = document.createElement("input");
        inputReason.type = "hidden";
        inputReason.name = "cancel_reason";
        inputReason.value = alasan;

        form.appendChild(inputAction);
        form.appendChild(inputRsvId);
        form.appendChild(inputReason);

        document.body.appendChild(form);
        form.submit();
    });
});