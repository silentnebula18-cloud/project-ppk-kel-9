let email_field = document.getElementById("email")
let old_password_field = document.getElementById("old_password")
let new_password_field = document.getElementById("new_password")
let confirm_password_field = document.getElementById("confirm_password")
let upd_button = document.getElementById("change_password_button")

let eight_len_check = document.getElementById("eight")
let num_check = document.getElementById("num")
let symbol_check = document.getElementById("symbol")

let email_alert = document.getElementById("email_alert")
let invalid_old_password = document.getElementById("invalid_old_password")
let confirm_password_error = document.getElementById("confirm_password_error")

let show_password_box = document.getElementById("show_password")

let home_button = document.getElementById("header_logo")
home_button.addEventListener("click", function(){
    window.location.replace("../../index.php")
})

show_password_box.addEventListener("change", show_password)

new_password_field.addEventListener("input", password_check)
confirm_password_field.addEventListener("input", confirm_check)
email_field.addEventListener("input", email_check)
old_password_field.addEventListener("input", function(){
    invalid_old_password.style.display = "none"
})

upd_button.addEventListener("click", on_upd_button_click)

function show_password(){
    let type = show_password_box.checked ? "text" : "password"
    old_password_field.type = type
    new_password_field.type = type
    confirm_password_field.type = type
}

function email_check(){
    let email = email_field.value

    if (email.trim() === ""){
        email_alert.style.display = "none"
        return false
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
        email_alert.style.display = "block"
        email_alert.innerHTML = "Email tidak sesuai. Contoh: user@example.com"
        return false
    }else{
        email_alert.style.display = "none"
        return true
    }
}

function confirm_check(){
    let new_pass = new_password_field.value
    let confirm_pass = confirm_password_field.value

    if (confirm_pass === ""){
        confirm_password_error.style.display = "none"
        return false
    }

    if (new_pass !== confirm_pass){
        confirm_password_error.style.display = "block"
        confirm_password_error.innerHTML = "Password tidak cocok"
        return false
    }else{
        confirm_password_error.style.display = "none"
        return true
    }
}

function password_check(){
    let password = new_password_field.value
    let len_valid = false
    let num_valid = false
    let sym_valid = false

    if (password.length >= 8){
        eight_len_check.style.color = "green"
        len_valid = true
    }else{
        eight_len_check.style.color = "red"
        len_valid = false
    }

    if (/[0-9]/.test(password)) {
        num_check.style.color = "green"
        num_valid = true
    }else{
        num_check.style.color = "red"
        num_valid = false
    }

    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
        symbol_check.style.color = "green"
        sym_valid = true
    }else{
        symbol_check.style.color = "red"
        sym_valid = false
    }

    if (confirm_password_field.value !== ""){
        confirm_check()
    }

    return len_valid && sym_valid && num_valid
}

async function on_upd_button_click(){
    let email = email_field.value
    let old_password = old_password_field.value
    let new_password = new_password_field.value
    let confirm_password = confirm_password_field.value

    if (email.trim() === ""){
        email_alert.style.display = "block"
        email_alert.innerHTML = "Email wajib diisi"
        return
    }

    if (old_password.trim() === ""){
        invalid_old_password.style.display = "block"
        invalid_old_password.innerHTML = "Password lama wajib diisi"
        return
    }

    let is_email_valid = email_check()
    let is_pass_valid = password_check()
    let is_confirm_valid = confirm_check()

    if (confirm_password === ""){
        confirm_password_error.style.display = "block"
        confirm_password_error.innerHTML = "Konfirmasi password wajib diisi"
        return
    }

    if (!is_email_valid || !is_pass_valid || !is_confirm_valid){
        return
    }

    const header = {"Content-Type": "application/json"}

    let info = {
        "email": email,
        "old_password": old_password,
        "new_password": new_password
    }

    try{
        upd_button.disabled = true
        let stringified_info = JSON.stringify(info)

        let response = await fetch("../../public/php/validate_lupa_password.php", {method: "POST", body: stringified_info, headers: header})
        
        if (response.ok){
            let result = await response.json()
            if (result.status === 'success'){
                window.location.replace("../auth/login.html")
            }else{
                if (result.message === "email_not_found"){
                    email_alert.style.display = "block"
                    email_alert.innerHTML = "Email tidak terdaftar"
                }else if (result.message === "invalid_old_password"){
                    invalid_old_password.style.display = "block"
                    invalid_old_password.innerHTML = "Password Tidak Sesuai."
                }
            }
        }
    }catch(err){
        console.error(err)
    }finally{
        upd_button.disabled = false
    }
}