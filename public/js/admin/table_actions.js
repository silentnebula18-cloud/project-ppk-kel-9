// filter baris tabel berdasarkan teks yang diketik di search bar
function filterTable(input, tableId) {
    var filter = input.value.toLowerCase();
    var table = document.getElementById(tableId);
    var rows = table.getElementsByTagName("tr");

    // mulai dari 1 biar baris header (th) gak ikut disembunyikan
    for (var i = 1; i < rows.length; i++) {
        var rowText = rows[i].innerText.toLowerCase();
        if (rowText.includes(filter)) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
}

// tombol "Admit" di Kelola Akun: konfirmasi, lalu hapus baris dari daftar pending
function admitAccount(button) {
    var yakin = confirm("Yakin mau admit akun ini?");
    if (yakin) {
        var baris = button.closest("tr");
        baris.remove();
        alert("Akun berhasil dibuat");
    }
}

// tombol "Hapus" di detail fasilitas: konfirmasi, lalu balik ke daftar fasilitas
function hapusFasilitas() {
    var yakin = confirm("Yakin mau hapus fasilitas ini?");
    if (yakin) {
        alert("Fasilitas berhasil dihapus");
        window.location.href = "kelola_fasilitas.html";
    }
}