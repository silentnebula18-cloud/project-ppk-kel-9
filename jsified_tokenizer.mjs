// you can check my github for more if u want
// https://github.com/danksup/nn-from-scratch/blob/main/engine/tokenizer.py

import tokenizer from "./tokenizer48000_1351277738len.json" with { type: "json" };
let vocab = tokenizer["vocab"]
let merge_rank = tokenizer["merge_rank"]
let id_to_word = tokenizer["id_to_token"]

function word_to_id(word, vocab){
    let tokenized = []
    for (let char of word) {
        tokenized.push(vocab[char] ?? "<UNK>")
    }
    tokenized.push(vocab["</w>"])
    return tokenized
}

function merge(word, best_pair, new_id) {
    let i = 0
    let n = word.length
    let new_word = []

    while (i < n-1) {
        let pair =[word[i], word[i + 1]]
        if (pair[0] == best_pair[0] && pair[1] == best_pair[1]) {
            new_word.push(new_id)
            i += 2
        } else {
            new_word.push(word[i])
            i += 1
        }
    }
    if (i == n - 1) {
        new_word.push(word[n-1])
    }
    return new_word
}

function encode(text){
    let split_text = text.split(" ")
    let tokenized_words = split_text.map(word => word_to_id(word, vocab))

    tokenized_words.forEach((word, idx) => {
        while (true){
            let best_pair = null
            let best_rank = Infinity
            let best_new_id = null

            for (let i=0; i < word.length-1; i++){
                let pair =[word[i], word[i + 1]]
                let stringified_pair = JSON.stringify(pair) 
                if (stringified_pair in merge_rank){
                    let [rank, new_id] = merge_rank[stringified_pair]

                    if (rank < best_rank) {
                        best_rank = rank
                        best_pair = pair
                        best_new_id = new_id
                    }
                }
            }

            if (best_pair == null) {
                break
            }
            word = merge(word, best_pair, best_new_id)
            tokenized_words[idx] = word
        }
    });

    let tokens = tokenized_words.flat()

    return tokens
}

function decode(thing){
    let decoded = ""

    for (let token_id of thing) {
        if (!( token_id == vocab["<PAD>"])){
            decoded += id_to_word[token_id]
        }
    }
   
    return  decoded.replaceAll("</w>", " ")
}

let a = encode("test tokenizer 123")
console.log(a)
console.log(decode(a))

export {encode, decode}