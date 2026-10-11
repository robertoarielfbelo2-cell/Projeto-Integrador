document.addEventListener('DOMContentLoaded', function () {

    const formLogin = document.getElementById('formLogin');
    const mensagem = document.getElementById('mensagem');

    formLogin.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Captura os dados digitados
        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value;

        // Organiza os dados para enviar ao PHP
        const dadosLogin = {
            email: email,
            senha: senha
        };

        const botao = formLogin.querySelector('button[type="submit"]');

        botao.disabled = true;
        mensagem.textContent = 'Verificando login...';

        try {
            // Envia os dados ao PHP
            const resposta = await fetch(
                '../../backend/php/login.php',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dadosLogin)
                }
            );

            // Recebe a resposta do servidor
            const resultado = await resposta.json();

            if (!resposta.ok || resultado.sucesso !== true) {
                mensagem.textContent =
                    resultado.mensagem || 'E-mail ou senha incorretos.';
                return;
            }

            // Login realizado com sucesso
            mensagem.textContent = 'Login realizado com sucesso!';

            // Redireciona para o feed de indicações
            window.location.href = 'index.html';

        } catch (erro) {
            console.error('Erro no login:', erro);

            mensagem.textContent =
                'Erro ao se comunicar com o servidor.';

        } finally {
            botao.disabled = false;
        }
    });
});