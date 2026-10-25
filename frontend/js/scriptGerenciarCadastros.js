document.addEventListener('DOMContentLoaded', function () {

    const mensagemVereadores = document.getElementById('mensagemVereadores');
    const mensagemPartidos = document.getElementById('mensagemPartidos');

    // Cria uma célula de texto
    function celula(texto) {
        const td = document.createElement('td');
        td.textContent = texto;
        return td;
    }

    // Atualiza o resumo "N vereadores cadastrados" a partir das linhas da tabela
    function atualizarResumo(tabela, mensagem, singular, plural) {
        const total = tabela.querySelectorAll('tbody tr').length;

        if (total === 0) {
            tabela.hidden = true;
            return 'Nenhum ' + singular + ' cadastrado.';
        }

        return total === 1 ? '1 ' + singular + ' cadastrado.' : total + ' ' + plural + ' cadastrados.';
    }

    // Célula com os botões Editar e Remover de uma linha
    function celulaAcoes(opcoes) {
        const td = document.createElement('td');
        td.classList.add('acoes');

        // Editar: abre o formulário de cadastro no modo de edição
        const editar = document.createElement('a');
        editar.href = opcoes.paginaEditar + '?id=' + encodeURIComponent(opcoes.id);
        editar.textContent = 'Editar';
        editar.classList.add('botao-editar');
        // Leitores de tela anunciam "Editar Amália Tortato", não só "Editar"
        editar.setAttribute('aria-label', 'Editar ' + opcoes.nome);

        // Remover: pede confirmação e chama o PHP
        const remover = document.createElement('button');
        remover.type = 'button';
        remover.textContent = 'Remover';
        remover.classList.add('botao-remover');
        remover.setAttribute('aria-label', 'Remover ' + opcoes.nome);

        remover.addEventListener('click', async function () {

            const confirmou = window.confirm(
                'Remover ' + opcoes.nome + '? Esta ação não pode ser desfeita.'
            );

            if (!confirmou) {
                return;
            }

            remover.disabled = true;
            opcoes.mensagem.textContent = 'Removendo ' + opcoes.nome + '...';

            try {
                const resposta = await fetch(opcoes.phpRemover, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: opcoes.id })
                });

                const dados = await resposta.json();

                if (resposta.status === 403) {
                    window.location.replace('loginAdmin.html');
                    return;
                }

                if (!resposta.ok || dados.sucesso !== true) {
                    // Ex.: o partido ainda tem vereadores; a mensagem explica o que fazer
                    opcoes.mensagem.textContent =
                        dados.mensagem || 'Não foi possível remover.';
                    return;
                }

                // Tira a linha da tabela e atualiza o total
                const tabela = td.closest('table');
                td.closest('tr').remove();

                opcoes.mensagem.textContent =
                    dados.mensagem + ' ' + opcoes.resumo(tabela);

            } catch (erro) {
                console.error('Erro ao remover:', erro);
                opcoes.mensagem.textContent = 'Erro ao se comunicar com o servidor.';
            } finally {
                remover.disabled = false;
            }
        });

        td.append(editar, remover);
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
        const tabela = document.getElementById('tabelaVereadores');
        const corpo = tabela.querySelector('tbody');

        const resumo = t => atualizarResumo(t, mensagemVereadores, 'vereador', 'vereadores');

        try {
            const resposta = await fetch('../../backend/php/listarVereadores.php');
            const vereadores = await resposta.json();

            if (!resposta.ok || !Array.isArray(vereadores)) {
                throw new Error('Resposta inválida.');
            }

            vereadores.forEach(function (vereador) {
                const linha = document.createElement('tr');

                linha.append(
                    celula(vereador.nome_parlamentar),
                    celula(vereador.sigla),
                    celulaAcoes({
                        id: vereador.id_vereador,
                        nome: vereador.nome_parlamentar,
                        paginaEditar: 'addVereador.html',
                        phpRemover: '../../backend/php/removerVereador.php',
                        mensagem: mensagemVereadores,
                        resumo: resumo
                    })
                );

                corpo.appendChild(linha);
            });

            tabela.hidden = vereadores.length === 0;
            mensagemVereadores.textContent = resumo(tabela);

        } catch (erro) {
            console.error('Erro ao carregar vereadores:', erro);
            mensagemVereadores.textContent = 'Não foi possível carregar os vereadores.';
        }
    }

    // 3. Lista de partidos
    async function carregarPartidos() {
        const tabela = document.getElementById('tabelaPartidos');
        const corpo = tabela.querySelector('tbody');

        const resumo = t => atualizarResumo(t, mensagemPartidos, 'partido', 'partidos');

        try {
            const resposta = await fetch('../../backend/php/listarPartidos.php');
            const partidos = await resposta.json();

            if (!resposta.ok || !Array.isArray(partidos)) {
                throw new Error('Resposta inválida.');
            }

            partidos.forEach(function (partido) {
                const linha = document.createElement('tr');

                linha.append(
                    celula(partido.nome),
                    celula(partido.sigla),
                    celula(partido.numero),
                    celulaAcoes({
                        id: partido.id,
                        nome: partido.nome,
                        paginaEditar: 'addPartido.html',
                        phpRemover: '../../backend/php/removerPartido.php',
                        mensagem: mensagemPartidos,
                        resumo: resumo
                    })
                );

                corpo.appendChild(linha);
            });

            tabela.hidden = partidos.length === 0;
            mensagemPartidos.textContent = resumo(tabela);

        } catch (erro) {
            console.error('Erro ao carregar partidos:', erro);
            mensagemPartidos.textContent = 'Não foi possível carregar os partidos.';
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
