// Esta página serve para cadastrar E para editar um partido.
// Aberta como addPartido.html?id=5, ela entra no modo de edição do partido 5.
const idPartidoEdicao = new URLSearchParams(window.location.search).get('id');
const modoEdicao = idPartidoEdicao !== null;

// No modo de edição: troca os textos da página e preenche o formulário
async function prepararEdicao() {
    const mensagem = document.getElementById('mensagem');
    const form = document.getElementById('formPartido');
    const botao = form.querySelector('button[type="submit"]');

    document.title = 'Editar Partido | Vox Populis';
    document.getElementById('tituloCadastroPartido').textContent = 'Editar Partido Político';
    document.querySelector('section > p').textContent =
        'Altere as informações do partido e salve.';
    botao.textContent = 'Salvar alterações';

    botao.disabled = true;
    mensagem.textContent = 'Carregando partido...';

    try {
        const response = await fetch('../../backend/php/listarPartidos.php');
        const partidos = await response.json();

        if (!response.ok || !Array.isArray(partidos)) {
            throw new Error('Não foi possível carregar o partido.');
        }

        const partido = partidos.find(function (item) {
            return String(item.id) === idPartidoEdicao;
        });

        if (!partido) {
            mensagem.textContent = 'Partido não encontrado.';
            return;
        }

        document.getElementById('nomePartido').value = partido.nome;
        document.getElementById('siglaPartido').value = partido.sigla;
        document.getElementById('numeroPartido').value = partido.numero;

        mensagem.textContent = '';
        botao.disabled = false;

    } catch (error) {
        console.error('Erro ao carregar partido:', error);
        mensagem.textContent = error.message;
    }
}

async function salvarPartido(event) {
    event.preventDefault();

    const form = document.getElementById('formPartido');
    const mensagem = document.getElementById('mensagem');
    const botao = form.querySelector('button[type="submit"]');

    const nome = document.getElementById('nomePartido').value.trim();
    const sigla = document.getElementById('siglaPartido').value.trim().toUpperCase();
    const numero = document.getElementById('numeroPartido').value;

    mensagem.textContent = '';

    if (!nome || !sigla || !numero) {
        mensagem.textContent = 'Preencha todos os campos obrigatórios.';
        return;
    }

    const numeroConvertido = Number(numero);

    if (
        !Number.isInteger(numeroConvertido) ||
        numeroConvertido < 10 ||
        numeroConvertido > 99
    ) {
        mensagem.textContent =
            'O número do partido deve ser um número inteiro entre 10 e 99.';
        return;
    }

    const dadosPartido = {
        nome: nome,
        sigla: sigla,
        numero: numeroConvertido
    };

    // Na edição, o PHP precisa saber qual partido alterar
    if (modoEdicao) {
        dadosPartido.id = Number(idPartidoEdicao);
    }

    const url = modoEdicao
        ? '../../backend/php/editarPartido.php'
        : '../../backend/php/cadastrarPartido.php';

    botao.disabled = true;
    mensagem.textContent = modoEdicao ? 'Salvando alterações...' : 'Cadastrando partido...';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dadosPartido)
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.mensagem || data.erro || 'Erro ao salvar o partido.'
            );
        }

        if (data.sucesso === true) {
            mensagem.textContent = data.mensagem;

            // No cadastro, limpa para o próximo; na edição, mantém o que foi salvo
            if (!modoEdicao) {
                form.reset();
            }
        } else {
            mensagem.textContent =
                data.mensagem || 'Não foi possível salvar o partido.';
        }

    } catch (error) {
        console.error('Erro ao salvar partido:', error);
        mensagem.textContent = error.message;
    } finally {
        botao.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const formPartido = document.getElementById('formPartido');

    formPartido.addEventListener('submit', salvarPartido);

    if (modoEdicao) {
        prepararEdicao();
    }
});
