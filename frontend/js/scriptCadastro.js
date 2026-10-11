document.addEventListener('DOMContentLoaded', function () {

    const formulario = document.getElementById('formCadastro');
    const mensagem = document.getElementById('mensagem');

    formulario.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Captura os dados preenchidos pelo cidadão
        const nome = document.getElementById('nome').value.trim();
        const email = document.getElementById('email').value.trim();
        const telefone = document.getElementById('telefone').value;
        const dataNascimento = document.getElementById('data_nascimento').value;
        const cpf = document.getElementById('cpf').value;
        const senha = document.getElementById('senha').value;

        // Verifica se o cidadão possui 18 anos completos
        const hoje = new Date();
        const nascimento = new Date(dataNascimento + 'T00:00:00');

        let idade = hoje.getFullYear() - nascimento.getFullYear();

        const aindaNaoFezAniversario =
            hoje.getMonth() < nascimento.getMonth() ||
            (
                hoje.getMonth() === nascimento.getMonth() &&
                hoje.getDate() < nascimento.getDate()
            );

        if (aindaNaoFezAniversario) {
            idade--;
        }

        if (idade < 18) {
            mensagem.textContent =
                'O cadastro é permitido apenas para maiores de 18 anos.';
            return;
        }

        // Organiza os dados para enviar ao PHP
        const dadosCadastro = {
            nome: nome,
            email: email,
            telefone: telefone,
            data_nascimento: dataNascimento,
            cpf: cpf,
            senha: senha
        };

        const botao = formulario.querySelector('button[type="submit"]');

        botao.disabled = true;
        mensagem.textContent = 'Realizando cadastro...';

        try {
            // Envia os dados ao PHP
            const resposta = await fetch(
                '../../backend/php/cadastrar.php',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dadosCadastro)
                }
            );

            // Recebe a resposta do PHP
            const resultado = await resposta.json();

            if (!resposta.ok) {
                throw new Error(
                    resultado.mensagem || 'Erro ao realizar cadastro.'
                );
            }

            if (resultado.sucesso === true) {
                mensagem.textContent =
                    'Cidadão cadastrado com sucesso!';

                formulario.reset();
            } else {
                mensagem.textContent =
                    resultado.mensagem || 'Não foi possível realizar o cadastro.';
            }

        } catch (erro) {
            console.error('Erro no cadastro:', erro);

            mensagem.textContent =
                erro.message || 'Erro ao comunicar com o servidor.';

        } finally {
            botao.disabled = false;
        }
    });
});