document.addEventListener('DOMContentLoaded', function () {

    // Cria uma célula de texto
    function celula(texto) {
        const td = document.createElement('td');
        td.textContent = texto;
        return td;
    }

    // Cria a célula com o link "Editar" que abre o formulário no modo de edição
    function celulaEditar(pagina, id, nome) {
        const td = document.createElement('td');
        const link = document.createElement('a');

        link.href = pagina + '?id=' + encodeURIComponent(id);
        link.textContent = 'Editar';
        link.classList.add('botao-editar');
        // Leitores de tela anunciam "Editar Amália Tortato", não só "Editar"
        link.setAttribute('aria-label', 'Editar ' + nome);

        td.appendChild(link);
        return td;
    }

    // 1. Só administradores usam esta tela. O scriptCabecalho.js já consultou o
    // perfil e redireciona para o login quando não é administrador.
    async function verificarAdmin() {
        const dados = await window.perfilUsuario;
        return dados.perfil === 'administrador';
    }

    // 2. Lista de vereadores
    async function carregarVereadores() {
        const mensagem = document.getElementById('mensagemVereadores');
        const tabela = document.getElementById('tabelaVereadores');
        const corpo = tabela.querySelector('tbody');

        try {
            const resposta = await fetch('../../backend/php/listarVereadores.php');
            const vereadores = await resposta.json();

            if (!resposta.ok || !Array.isArray(vereadores)) {
                throw new Error('Resposta inválida.');
            }

            if (vereadores.length === 0) {
                mensagem.textContent = 'Nenhum vereador cadastrado.';
                return;
            }

            vereadores.forEach(function (vereador) {
                const linha = document.createElement('tr');

                linha.append(
                    celula(vereador.nome_parlamentar),
                    celula(vereador.sigla),
                    celulaEditar('addVereador.html', vereador.id_vereador, vereador.nome_parlamentar)
                );

                corpo.appendChild(linha);
            });

            mensagem.textContent = vereadores.length + ' vereadores cadastrados.';
            tabela.hidden = false;

        } catch (erro) {
            console.error('Erro ao carregar vereadores:', erro);
            mensagem.textContent = 'Não foi possível carregar os vereadores.';
        }
    }

    // 3. Lista de partidos
    async function carregarPartidos() {
        const mensagem = document.getElementById('mensagemPartidos');
        const tabela = document.getElementById('tabelaPartidos');
        const corpo = tabela.querySelector('tbody');

        try {
            const resposta = await fetch('../../backend/php/listarPartidos.php');
            const partidos = await resposta.json();

            if (!resposta.ok || !Array.isArray(partidos)) {
                throw new Error('Resposta inválida.');
            }

            if (partidos.length === 0) {
                mensagem.textContent = 'Nenhum partido cadastrado.';
                return;
            }

            partidos.forEach(function (partido) {
                const linha = document.createElement('tr');

                linha.append(
                    celula(partido.nome),
                    celula(partido.sigla),
                    celula(partido.numero),
                    celulaEditar('addPartido.html', partido.id, partido.nome)
                );

                corpo.appendChild(linha);
            });

            mensagem.textContent = partidos.length + ' partidos cadastrados.';
            tabela.hidden = false;

        } catch (erro) {
            console.error('Erro ao carregar partidos:', erro);
            mensagem.textContent = 'Não foi possível carregar os partidos.';
        }
    }

    async function iniciar() {
        const ehAdmin = await verificarAdmin();

        if (ehAdmin) {
            carregarVereadores();
            carregarPartidos();
        }
    }

    iniciar();
});
