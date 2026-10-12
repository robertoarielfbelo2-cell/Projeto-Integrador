

const formIndicacao = document.getElementById('formIndicacao')
const mensagem = document.getElementById('mensagem')

formIndicacao.addEventListener('submit', (e) => {
    e.preventDefault()
    const formIndicacaoData = new FormData(formIndicacao)



    fetch("../../backend/php/cadastrarIndicacao.php", {
        method: "POST",
        body: formIndicacaoData,
    })
        .then(res => res.json())
        .then(dados => {
            if(dados.sucesso) {
                mensagem.textContent = dados.mensagem
                formIndicacao.reset()
            }
            else {
                mensagem.textContent = dados.mensagem
            }
        })
        .catch (erro => {
            console.error(erro)
            mensagem.textContent = "Erro ao cadastrar indicação!"
        })
})