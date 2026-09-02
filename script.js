import {encode, decode} from "./jsified_tokenizer.mjs"

const input = document.getElementById("input")
const output = document.getElementById("output")
const clearButton = document.getElementById("clear")
const statToken = document.getElementById("token_count")
const statChar = document.getElementById("character_count")

input.addEventListener("input", get_output)

function clear(){
    input.value = ""
    output.value = ""
    statToken.textContent = String(0)
    statChar.textContent = String(0)
}

clearButton.addEventListener('click', function() {
    clear()

})

function get_output(){
    if (input.value.length === 0 || input.value === "") {
        clear()
        return
    }
    let output_value = encode(input.value)
    output.value = output_value
    statToken.textContent = String(output_value.length)
    statChar.textContent = String(input.value.length)
    
}

