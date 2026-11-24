document.addEventListener('DOMContentLoaded', function () {

    const listaIndicacoesAdmin = document.getElementById('listaIndicacoesAdmin');

    let statusDisponiveis = [];

    async function verificarSessaoAdmin() {

        const dados = await window.perfilUsuario;

        return dados.perfil === 'administrador';
    }

    async function carregarStatus() {

        const resposta = await fetch(
            '../../backend/php/listarStatus.php'
        );

        if (!resposta.ok) {
            throw new Error('Erro ao carregar os status.');
        }

        const dados = await resposta.json();

        if (!dados.sucesso || !Array.isArray(dados.status)) {
            throw new Error('Resposta inválida ao carregar status.');
        }

        statusDisponiveis = dados.status;
    }


    function criarIndicacaoAdmin(indicacao) {

        const artigo = document.createElement('article');
        artigo.classList.add('indicacao-admin');

        const titulo = document.createElement('h3');
        titulo.textContent = indicacao.titulo;

        const descricao = document.createElement('p');
        descricao.textContent = indicacao.descricao;


        const areaAnexo = document.createElement('div');
        areaAnexo.classList.add('anexo-indicacao');

        if (indicacao.foto_ou_anexo) {

            const caminhoAnexo = indicacao.foto_ou_anexo;
            const urlAnexo = '../../' + caminhoAnexo;

            if (/\.(jpg|jpeg|png|webp)$/i.test(caminhoAnexo)) {

                // Exibe a imagem
                const imagem = document.createElement('img');

                imagem.src = urlAnexo;
                imagem.alt = 'Imagem anexada à indicação';
                imagem.loading = 'lazy';

                areaAnexo.appendChild(imagem);

            } else if (/\.pdf$/i.test(caminhoAnexo)) {


                const link = document.createElement('a');

                link.href = urlAnexo;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.textContent = 'Visualizar documento PDF';

                areaAnexo.appendChild(link);
            }
        }

        const autor = document.createElement('p');
        autor.textContent = 'Autor: ' + indicacao.autor;

        const localizacao = document.createElement('p');
        localizacao.textContent =
            'Localização: ' +
            indicacao.bairro + ', ' +
            indicacao.municipio + ' - ' +
            indicacao.estado +
            ' | CEP: ' + indicacao.cep;

        const vereador = document.createElement('p');
        vereador.textContent = indicacao.vereador
            ? 'Encaminhada para: ' + indicacao.vereador + ' (' + indicacao.vereador_partido + ')'
            : 'Encaminhada para: nenhum vereador específico';

        const apoios = document.createElement('p');
        apoios.textContent =
            'Quantidade de apoios: ' + indicacao.total_apoios;

        const statusAtual = document.createElement('p');
        statusAtual.textContent =
            'Status atual: ' + indicacao.status;


        const selectStatus = document.createElement('select');

        statusDisponiveis.forEach(function (status) {

            const opcao = document.createElement('option');

            opcao.value = status.id_status;
            opcao.textContent = status.nome_do_status;

            if (Number(status.id_status) === Number(indicacao.id_status)) {
                opcao.selected = true;
            }

            selectStatus.appendChild(opcao);
        });

        const botaoSalvar = document.createElement('button');
        botaoSalvar.type = 'button';
        botaoSalvar.textContent = 'Atualizar status';

        const mensagem = document.createElement('p');

        botaoSalvar.addEventListener('click', async function () {

            botaoSalvar.disabled = true;
            mensagem.textContent = 'Atualizando...';

            try {

                const resposta = await fetch(
                    '../../backend/php/atualizarStatus.php',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_indicacao: indicacao.id_indicacao,
                            id_status: Number(selectStatus.value)
                        })
                    }
                );

                const resultado = await resposta.json();

                if (!resposta.ok || resultado.sucesso !== true) {

                    if (resposta.status === 401) {
                        window.location.replace('loginAdmin.html');
                        return;
                    }

                    mensagem.textContent =
                        resultado.mensagem || 'Erro ao atualizar status.';

                    return;
                }

                const nomeStatus =
                    selectStatus.options[selectStatus.selectedIndex].textContent;

                statusAtual.textContent =
                    'Status atual: ' + nomeStatus;

                mensagem.textContent =
                    'Status atualizado com sucesso!';

            } catch (erro) {

                console.error(erro);

                mensagem.textContent =
                    'Erro de comunicação com o servidor.';

            } finally {

                botaoSalvar.disabled = false;
            }

        });

        artigo.append(
            titulo,
            descricao,
            areaAnexo,
            autor,
            localizacao,
            vereador,
            apoios,
            statusAtual,
            selectStatus,
            botaoSalvar,
            mensagem
        );
        return artigo;
    }


    async function carregarIndicacoesAdmin() {

        listaIndicacoesAdmin.textContent =
            'Carregando indicações...';

        try {

            const resposta = await fetch(
                '../../backend/php/listarIndicacoesAdmin.php'
            );

            const dados = await resposta.json();

            if (!resposta.ok || dados.sucesso !== true) {
                throw new Error(
                    dados.mensagem || 'Erro ao carregar indicações.'
                );
            }

            listaIndicacoesAdmin.replaceChildren();

            if (dados.indicacoes.length === 0) {

                listaIndicacoesAdmin.textContent =
                    'Nenhuma indicação cadastrada.';

                return;
            }

            dados.indicacoes.forEach(function (indicacao) {

                const artigo = criarIndicacaoAdmin(indicacao);

                listaIndicacoesAdmin.appendChild(artigo);
            });

        } catch (erro) {

            console.error(erro);

            listaIndicacoesAdmin.textContent =
                'Não foi possível carregar as indicações.';
        }
    }


    async function iniciarPainel() {

        const autenticado = await verificarSessaoAdmin();

        if (!autenticado) {
            return;
        }

        try {

            await carregarStatus();
            await carregarIndicacoesAdmin();

        } catch (erro) {

            console.error(erro);

            listaIndicacoesAdmin.textContent =
                'Erro ao inicializar o painel administrativo.';
        }
    }

    iniciarPainel();

});