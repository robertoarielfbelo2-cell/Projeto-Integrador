
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

// Esta página serve para cadastrar E para editar um vereador.
// Aberta como addVereador.html?id=5, ela entra no modo de edição do vereador 5.
const idVereadorEdicao = new URLSearchParams(window.location.search).get('id');
const modoEdicao = idVereadorEdicao !== null;

// No modo de edição: troca os textos da página e preenche o formulário
async function prepararEdicao() {
    const mensagem = document.getElementById('mensagem');
    const form = document.getElementById('formVereadores');
    const botao = form.querySelector('button[type="submit"]');

    document.title = 'Editar Vereador | Vox Populis';
    document.getElementById('tituloCadastroVereador').textContent = 'Editar Vereador';
    document.querySelector('section > p').textContent =
        'Altere os dados do vereador e salve. O e-mail e a senha de acesso são definidos na tela Acesso dos Vereadores.';
    botao.textContent = 'Salvar alterações';

    botao.disabled = true;
    mensagem.textContent = 'Carregando vereador...';

    try {
        const response = await fetch(
            '../../backend/php/buscarVereador.php?id=' +
            encodeURIComponent(idVereadorEdicao)
        );

        const data = await response.json();

        // 403: o administrador não está logado
        if (response.status === 403) {
            window.location.href = 'loginAdmin.html';
            return;
        }

        if (!response.ok || data.sucesso !== true) {
            throw new Error(data.mensagem || 'Não foi possível carregar o vereador.');
        }

        const vereador = data.vereador;

        // Campos vazios no banco (NULL) viram campos vazios no formulário
        document.getElementById('nomeVereador').value = vereador.nome_parlamentar;
        document.getElementById('dataInicioMandato').value = vereador.data_inicio_mandato || '';
        document.getElementById('dataFimDoMandato').value = vereador.data_termino_mandato || '';
        document.getElementById('descricao').value = vereador.descricao || '';
        document.getElementById('fotoVereador').value = vereador.foto || '';
        document.getElementById('selecionarPartido').value = vereador.id_partido;

        mensagem.textContent = '';
        botao.disabled = false;

    } catch (error) {
        console.error('Erro ao carregar vereador:', error);
        mensagem.textContent = error.message;
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

    // Na edição, o PHP precisa saber qual vereador alterar
    if (modoEdicao) {
        dadosVereador.id = Number(idVereadorEdicao);
    }

    const url = modoEdicao
        ? '../../backend/php/editarVereador.php'
        : '../../backend/php/cadastrarVereador.php';

    botao.disabled = true;
    mensagem.textContent = modoEdicao ? 'Salvando alterações...' : 'Cadastrando vereador...';

    try {
        const response = await fetch(
            url,
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

        // No cadastro, limpa para o próximo; na edição, mantém o que foi salvo
        if (data.sucesso === true && !modoEdicao) {
            form.reset();
        }

    } catch (error) {
        console.error('Erro ao salvar vereador:', error);
        mensagem.textContent = error.message;
    } finally {
        botao.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', async function () {
    const form = document.getElementById('formVereadores');
    form.addEventListener('submit', cadastrarVereador);

    // A lista de partidos precisa estar pronta antes de marcar o partido do vereador
    await carregarPartidos();

    if (modoEdicao) {
        prepararEdicao();
    }
});
