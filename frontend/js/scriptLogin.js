document.addEventListener('DOMContentLoaded', function () {

    const formLogin = document.getElementById('formLogin');
    const mensagem = document.getElementById('mensagem');

    formLogin.addEventListener('submit', async function (event) {
        event.preventDefault();


        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value;


        const dadosLogin = {
            email: email,
            senha: senha
        };

        const botao = formLogin.querySelector('button[type="submit"]');

        botao.disabled = true;
        mensagem.textContent = 'Verificando login...';

        try {

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


            const resultado = await resposta.json();

            if (!resposta.ok || resultado.sucesso !== true) {
                mensagem.textContent =
                    resultado.mensagem || 'E-mail ou senha incorretos.';
                return;
            }


            mensagem.textContent = 'Login realizado com sucesso!';


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