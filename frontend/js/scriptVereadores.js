
async function carregarPartidos() {
    const selectPartido = document.getElementById('selecionarPartido');
    const mensagem = document.getElementById('mensagem');

    selectPartido.disabled = true;

    try {
        const response = await fetch(
            '../../backend/php/listarPartidos.php'
        );

        if (!response.ok) {
            throw new Error('Erro HTTP: ' + response.status);
        }

        const partidos = await response.json();

        if (!Array.isArray(partidos)) {
            throw new Error('Resposta inválida do servidor.');
        }

        selectPartido.replaceChildren();

        const opcaoInicial = document.createElement('option');
        opcaoInicial.value = '';
        opcaoInicial.textContent = 'Selecione um partido';
        selectPartido.appendChild(opcaoInicial);

        partidos.forEach(partido => {
            const option = document.createElement('option');

            option.value = partido.id;
            option.textContent = `${partido.nome} (${partido.sigla})`;

            selectPartido.appendChild(option);
        });

        if (partidos.length === 0) {
            mensagem.textContent = 'Nenhum partido cadastrado.';
        } else {
            mensagem.textContent = '';
        }

        selectPartido.disabled = false;

    } catch (error) {
        console.error('Erro ao carregar partidos:', error);
        mensagem.textContent = 'Não foi possível carregar os partidos.';
    }
}

async function cadastrarVereador(event) {
    event.preventDefault();

    const form = document.getElementById('formVereadores');
    const mensagem = document.getElementById('mensagem');
    const botao = form.querySelector('button[type="submit"]');

    const nomeV = document.getElementById('nomeVereador').value.trim();
    const inicioMandato = document.getElementById('dataInicioMandato').value;
    const fimMandato = document.getElementById('dataFimDoMandato').value;
    const descricao = document.getElementById('descricao').value.trim();
    const fotoVereador = document.getElementById('fotoVereador').value.trim();
    const partidoVereador = document.getElementById('selecionarPartido').value;

    mensagem.textContent = '';

    if (fimMandato < inicioMandato) {
        mensagem.textContent =
            'A data de término não pode ser anterior ao início do mandato.';
        return;
    }

    const dadosVereador = {
        nomeV: nomeV,
        inicioMandato: inicioMandato,
        fimMandato: fimMandato,
        descricao: descricao,
        foto: fotoVereador,
        partidoVereador: partidoVereador
    };

    botao.disabled = true;
    mensagem.textContent = 'Cadastrando vereador...';

    try {
        const response = await fetch(
            '../../backend/php/cadastrarVereador.php',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dadosVereador)
            }
        );

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.mensagem || data.erro || 'Erro ao cadastrar vereador.'
            );
        }

        mensagem.textContent =
            data.mensagem || 'Cadastro realizado.';

        if (data.sucesso === true) {
            form.reset();
        }

    } catch (error) {
        console.error('Erro ao cadastrar vereador:', error);
        mensagem.textContent = error.message;
    } finally {
        botao.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    carregarPartidos();

    const form = document.getElementById('formVereadores');
    form.addEventListener('submit', cadastrarVereador);
});
