document.addEventListener('DOMContentLoaded', function () {

    const formIndicacao = document.getElementById('formIndicacao');
    const mensagem = document.getElementById('mensagem');

    const selectVereador = document.getElementById('vereador');

    // Preenche a lista de vereadores (RF11). A escolha é opcional: se a lista
    // não carregar, o cidadão ainda consegue enviar a indicação sem vereador.
    async function carregarVereadores() {

        try {

            // Só leitura: o fetch sem opções faz um GET
            const resposta = await fetch('../../backend/php/listarVereadores.php');

            if (!resposta.ok) {
                throw new Error('Erro HTTP: ' + resposta.status);
            }

            // O PHP devolve uma lista: [{ id_vereador, nome_parlamentar, sigla, ... }, ...]
            const vereadores = await resposta.json();

            vereadores.forEach(function (vereador) {
                const opcao = document.createElement('option');
                opcao.value = vereador.id_vereador;
                opcao.textContent = vereador.nome_parlamentar + ' (' + vereador.sigla + ')';
                selectVereador.appendChild(opcao);
            });

        } catch (erro) {
            console.error('Erro ao carregar vereadores:', erro);
        }
    }

    carregarVereadores();

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
                resultado.mensagem || 'Indicação cadastrada com sucesso!';

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