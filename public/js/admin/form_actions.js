// form "Buat Akun Baru"
function createAccount() {
    var username = document.getElementById("new_username").value;
    var password = document.getElementById("new_password").value;
    var email = document.getElementById("new_email").value;
    var role = document.getElementById("new_role").value;

    // validasi sisi client: semua field wajib diisi
    if (username === "" || password === "" || email === "" || role === "") {
        alert("Akun gagal dibuat: semua field wajib diisi");
        return;
    }

    alert("Akun berhasil dibuat");
    window.location.href = "kelola_akun.html";
}

// form "Masuk Data Fasilitas Baru"
function createFasilitas() {
    var data1 = document.getElementById("fasilitas_data1").value;
    var data2 = document.getElementById("fasilitas_data2").value;
    var data3 = document.getElementById("fasilitas_data3").value;

    if (data1 === "" || data2 === "" || data3 === "") {
        alert("Data gagal disimpan: semua field wajib diisi");
        return;
    }

    alert("Data fasilitas berhasil ditambahkan");
    window.location.href = "kelola_fasilitas.html";
}

// tombol "Edit" di halaman detail fasilitas
function editFasilitas() {
    var yakin = confirm("Yakin mau edit fasilitas ini?");
    if (yakin) {
        alert("Fasilitas berhasil diedit");
    }
}

// tombol "Download Rekap" di daftar fasilitas
function downloadRekap() {
    alert("Fitur ekspor rekap masih placeholder, belum tersambung ke data asli");
}
