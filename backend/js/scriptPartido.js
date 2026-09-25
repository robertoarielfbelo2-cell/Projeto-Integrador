const formPartido = document.getElementById('formPartido');
formPartido.addEventListener('submit', function(event) {
    event.preventDefault();

    const nome = document.getElementById('nomePartido').value;
    const sigla = document.getElementById('siglaPartido').value;
    const numero = document.getElementById('numeroPartido').value;
    const dadosPartido = {
        nome: nome,
        sigla: sigla,
        numero: numero
    };
    fetch('/trabalhoIntegrador/backend/php/cadastrarPartido.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dadosPartido)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.mensagem);
            if (data.mensagem.includes("sucesso")) {
                formPartido.reset();
            }
        })
        .catch(error => {
            console.error('erro na requisição:', error);
            alert('Erro ao se comunicar com o servidor');
        });
});