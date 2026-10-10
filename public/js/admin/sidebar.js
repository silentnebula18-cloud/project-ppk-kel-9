function toggleSidebar() {
    var sidebar = document.getElementById("sidebar");
    sidebar.classList.toggle("hidden");
}

function logout(){
    window.location.replace("../../app/controllers/logout_controller.php")
}