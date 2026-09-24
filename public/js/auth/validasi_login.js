// import users from "../../../database_mockup/users.json" with { type: "json" };

let username_field = document.getElementById("username")
let password_field = document.getElementById("password")
let login_button = document.getElementById("login_button")
let invalid_credential_text = document.getElementById("invalid_credential_text")
let invalid_div = document.getElementById("invalid_div")

let show_password_box = document.getElementById("show_password")

let home_button = document.getElementById("header_logo")
home_button.addEventListener("click", function(){
    window.location.replace("../index.html")
})

show_password_box.addEventListener("change", show_password)

login_button.addEventListener("click", on_login_button_click)
username_field.addEventListener("click", reset_invalid_credential_dialog)
password_field.addEventListener("click", reset_invalid_credential_dialog)

function reset_invalid_credential_dialog(){
    invalid_div.style.visibility = "hidden"
    invalid_credential_text.innerHTML = ""
}

function show_invalid_dialog(msg){
    invalid_div.style.visibility = "visible"
    invalid_credential_text.innerHTML = msg
}

function show_password(){
    if (show_password_box.checked){
        password_field.type = "text"
    }else{
        password_field.type = "password"
    }
}

async function on_login_button_click(){
    const header = {"Content-Type": "application/json"}
    
    let password = password_field.value
    let username = username_field.value


    let info = {
        "username":username,
        "password":password,
    }

   try{
        login_button.disabled = true
        let stringified_info = JSON.stringify(info)

        let response = await fetch("../../public/php/validate_login.php", {method: "POST", body:stringified_info, headers:header})
        
        if (response.ok){
            let result = await response.json()
            console.log(result)
            if (result.status == 'success'){
                let role = result.user.role
                switch (role){
                    case "admin":
                        console.log("a")
                        break
                    case "user":
                        console.log("b")
                        break
                    case "petugas":
                        console.log("c")
                    default:
                        window.location.replace("../index.html")
                }
            }else{
                let msg = `${result.status}: ${result.message}`
                show_invalid_dialog(msg)
            }

        }else{
            show_invalid_dialog("err")
            throw 'err'
        }

    }catch(err) {
        show_invalid_dialog(`error: ${err}`)
        throw err
    }finally{
        login_button.disabled = false
    }
}