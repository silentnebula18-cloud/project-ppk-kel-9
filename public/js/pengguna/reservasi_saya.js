document.addEventListener("DOMContentLoaded", function () {
    const filterStatus = document.getElementById("rsv_saya_fltr_status");

    if (!filterStatus) {
        return;
    }

    filterStatus.addEventListener("change", function () {
        const selectedStatus = filterStatus.value;

        window.location.href =
            "../../index.php?page=reservasi_saya&rsv_saya_fltr_status="
            + encodeURIComponent(selectedStatus);
    });
});