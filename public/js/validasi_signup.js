import users from "../../database_mockup/users.json" with { type: "json" };

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
    let username = username_field.value
    let search_username = search(users, "username", username)
    let exist = search_username[0]
    if (exist){
        username_alert.style.display = "block"
    }else{
        username_alert.style.display = "none"
    }

    return !exist
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

    email_alert.style.display = "none"
    for (let user of users){
        if (user["email"] === email){
            valid_email = false
            email_alert.style.display = "block"
            break
        }
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

            let response = await fetch("../public/php/validate_signup.php", {method: "POST", body:stringified_info, headers:header})
            
            if (response.ok){
                let result = await response.json()

                if (result.status == 'success'){
                    console.log('yay')
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

function search(obj, key, value){
    for (let i = 0; i < obj.length; i++){
        if (obj[i][key] == value){
            return [true, obj[i]]
        }
    }
    return [false, -1]
}