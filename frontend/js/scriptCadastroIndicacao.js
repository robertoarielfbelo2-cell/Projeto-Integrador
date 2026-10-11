document.addEventListener('DOMContentLoaded', function () {

    const formIndicacao = document.getElementById('formIndicacao');
    const mensagem = document.getElementById('mensagem');

    formIndicacao.addEventListener('submit', async function (event) {

        // Impede o recarregamento da página
        event.preventDefault();

        // Captura todos os campos do formulário
        const dadosIndicacao = new FormData(formIndicacao);

        // Captura o botão de envio
        const botao = formIndicacao.querySelector('button[type="submit"]');

        // Evita vários envios ao mesmo tempo
        botao.disabled = true;

        mensagem.textContent = 'Enviando indicação...';

        try {

            // Envia os dados ao PHP
            const resposta = await fetch(
                '../../backend/php/cadastrarIndicacao.php',
                {
                    method: 'POST',
                    body: dadosIndicacao
                }
            );

            // Recebe a resposta do PHP
            const resultado = await resposta.json();

            // Verifica se ocorreu algum erro
            if (!resposta.ok || resultado.sucesso !== true) {

                mensagem.textContent =
                    resultado.mensagem ||
                    'Não foi possível cadastrar a indicação.';

                // Se não estiver logado, encaminha para o login
            if (resposta.status === 401) {
                window.location.href = 'login.html';
                return;
            }

                return;
            }

            // Cadastro realizado com sucesso
            mensagem.textContent =
                'Indicação cadastrada com sucesso!';

            // Limpa os campos do formulário
            formIndicacao.reset();

        } catch (erro) {

            console.error('Erro ao cadastrar indicação:', erro);

            mensagem.textContent =
                'Erro ao se comunicar com o servidor.';

        } finally {

            // Libera o botão novamente
            botao.disabled = false;

        }

    });

});