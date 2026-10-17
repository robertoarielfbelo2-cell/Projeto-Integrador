document.addEventListener('DOMContentLoaded', function () {

    const formAcesso = document.getElementById('formAcessoVereador');
    const selectVereador = document.getElementById('vereador');
    const campoEmail = document.getElementById('email');
    const mensagem = document.getElementById('mensagem');

    // E-mail atual de cada vereador, para preencher o campo ao trocar a seleção
    let emailsPorVereador = {};

    async function carregarVereadores() {

        try {

            const resposta = await fetch(
                '../../backend/php/listarAcessoVereadores.php'
            );

            const resultado = await resposta.json();

            // 403: não há administrador logado
            if (resposta.status === 403) {
                window.location.href = 'loginAdmin.html';
                return;
            }

            if (!resposta.ok || resultado.sucesso !== true) {
                throw new Error(resultado.mensagem || 'Erro ao carregar vereadores.');
            }

            selectVereador.replaceChildren();

            const opcaoInicial = document.createElement('option');
            opcaoInicial.value = '';
            opcaoInicial.textContent = 'Selecione um vereador';
            selectVereador.appendChild(opcaoInicial);

            emailsPorVereador = {};

            resultado.vereadores.forEach(function (vereador) {
                const opcao = document.createElement('option');
                opcao.value = vereador.id_vereador;

                // Mostra quem já tem acesso, para o admin saber se está criando ou trocando
                opcao.textContent =
                    vereador.nome_parlamentar + ' (' + vereador.sigla + ')' +
                    (vereador.email ? ' - com acesso' : ' - sem acesso');

                selectVereador.appendChild(opcao);

                emailsPorVereador[vereador.id_vereador] = vereador.email || '';
            });

        } catch (erro) {
            console.error('Erro ao carregar vereadores:', erro);
            mensagem.textContent = 'Não foi possível carregar os vereadores.';
        }
    }

    // Ao escolher um vereador que já tem acesso, mostra o e-mail atual
    selectVereador.addEventListener('change', function () {
        campoEmail.value = emailsPorVereador[selectVereador.value] || '';
    });

    formAcesso.addEventListener('submit', async function (event) {

        event.preventDefault();

        const senha = document.getElementById('senha').value;
        const confirmarSenha = document.getElementById('confirmarSenha').value;

        if (senha !== confirmarSenha) {
            mensagem.textContent = 'As senhas não conferem.';
            return;
        }

        const dados = {
            id_vereador: selectVereador.value,
            email: campoEmail.value.trim(),
            senha: senha
        };

        const botao = formAcesso.querySelector('button[type="submit"]');
        botao.disabled = true;
        mensagem.textContent = 'Salvando...';

        try {

            const resposta = await fetch(
                '../../backend/php/definirAcessoVereador.php',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dados)
                }
            );

            const resultado = await resposta.json();

            mensagem.textContent =
                resultado.mensagem || 'Não foi possível salvar o acesso.';

            if (resposta.ok && resultado.sucesso === true) {
                formAcesso.reset();
                // Recarrega a lista para o vereador aparecer como "com acesso"
                carregarVereadores();
            }

        } catch (erro) {
            console.error('Erro ao salvar acesso:', erro);
            mensagem.textContent = 'Erro ao se comunicar com o servidor.';
        } finally {
            botao.disabled = false;
        }
    });

    carregarVereadores();
});
