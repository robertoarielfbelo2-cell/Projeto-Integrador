document.addEventListener('DOMContentLoaded', function () {

    const formLoginAdmin = document.getElementById('formLoginAdmin');
    const mensagem = document.getElementById('mensagem');

    formLoginAdmin.addEventListener('submit', async function (event) {

        event.preventDefault();


        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value;

        const botao = formLoginAdmin.querySelector('button[type="submit"]');

        botao.disabled = true;
        mensagem.textContent = 'Verificando login...';

        try {


            const resposta = await fetch(
                '../../backend/php/loginAdmin.php',
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

            // 4. Verifica se o login foi autorizado
            if (!resposta.ok || resultado.sucesso !== true) {

                mensagem.textContent =
                    resultado.mensagem || 'Erro ao realizar login.';

                return;
            }

    
            mensagem.textContent = 'Login realizado com sucesso!';

  
            window.location.href = 'painelAdmin.html';

        } catch (erro) {

            console.error('Erro:', erro);

            mensagem.textContent =
                'Não foi possível conectar ao servidor.';

        } finally {

            botao.disabled = false;

        }

    });

});