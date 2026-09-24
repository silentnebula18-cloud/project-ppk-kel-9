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