document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("form_rsv_2");
    const startTime = document.getElementById("start_time");
    const endTime = document.getElementById("end_time");
    const rsvDate = document.getElementById("rsv_date");
    const facStatus = document.getElementById("fac_status");

    // Validasi fasilitas
    if (facStatus && facStatus.value !== "aktif") {
        alert("Fasilitas sedang tidak dapat digunakan untuk reservasi.");
        form.querySelector("button[type='submit']").disabled = true;
    }

    // Batas Tanggal, minimal 48 jam
    const minimumDateTime = new Date(
        Date.now() + 48 * 60 * 60 * 1000
    );

    rsvDate.min = minimumDateTime.toISOString().split("T")[0];

    // Simpan semua pilihan jam mulai
    const allStartOptions = Array.from(startTime.options);

    // Simpan semua pilihan jam selesai
    const allEndTimes = Array.from(endTime.options)
        .map(option => option.value)
        .filter(value => value !== "");

    // Awalnya jam selesai belum bisa dipilih
    endTime.innerHTML = "";

    const defaultEndOption = document.createElement("option");
    defaultEndOption.value = "";
    defaultEndOption.textContent = "-- Pilih Jam Selesai --";
    endTime.appendChild(defaultEndOption);

    endTime.disabled = true;

    // Filter jam mulai berdasarkan aturan 48 jam
    function updateStartTimes() {

        const selectedDate = rsvDate.value;

        if (!selectedDate) {
            return;
        }

        allStartOptions.forEach(function (option) {

            if (option.value === "") {
                return;
            }

            const selectedDateTime = new Date(
                `${selectedDate}T${option.value}`
            );

            option.disabled = selectedDateTime < minimumDateTime;
        });
    }

    // Filter jam selesai berdasarkan jam mulai
    startTime.addEventListener("change", function () {

        const selectedStart = startTime.value;

        endTime.innerHTML = "";

        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = "-- Pilih Jam Selesai --";
        endTime.appendChild(placeholder);

        if (!selectedStart) {
            endTime.disabled = true;
            return;
        }

        for (const time of allEndTimes) {

            if (time > selectedStart) {

                const option = document.createElement("option");
                option.value = time;
                option.textContent = time;

                endTime.appendChild(option);
            }
        }

        endTime.disabled = false;
    });


    // Ketika tanggal berubah, update jam mulai
    rsvDate.addEventListener("change", function () {

        updateStartTimes();

        // Reset jam mulai
        startTime.value = "";

        // Reset jam selesai
        endTime.innerHTML = "";

        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = "-- Pilih Jam Selesai --";
        endTime.appendChild(placeholder);

        endTime.disabled = true;
    });

    // Validasi saat submit
    form.addEventListener("submit", function (event) {

        // 1. Fasilitas harus aktif
        if (facStatus && facStatus.value !== "aktif") {
            event.preventDefault();
            alert("Fasilitas sedang tidak dapat digunakan untuk reservasi.");
            return;
        }

        // 2. Tanggal dan jam harus ada
        if (!rsvDate.value || !startTime.value || !endTime.value) {
            return;
        }

        // 3. Minimal 48 jam sebelum waktu mulai
        const selectedStartDateTime = new Date(
            `${rsvDate.value}T${startTime.value}`
        );

        if (selectedStartDateTime < minimumDateTime) {
            event.preventDefault();
            alert("Reservasi harus diajukan minimal 48 jam sebelum waktu mulai.");
            return;
        }

        // 4. Cek bentrok dengan reservasi yang sudah disetujui
        const selectedStart = startTime.value;
        const selectedEnd = endTime.value;

        const isOverlapping = approvedReservations.some(function (reservation) {

            // Hanya cek reservation pada tanggal yang sama
            if (reservation.rsv_date !== rsvDate.value) {
                return false;
            }

            // Cek overlap jam
            return (
                reservation.start_time < selectedEnd &&
                reservation.end_time > selectedStart
            );
        });

        if (isOverlapping) {
            event.preventDefault();
            alert(
                "Waktu yang dipilih bertabrakan dengan reservasi lain yang sudah disetujui."
            );
            return;
        }
    });

});