// Aba "Projetos de lei" da página inicial: o cidadão vê os projetos dos
// vereadores, filtra por situação e apoia (um apoio por projeto).
// Os projetos só são buscados quando a aba é aberta pela primeira vez.

document.addEventListener('DOMContentLoaded', function () {

    const painel = document.getElementById('painel-projetos');
    const filtroSituacao = document.getElementById('filtroSituacao');
    const resumoProjetos = document.getElementById('resumoProjetos');
    const listaProjetos = document.getElementById('listaProjetos');

    let jaCarregou = false;

    // Converte "2026-10-29 14:30:00" em "29/10/2026"
    function formatarData(dataBanco) {
        const data = new Date(dataBanco.replace(' ', 'T'));
        return isNaN(data) ? dataBanco : data.toLocaleDateString('pt-BR');
    }

    function textoApoios(total) {
        return total === 1 ? '1 apoio' : total + ' apoios';
    }

    async function carregarSituacoes() {
        try {
            const resposta = await fetch('../../backend/php/listarSituacoesProjeto.php');
            const dados = await resposta.json();

            if (!resposta.ok || dados.sucesso !== true) {
                return;
            }

            dados.situacoes.forEach(function (situacao) {
                const opcao = document.createElement('option');
                opcao.value = situacao.id_situacao;
                opcao.textContent = situacao.nome_da_situacao;
                filtroSituacao.appendChild(opcao);
            });

        } catch (erro) {
            // Sem a lista, a aba continua funcionando, só sem filtro
            console.error('Erro ao carregar situações:', erro);
        }
    }

    // Botão Apoiar de um projeto
    function criarBotaoApoiar(projeto, paragrafoApoios, mensagem) {

        const botao = document.createElement('button');
        botao.type = 'button';

        if (projeto.ja_apoiou) {
            botao.textContent = 'Apoiado';
            botao.disabled = true;
            return botao;
        }

        botao.textContent = 'Apoiar';

        botao.addEventListener('click', async function () {

            // Só cidadão apoia: visitante vai para o login
            const perfil = await window.perfilUsuario;

            if (perfil.perfil !== 'cidadao') {
                if (perfil.perfil === null) {
                    window.location.href = 'login.html';
                } else {
                    mensagem.textContent = 'Só cidadãos podem apoiar projetos de lei.';
                }
                return;
            }

            botao.disabled = true;

            try {
                const resposta = await fetch('../../backend/php/apoiarProjeto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id_projeto: projeto.id_projeto })
                });

                const dados = await resposta.json();

                if (resposta.status === 401) {
                    window.location.href = 'login.html';
                    return;
                }

                if (!resposta.ok || dados.sucesso !== true) {
                    mensagem.textContent = dados.mensagem || 'Não foi possível apoiar o projeto.';
                    botao.disabled = false;
                    return;
                }

                paragrafoApoios.textContent = 'Apoios: ' + dados.total_apoios;
                botao.textContent = 'Apoiado';
                mensagem.textContent = dados.mensagem;

            } catch (erro) {
                console.error('Erro ao apoiar projeto:', erro);
                mensagem.textContent = 'Erro ao se comunicar com o servidor.';
                botao.disabled = false;
            }
        });

        return botao;
    }

    // Cartão de um projeto de lei
    function criarProjeto(projeto) {

        const artigo = document.createElement('article');
        artigo.classList.add('indicacao', 'projeto-lei');

        const titulo = document.createElement('h3');
        titulo.textContent = projeto.titulo;

        const ementa = document.createElement('p');
        ementa.classList.add('ementa');
        ementa.textContent = projeto.ementa;

        const situacao = document.createElement('p');
        situacao.textContent = 'Situação: ' + projeto.situacao;

        const autor = document.createElement('p');
        autor.textContent =
            'Apresentado por ' + projeto.vereador + ' (' + projeto.vereador_partido + ')' +
            ' em ' + formatarData(projeto.data_publicacao);

        // O texto completo pode ser longo: fica recolhido até o cidadão abrir
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

        const apoios = document.createElement('p');
        apoios.textContent = 'Apoios: ' + projeto.total_apoios;

        const mensagem = document.createElement('p');
        mensagem.classList.add('mensagem-projeto');
        mensagem.setAttribute('role', 'status');

        const botaoApoiar = criarBotaoApoiar(projeto, apoios, mensagem);

        artigo.append(titulo, ementa, situacao, autor, detalhes, areaAnexo, apoios, botaoApoiar, mensagem);

        return artigo;
    }

    async function carregarProjetos() {

        listaProjetos.replaceChildren();
        resumoProjetos.textContent = 'Carregando projetos...';

        let url = '../../backend/php/listarProjetos.php';

        if (filtroSituacao.value !== '') {
            url += '?situacao=' + encodeURIComponent(filtroSituacao.value);
        }

        try {
            const resposta = await fetch(url);
            const dados = await resposta.json();

            if (!resposta.ok || dados.sucesso !== true) {
                throw new Error(dados.mensagem || 'Erro ao carregar projetos.');
            }

            const total = dados.projetos.length;

            if (total === 0) {
                resumoProjetos.textContent = filtroSituacao.value === ''
                    ? 'Nenhum projeto de lei publicado ainda.'
                    : 'Nenhum projeto de lei com esta situação.';
                return;
            }

            resumoProjetos.textContent = total === 1
                ? '1 projeto de lei encontrado.'
                : total + ' projetos de lei encontrados.';

            dados.projetos.forEach(function (projeto) {
                listaProjetos.appendChild(criarProjeto(projeto));
            });

        } catch (erro) {
            console.error('Erro ao carregar projetos:', erro);
            resumoProjetos.textContent = 'Não foi possível carregar os projetos de lei.';
        }
    }

    // Busca os dados só na primeira vez que a aba aparece
    async function carregarNaPrimeiraVez() {
        if (jaCarregou) {
            return;
        }

        jaCarregou = true;
        await carregarSituacoes();
        carregarProjetos();
    }

    filtroSituacao.addEventListener('change', carregarProjetos);

    painel.addEventListener('abaAberta', carregarNaPrimeiraVez);

    // A aba pode já ter sido aberta pelo endereço (index.html#projetos)
    if (!painel.hidden) {
        carregarNaPrimeiraVez();
    }
});
