async function cadastrarPartido(event) {
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

    botao.disabled = true;
    mensagem.textContent = 'Cadastrando partido...';

    try {
        const response = await fetch(
            '../../backend/php/cadastrarPartido.php',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dadosPartido)
            }
        );

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.mensagem || data.erro || 'Erro ao cadastrar partido.'
            );
        }

        if (data.sucesso === true) {
            mensagem.textContent =
                data.mensagem || 'Partido cadastrado com sucesso!';

            form.reset();
        } else {
            mensagem.textContent =
                data.mensagem || 'Não foi possível cadastrar o partido.';
        }

    } catch (error) {
        console.error('Erro ao cadastrar partido:', error);
        mensagem.textContent = error.message;
    } finally {
        botao.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const formPartido = document.getElementById('formPartido');

    formPartido.addEventListener('submit', cadastrarPartido);
});