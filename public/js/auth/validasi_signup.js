let username_field = document.getElementById("username")
let password_field = document.getElementById("password")
let email_field = document.getElementById("email")

let signup_button = document.getElementById("signup_button")

let eight_len_check = document.getElementById("eight")
let num_check = document.getElementById("num")
let symbol_check = document.getElementById("symbol")

let username_alert = document.getElementById("username_taken")
let email_alert = document.getElementById("email_taken")

let show_password_box = document.getElementById("show_password")

let home_button = document.getElementById("header_logo")
home_button.addEventListener("click", function(){
    window.location.replace("../index.html")
})

password_field.addEventListener("input", password_check)
username_field.addEventListener("input", username_check)
email_field.addEventListener("input", email_check)

signup_button.addEventListener("click", signup)

show_password_box.addEventListener("change",show_password)



function show_password(){
    if (show_password_box.checked){
        password_field.type = "text"
    }else{
        password_field.type = "password"

    }
}

function username_check(){
    let email = email_field.value

    if (email.trim() === ""){
        email_alert.style.display = "none"
        return false
    }

    return true
}

function password_check(){
    let password = password_field.value
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

    return len_valid && sym_valid && num_valid
}

//buat test email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
function email_check(){
    let email = email_field.value
    let valid_email = true

    if (email.trim() === ""){
        email_alert.style.display = "none"
        return false
    }

    return valid_email
}

async function signup(){
    if (email_check() && password_check() && username_check()){
        signup_button.disabled = true
        let password = password_field.value
        let email = email_field.value
        let username = username_field.value

        const header = {"Content-Type": "application/json"}

        let info = {
            "email":email,
            "username":username,
            "password":password,
        }
        try{
            let stringified_info = JSON.stringify(info)

            let response = await fetch("../../public/php/validate_signup.php", {method: "POST", body:stringified_info, headers:header})
            
            if (response.ok){
                let result = await response.json()

                if (result.status == 'success'){
                    reset_field()
                    window.location.replace("../auth/login.html")
                }else{
                    throw `${result.status} ${result.message}`
                }

            }else{
                throw 'lol'
            }

        }catch(err) {
            console.error('signup error:', err);
        }finally{
            signup_button.disabled = false
        }
    }
}

function reset_field(){
    username_field.value = ""
    email_field.value = ""
    password_field.value = ""
    password_check()
    email_check()
    username_check()
}