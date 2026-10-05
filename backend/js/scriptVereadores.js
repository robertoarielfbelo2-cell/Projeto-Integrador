const formVereadores = document.getElementById('formVereadores');
formVereadores.addEventListener('submit', function(event) {
    event.preventDefault();
    const nomeV = document.getElementById('nomeVereador').value;
    const inicioMandato = document.getElementById('dataInicioMandato').value;
    const fimMandato = document.getElementById('dataFimDoMandato').value;
    const descricao = document.getElementById('descricao').value;
    const fotoVereador = document.getElementById('fotoVereador').value;
    const partidoVereador = document.getElementById('selecionarPartido').value;

    const dadosVereador = {
        nomeV: nomeV,
        inicioMandato: inicioMandato,
        fimMandato: fimMandato,
        descricao: descricao,
        foto: fotoVereador,
        partidoVereador: partidoVereador
    };

    fetch('/trabalhoIntegrador/backend/php/cadastrarVereador.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dadosVereador)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.mensagem);
            if (data.mensagem && data.mensagem.includes("sucesso")) {
                formVereadores.reset();
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro ao se comunicar com o servidor');
        });
});