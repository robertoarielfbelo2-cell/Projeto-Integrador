document.addEventListener('DOMContentLoaded', function() {
    carregarPartidos();
});

function carregarPartidos() {
    fetch('/trabalhoIntegrador/backend/php/listarPartidos.php')
        .then(response => response.json())
        .then(partidos => {
            const selectPartido = document.getElementById('selecionarPartido');
            selectPartido.innerHTML = '<option value="">Partido do Parlamentar</option>';

            partidos.forEach(partido => {
                const option = document.createElement('option');
                option.value = partido.id; // Envia o ID (FK) do partido
                option.textContent = `${partido.nome} (${partido.sigla})`;
                selectPartido.appendChild(option);
            });
        })
        .catch(error => console.error('Erro ao carregar partidos:', error));
}
const formVereadores = document.getElementById('formVereadores'); // Usando o id idêntico ao HTML
formVereadores.addEventListener('submit', function(event) {
    event.preventDefault();
    const nomeV = document.getElementById('nomeVereador').value;
    const inicioMandato = document.getElementById('dataInicioMandato').value;
    const fimMandato = document.getElementById('dataFimDoMandato').value;
    const descricao = document.getElementById('descricao').value;
    const partidoVereador = document.getElementById('selecionarPartido').value;

    const dadosVereador = {
        nomeV: nomeV,
        inicioMandato: inicioMandato,
        fimMandato: fimMandato,
        descricao: descricao,
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