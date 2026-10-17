document.addEventListener('DOMContentLoaded', function () {

    const formLogin = document.getElementById('formLoginVereador');
    const mensagem = document.getElementById('mensagem');

    formLogin.addEventListener('submit', async function (event) {

        event.preventDefault();

        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value;

        const botao = formLogin.querySelector('button[type="submit"]');

        botao.disabled = true;
        mensagem.textContent = 'Verificando login...';

        try {

            const resposta = await fetch(
                '../../backend/php/loginVereador.php',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        senha: senha
                    })
                }
            );

            const resultado = await resposta.json();

            if (!resposta.ok || resultado.sucesso !== true) {
                mensagem.textContent =
                    resultado.mensagem || 'Não foi possível entrar.';
                return;
            }

            // Login aceito: vai para o painel do vereador
            window.location.href = 'painelVereador.html';

        } catch (erro) {
            console.error('Erro no login do vereador:', erro);
            mensagem.textContent = 'Erro ao se comunicar com o servidor.';
        } finally {
            botao.disabled = false;
        }
    });
});
