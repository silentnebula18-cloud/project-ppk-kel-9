import users from "../../database_mockup/users.json" with { type: "json" };

let username_field = document.getElementById("username")
let password_field = document.getElementById("password")
let login_button = document.getElementById("login_button")
let invalid_credential_text = document.getElementById("invalid_credential_text")
let invalid_div = document.getElementById("invalid_div")

let show_password_box = document.getElementById("show_password")
show_password_box.addEventListener("change", show_password)

login_button.addEventListener("click", on_login_button_click)
username_field.addEventListener("click", reset_invalid_credential_dialog)
password_field.addEventListener("click", reset_invalid_credential_dialog)

function reset_invalid_credential_dialog(){
    invalid_div.style.visibility = "hidden"
    invalid_credential_text.innerHTML = ""
}

function show_password(){
    if (show_password_box.checked){
        password_field.type = "text"
    }else{
        password_field.type = "password"
    }
}

function on_login_button_click(){
    // if (username_field.value in users){
    let search_username = search(users, "username", username_field.value)
    let exist = search_username[0]    
    if (exist){
        let user_array = search_username[1]
        if (password_field.value === user_array['password']){
            reset_invalid_credential_dialog()
            let role = user_array['role']
            switch(role){
                case "admin":
                    window.location.replace("https://google.com")
                    break
                case "user":
                    window.location.replace("https://youtube.com")
                    break
                default:
                    window.location.replace("https://github.com")
                    break
            }
        }
        else{
            invalid_div.style.visibility = "visible"
            invalid_credential_text.innerHTML = "Invalid Credential"
        }
    }else{
        invalid_div.style.visibility = "visible"
        invalid_credential_text.innerHTML = "Invalid Credential"
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