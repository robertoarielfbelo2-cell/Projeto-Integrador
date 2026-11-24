document.addEventListener('DOMContentLoaded', function () {

    const formIndicacao = document.getElementById('formIndicacao');
    const mensagem = document.getElementById('mensagem');

    const selectVereador = document.getElementById('vereador');


    async function carregarVereadores() {

        try {


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


        event.preventDefault();


        const dadosIndicacao = new FormData(formIndicacao);


        const botao = formIndicacao.querySelector('button[type="submit"]');


        botao.disabled = true;

        mensagem.textContent = 'Enviando indicação...';

        try {


            const resposta = await fetch(
                '../../backend/php/cadastrarIndicacao.php',
                {
                    method: 'POST',
                    body: dadosIndicacao
                }
            );


            const resultado = await resposta.json();

   
            if (!resposta.ok || resultado.sucesso !== true) {

                mensagem.textContent =
                    resultado.mensagem ||
                    'Não foi possível cadastrar a indicação.';


                if (resposta.status === 401) {
                    window.location.href = 'login.html';
                    return;
                }

                return;
            }


            mensagem.textContent =
                resultado.mensagem || 'Indicação cadastrada com sucesso!';

            // Limpa os campos do formulário
            formIndicacao.reset();

        } catch (erro) {

            console.error('Erro ao cadastrar indicação:', erro);

            mensagem.textContent =
                'Erro ao se comunicar com o servidor.';

        } finally {


            botao.disabled = false;

        }

    });

});