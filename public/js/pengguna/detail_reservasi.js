document.addEventListener("DOMContentLoaded", function () {

    const btnBatal = document.getElementById("btn_batal_reservasi");
    const popup = document.getElementById("dtl_rsv_popup_konfirmasi");
    const btnKonfirmasi = document.getElementById("btn_batal_konfirmasi");
    const btnTidak = document.getElementById("btn_batal_batal");

    if (!btnBatal) {
        return;
    }

    btnBatal.addEventListener("click", function () {

        const status = btnBatal.dataset.status;
        const rsvDate = btnBatal.dataset.rsvDate;
        const startTime = btnBatal.dataset.startTime;

        // CLIENT-SIDE VALIDATION
        const statusValid =
            status === "proses pengajuan" ||
            status === "disetujui";

        const waktuReservasi = new Date(
            rsvDate + "T" + startTime
        );

        const batasPembatalan = new Date(
            waktuReservasi.getTime() - (24 * 60 * 60 * 1000)
        );

        const sekarang = new Date();

        const waktuValid = sekarang < batasPembatalan;

        if (!statusValid) {
            alert("Reservasi tidak dapat dibatalkan.");
            return;
        }

        if (!waktuValid) {
            alert("Batas waktu pembatalan reservasi telah lewat.");
            return;
        }

        // Kalau valid → tampilkan popup
        popup.classList.remove("hidden");
    });


    // Tombol "Tidak"
    btnTidak.addEventListener("click", function () {
        popup.classList.add("hidden");
    });


    // Tombol "Ya, Batalkan"
    btnKonfirmasi.addEventListener("click", function () {

        const rsvId = btnBatal.dataset.rsvId;

        window.location.href =
            "../../index.php?page=batalkan_reservasi&rsv_id="
            + encodeURIComponent(rsvId);
    });

});