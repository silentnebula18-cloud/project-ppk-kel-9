function togglePopUp() {
    var popUp = document.getElementById("popUp_detail_fas_rsv");
    popUp.classList.toggle("hidden");
}

function showFacilityDetail(facilityId) {

    console.log("ID yang dikirim:", facilityId);

    fetch(`../../index.php?page=detail_fasilitas&fac_id=${facilityId}`)
        .then(response => {
            console.log("Response:", response);
            console.log("Status:", response.status);

            return response.json();
        })
        .then(data => {
            console.log("Data dari PHP:", data);

            document.getElementById("detail_fac_name").textContent = data.fac_name;
            document.getElementById("detail_fac_desc").textContent = data.fac_desc;
            document.getElementById("detail_fac_type").textContent = data.type;
            document.getElementById("detail_fac_location").textContent = data.location;
            document.getElementById("detail_fac_capacity").textContent = data.capacity;
            document.getElementById("detail_fac_status").textContent = data.fac_status;

            document.getElementById("popUp_detail_fas_rsv").classList.remove("hidden");
        });
}