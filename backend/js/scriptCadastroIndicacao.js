

const formIndicacao = document.getElementById('formIndicacao')

formIndicacao.addEventListener('submit', (e) => {
    e.preventDefault()
    const formIndicacaoData = new FormData(formIndicacao)

    for (item of formIndicacaoData) {
        console.log(item[0], item[1])
    }

    fetch("../../backend/php/cadastrarIndicacao.php", {
        method: "POST",
        body: formIndicacaoData,
    })
        .then(res => res.json())
        .then(res => console.log(res))
})