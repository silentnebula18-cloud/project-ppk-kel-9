function toggleSidebar() {
    var sidebar = document.getElementById("sidebar");
    sidebar.classList.toggle("hidden");
}

let logout_btn = document.getElementById("logout_btn");
if (logout_btn) {
    logout_btn.addEventListener("click", function() {
        window.location.replace("../../app/controllers/logout_controller.php");
    });
}