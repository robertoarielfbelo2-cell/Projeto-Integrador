function carregarPartidos() {
    fetch('../../backend/php/listarPartidos.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta da rede: ' + response.statusText);
            }
            return response.json();
        })
        .then(partidos => {
            const selectPartido = document.getElementById('selecionarPartido');

            // Limpa opções antigas mantendo a padrão
            selectPartido.innerHTML = '<option value="">Partido do Parlamentar</option>';

            partidos.forEach(partido => {
                const option = document.createElement('option');
                option.value = partido.id; // Corresponde ao "AS id" da sua SQL
                option.textContent = `${partido.nome} (${partido.sigla})`;
                selectPartido.appendChild(option);
            });
        })
        .catch(error => console.error('Erro ao carregar partidos:', error));
}

// Executa assim que o HTML carregar
document.addEventListener('DOMContentLoaded', carregarPartidos);

// Envio do formulário
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

    fetch('../../backend/php/cadastrarVereador.php', {
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