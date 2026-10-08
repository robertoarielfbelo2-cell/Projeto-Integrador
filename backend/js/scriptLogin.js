const formLogin = document.getElementById('formLogin');
const mensagem = document.getElementById('mensagem');

formLogin.addEventListener('submit', function (event) {
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const senha = document.getElementById('senha').value;

    const dadosLogin = {
        email: email,
        senha: senha
    };

    mensagem.textContent = '';

    fetch('../../backend/php/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(dadosLogin)
    })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    mensagem.textContent =
                        data.mensagem || 'Não foi possível entrar.';
                    return;
                }

                if (data.sucesso === true) {
                    window.location.href = 'feedIndicacao.html';
                } else {
                    mensagem.textContent =
                        data.mensagem || 'Não foi possível entrar.';
                }
            });
        })
        .catch(error => {
            console.error('Erro no login:', error);
            mensagem.textContent = 'Erro ao se comunicar com o servidor.';
        });
});
