// Aba "Projetos de lei" do painel do vereador:
// publicar um projeto e acompanhar os projetos publicados (situação e apoios).
// A verificação de login fica no scriptPainelVereador.js.

document.addEventListener('DOMContentLoaded', function () {

    const formProjeto = document.getElementById('formProjeto');
    const mensagemProjeto = document.getElementById('mensagemProjeto');
    const resumoProjetos = document.getElementById('resumoProjetos');
    const listaProjetos = document.getElementById('listaProjetosVereador');

    // Situações de tramitação (Em tramitação, Aprovado, ...), vindas do banco
    let situacoes = [];

    // Converte "2026-10-29 14:30:00" em "29/10/2026"
    function formatarData(dataBanco) {
        const data = new Date(dataBanco.replace(' ', 'T'));
        return isNaN(data) ? dataBanco : data.toLocaleDateString('pt-BR');
    }

    async function carregarSituacoes() {
        try {
            const resposta = await fetch('../../backend/php/listarSituacoesProjeto.php');
            const dados = await resposta.json();

            if (resposta.ok && dados.sucesso === true) {
                situacoes = dados.situacoes;
            }
        } catch (erro) {
            console.error('Erro ao carregar situações:', erro);
        }
    }

    // Controle para mudar a situação de um projeto
    function criarControleSituacao(projeto, paragrafoSituacao) {

        const bloco = document.createElement('div');
        bloco.classList.add('alterar-status');

        const idCampo = 'situacao-' + projeto.id_projeto;

        const label = document.createElement('label');
        label.htmlFor = idCampo;
        label.textContent = 'Alterar situação:';

        const select = document.createElement('select');
        select.id = idCampo;

        situacoes.forEach(function (situacao) {
            const opcao = document.createElement('option');
            opcao.value = situacao.id_situacao;
            opcao.textContent = situacao.nome_da_situacao;
            opcao.selected = situacao.id_situacao === projeto.id_situacao;
            select.appendChild(opcao);
        });

        const botao = document.createElement('button');
        botao.type = 'button';
        botao.textContent = 'Atualizar situação';

        const mensagem = document.createElement('p');
        mensagem.setAttribute('role', 'status');

        botao.addEventListener('click', async function () {

            const novaSituacao = Number(select.value);

            if (novaSituacao === projeto.id_situacao) {
                mensagem.textContent = 'O projeto já está com esta situação.';
                return;
            }

            botao.disabled = true;
            mensagem.textContent = 'Atualizando...';

            try {
                const resposta = await fetch('../../backend/php/atualizarSituacaoProjeto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_projeto: projeto.id_projeto,
                        id_situacao: novaSituacao
                    })
                });

                const dados = await resposta.json();

                if (resposta.status === 401) {
                    window.location.replace('loginVereador.html');
                    return;
                }

                if (!resposta.ok || dados.sucesso !== true) {
                    mensagem.textContent = dados.mensagem || 'Não foi possível atualizar.';
                    return;
                }

                projeto.id_situacao = novaSituacao;
                projeto.situacao = select.options[select.selectedIndex].textContent;
                paragrafoSituacao.textContent = 'Situação: ' + projeto.situacao;
                mensagem.textContent = dados.mensagem;

            } catch (erro) {
                console.error('Erro ao atualizar situação:', erro);
                mensagem.textContent = 'Erro ao se comunicar com o servidor.';
            } finally {
                botao.disabled = false;
            }
        });

        bloco.append(label, select, botao, mensagem);
        return bloco;
    }

    // Cartão de um projeto de lei
    function criarProjeto(projeto) {

        const artigo = document.createElement('article');
        artigo.classList.add('indicacao-admin', 'projeto-lei');

        const titulo = document.createElement('h3');
        titulo.textContent = projeto.titulo;

        const situacao = document.createElement('p');
        situacao.textContent = 'Situação: ' + projeto.situacao;

        const ementa = document.createElement('p');
        ementa.classList.add('ementa');
        ementa.textContent = projeto.ementa;

        // O texto completo pode ser longo: fica recolhido até o vereador abrir
        const detalhes = document.createElement('details');
        const resumo = document.createElement('summary');
        resumo.textContent = 'Ler o texto completo';
        const texto = document.createElement('p');
        texto.classList.add('texto-projeto');
        texto.textContent = projeto.texto;
        detalhes.append(resumo, texto);

        const areaAnexo = document.createElement('div');
        areaAnexo.classList.add('anexo-indicacao');

        if (projeto.anexo_pdf) {
            const link = document.createElement('a');
            link.href = '../../' + projeto.anexo_pdf;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = 'Abrir o projeto oficial (PDF)';
            areaAnexo.appendChild(link);
        }

        const info = document.createElement('p');
        const apoios = projeto.total_apoios === 1 ? '1 apoio' : projeto.total_apoios + ' apoios';
        info.textContent = 'Publicado em ' + formatarData(projeto.data_publicacao) + ' · ' + apoios + ' dos cidadãos';

        artigo.append(titulo, situacao, ementa, detalhes, areaAnexo, info);

        if (situacoes.length > 0) {
            artigo.appendChild(criarControleSituacao(projeto, situacao));
        }

        return artigo;
    }

    async function carregarProjetos() {

        resumoProjetos.textContent = 'Carregando projetos...';
        listaProjetos.replaceChildren();

        try {
            const resposta = await fetch('../../backend/php/listarProjetosVereador.php');
            const dados = await resposta.json();

            // Sem login: o scriptPainelVereador.js já leva para o login
            if (resposta.status === 401) {
                return;
            }

            if (!resposta.ok || dados.sucesso !== true) {
                throw new Error(dados.mensagem || 'Erro ao carregar projetos.');
            }

            const total = dados.projetos.length;

            if (total === 0) {
                resumoProjetos.textContent = 'Você ainda não publicou nenhum projeto de lei.';
                return;
            }

            resumoProjetos.textContent = total === 1
                ? '1 projeto de lei publicado.'
                : total + ' projetos de lei publicados.';

            dados.projetos.forEach(function (projeto) {
                listaProjetos.appendChild(criarProjeto(projeto));
            });

        } catch (erro) {
            console.error('Erro ao carregar projetos:', erro);
            resumoProjetos.textContent = 'Não foi possível carregar os projetos de lei.';
        }
    }

    // Publicar: envia com FormData por causa do PDF
    formProjeto.addEventListener('submit', async function (event) {

        event.preventDefault();

        const botao = formProjeto.querySelector('button[type="submit"]');
        botao.disabled = true;
        mensagemProjeto.textContent = 'Publicando...';

        try {
            const resposta = await fetch('../../backend/php/publicarProjeto.php', {
                method: 'POST',
                body: new FormData(formProjeto)
            });

            const dados = await resposta.json();

            if (resposta.status === 401) {
                window.location.replace('loginVereador.html');
                return;
            }

            mensagemProjeto.textContent = dados.mensagem || 'Não foi possível publicar o projeto.';

            if (resposta.ok && dados.sucesso === true) {
                formProjeto.reset();
                carregarProjetos();
            }

        } catch (erro) {
            console.error('Erro ao publicar projeto:', erro);
            mensagemProjeto.textContent = 'Erro ao se comunicar com o servidor.';
        } finally {
            botao.disabled = false;
        }
    });

    async function iniciar() {
        await carregarSituacoes();
        carregarProjetos();
    }

    iniciar();
});
