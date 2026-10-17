document.addEventListener('DOMContentLoaded', function () {

    const conteudoVereador = document.getElementById('conteudoVereador');
    const mensagemVereador = document.getElementById('mensagemVereador');
    const nomeVereador = document.getElementById('nomeVereador');
    const botaoSair = document.getElementById('botaoSair');
    const filtroStatus = document.getElementById('filtroStatusVereador');
    const resumoIndicacoes = document.getElementById('resumoIndicacoes');
    const listaIndicacoes = document.getElementById('listaIndicacoesVereador');

    // 1. Só mostra o painel se houver um vereador logado
    async function verificarSessaoVereador() {

        try {

            const resposta = await fetch(
                '../../backend/php/verificarSessaoVereador.php'
            );

            const dados = await resposta.json();

            if (!resposta.ok || dados.logado !== true) {
                window.location.replace('loginVereador.html');
                return false;
            }

            nomeVereador.textContent = dados.nome;

            conteudoVereador.hidden = false;
            mensagemVereador.hidden = true;

            return true;

        } catch (erro) {
            console.error('Erro ao verificar sessão do vereador:', erro);
            mensagemVereador.textContent = 'Erro ao verificar o acesso.';
            return false;
        }
    }

    // 2. Preenche o filtro com os status cadastrados no banco
    async function carregarStatus() {

        try {

            const resposta = await fetch('../../backend/php/listarStatus.php');
            const dados = await resposta.json();

            if (!resposta.ok || dados.sucesso !== true) {
                throw new Error(dados.mensagem || 'Erro ao carregar status.');
            }

            dados.status.forEach(function (status) {
                const opcao = document.createElement('option');
                opcao.value = status.id_status;
                opcao.textContent = status.nome_do_status;
                filtroStatus.appendChild(opcao);
            });

        } catch (erro) {
            // Sem a lista de status o painel continua funcionando, só sem filtro
            console.error('Erro ao carregar status:', erro);
        }
    }

    // Converte "2026-10-16 14:30:00" em "16/10/2026"
    function formatarData(dataBanco) {
        const data = new Date(dataBanco.replace(' ', 'T'));
        return isNaN(data) ? dataBanco : data.toLocaleDateString('pt-BR');
    }

    // Converte "2026-10-16 14:30:00" em "16/10/2026 14:30"
    function formatarDataHora(dataBanco) {
        const data = new Date(dataBanco.replace(' ', 'T'));

        if (isNaN(data)) {
            return dataBanco;
        }

        return data.toLocaleDateString('pt-BR') + ' ' +
            data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    }

    // Monta o bloco de uma resposta do vereador
    function criarElementoResposta(resposta) {

        const divResposta = document.createElement('div');
        divResposta.classList.add('comentario', 'resposta-vereador');

        const autorResposta = document.createElement('strong');
        autorResposta.textContent = resposta.vereador;

        const textoResposta = document.createElement('p');
        textoResposta.textContent = resposta.texto;

        const dataResposta = document.createElement('small');
        dataResposta.textContent = formatarDataHora(resposta.data_resposta);

        divResposta.append(autorResposta, textoResposta, dataResposta);

        return divResposta;
    }

    // Busca o histórico de respostas de uma indicação (RF14)
    async function carregarRespostas(idIndicacao, listaRespostas) {

        listaRespostas.textContent = 'Carregando respostas...';

        try {

            const resposta = await fetch(
                '../../backend/php/listarRespostas.php?id_indicacao=' +
                encodeURIComponent(idIndicacao)
            );

            const dados = await resposta.json();

            if (!resposta.ok || dados.sucesso !== true) {
                throw new Error(dados.mensagem || 'Erro ao carregar respostas.');
            }

            listaRespostas.replaceChildren();

            if (dados.respostas.length === 0) {
                listaRespostas.textContent = 'Você ainda não respondeu esta indicação.';
                return;
            }

            dados.respostas.forEach(function (item) {
                listaRespostas.appendChild(criarElementoResposta(item));
            });

        } catch (erro) {
            console.error('Erro ao carregar respostas:', erro);
            listaRespostas.textContent = 'Não foi possível carregar as respostas.';
        }
    }

    // Formulário para o vereador responder uma indicação
    function criarFormularioResposta(idIndicacao, listaRespostas) {

        const form = document.createElement('form');
        form.classList.add('form-comentario');

        // id único por indicação, para o label apontar para o campo certo
        const idCampo = 'resposta-' + idIndicacao;

        const label = document.createElement('label');
        label.htmlFor = idCampo;
        label.textContent = 'Sua resposta ao cidadão:';

        const campoTexto = document.createElement('textarea');
        campoTexto.id = idCampo;
        campoTexto.maxLength = 1000;
        campoTexto.required = true;
        campoTexto.placeholder = 'Ex.: Recebemos a indicação e encaminhamos à Secretaria responsável.';

        const contador = document.createElement('small');
        contador.textContent = '0/1000 caracteres';

        campoTexto.addEventListener('input', function () {
            contador.textContent = campoTexto.value.length + '/1000 caracteres';
        });

        const botao = document.createElement('button');
        botao.type = 'submit';
        botao.textContent = 'Publicar resposta';

        const mensagemForm = document.createElement('p');
        mensagemForm.setAttribute('role', 'status');

        form.append(label, campoTexto, contador, botao);

        form.addEventListener('submit', async function (event) {

            event.preventDefault();

            const texto = campoTexto.value.trim();

            if (texto === '') {
                mensagemForm.textContent = 'Escreva a resposta antes de publicar.';
                return;
            }

            botao.disabled = true;
            mensagemForm.textContent = 'Publicando...';

            try {

                const resposta = await fetch(
                    '../../backend/php/responderIndicacao.php',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_indicacao: idIndicacao,
                            texto: texto
                        })
                    }
                );

                const dados = await resposta.json();

                if (resposta.status === 401) {
                    window.location.replace('loginVereador.html');
                    return;
                }

                mensagemForm.textContent =
                    dados.mensagem || 'Não foi possível publicar a resposta.';

                if (resposta.ok && dados.sucesso === true) {

                    // Tira o aviso "ainda não respondeu" e mostra a nova resposta no fim
                    if (listaRespostas.querySelector('.resposta-vereador') === null) {
                        listaRespostas.replaceChildren();
                    }

                    listaRespostas.appendChild(criarElementoResposta(dados.resposta));

                    form.reset();
                    contador.textContent = '0/1000 caracteres';
                }

            } catch (erro) {
                console.error('Erro ao publicar resposta:', erro);
                mensagemForm.textContent = 'Erro ao se comunicar com o servidor.';
            } finally {
                botao.disabled = false;
            }
        });

        // A mensagem fica logo depois do formulário
        const bloco = document.createElement('div');
        bloco.append(form, mensagemForm);

        return bloco;
    }

    // 3. Cria o cartão de uma indicação
    function criarIndicacao(indicacao) {

        const artigo = document.createElement('article');
        artigo.classList.add('indicacao-admin');

        const titulo = document.createElement('h3');
        titulo.textContent = indicacao.titulo;

        const status = document.createElement('p');
        status.textContent = 'Status: ' + indicacao.status;

        const descricao = document.createElement('p');
        descricao.textContent = indicacao.descricao;

        // Anexo: imagem aparece na página, PDF vira link
        const areaAnexo = document.createElement('div');
        areaAnexo.classList.add('anexo-indicacao');

        if (indicacao.foto_ou_anexo) {

            const urlAnexo = '../../' + indicacao.foto_ou_anexo;

            if (/\.(jpg|jpeg|png|webp)$/i.test(indicacao.foto_ou_anexo)) {

                const imagem = document.createElement('img');
                imagem.src = urlAnexo;
                imagem.alt = 'Imagem anexada à indicação';
                imagem.loading = 'lazy';
                areaAnexo.appendChild(imagem);

            } else if (/\.pdf$/i.test(indicacao.foto_ou_anexo)) {

                const link = document.createElement('a');
                link.href = urlAnexo;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.textContent = 'Visualizar documento PDF';
                areaAnexo.appendChild(link);
            }
        }

        const autor = document.createElement('p');
        autor.textContent =
            'Enviada por ' + indicacao.autor +
            ' em ' + formatarData(indicacao.data_de_cadastro);

        // Endereço completo: o vereador precisa saber exatamente onde é o problema
        const partesEndereco = [indicacao.rua, indicacao.numero]
            .filter(function (parte) { return parte; })
            .join(', ');

        const localizacao = document.createElement('p');
        localizacao.textContent =
            'Local: ' +
            (partesEndereco ? partesEndereco + ' - ' : '') +
            indicacao.bairro + ', ' +
            indicacao.municipio + '/' + indicacao.estado +
            ' (CEP ' + indicacao.cep + ')';

        const apoios = document.createElement('p');
        apoios.textContent = 'Apoios dos cidadãos: ' + indicacao.total_apoios;

        // Histórico de respostas + formulário para responder (RF14)
        const tituloRespostas = document.createElement('h4');
        tituloRespostas.textContent = 'Respostas do gabinete';

        const listaRespostas = document.createElement('div');
        listaRespostas.classList.add('lista-comentarios');

        const formularioResposta = criarFormularioResposta(
            indicacao.id_indicacao,
            listaRespostas
        );

        artigo.append(
            titulo, status, descricao, areaAnexo, autor, localizacao, apoios,
            tituloRespostas, listaRespostas, formularioResposta
        );

        carregarRespostas(indicacao.id_indicacao, listaRespostas);

        return artigo;
    }

    // 4. Busca e exibe as indicações (com o filtro escolhido)
    async function carregarIndicacoes() {

        listaIndicacoes.replaceChildren();
        resumoIndicacoes.textContent = 'Carregando indicações...';

        let url = '../../backend/php/listarIndicacoesVereador.php';

        if (filtroStatus.value !== '') {
            url += '?status=' + encodeURIComponent(filtroStatus.value);
        }

        try {

            const resposta = await fetch(url);
            const dados = await resposta.json();

            // Sessão expirou no meio do caminho
            if (resposta.status === 401) {
                window.location.replace('loginVereador.html');
                return;
            }

            if (!resposta.ok || dados.sucesso !== true) {
                throw new Error(dados.mensagem || 'Erro ao carregar indicações.');
            }

            const total = dados.indicacoes.length;

            if (total === 0) {
                resumoIndicacoes.textContent = filtroStatus.value === ''
                    ? 'Nenhuma indicação foi encaminhada a você até agora.'
                    : 'Nenhuma indicação com este status.';
                return;
            }

            resumoIndicacoes.textContent = total === 1
                ? '1 indicação encontrada.'
                : total + ' indicações encontradas.';

            dados.indicacoes.forEach(function (indicacao) {
                listaIndicacoes.appendChild(criarIndicacao(indicacao));
            });

        } catch (erro) {
            console.error('Erro ao carregar indicações:', erro);
            resumoIndicacoes.textContent = 'Não foi possível carregar as indicações.';
        }
    }

    // O filtro atualiza a lista assim que o status é trocado
    filtroStatus.addEventListener('change', carregarIndicacoes);

    // 5. Encerra a sessão
    botaoSair.addEventListener('click', async function () {

        try {
            await fetch('../../backend/php/logoutVereador.php', {
                method: 'POST'
            });
        } catch (erro) {
            console.error('Erro ao sair:', erro);
        }

        window.location.replace('loginVereador.html');
    });

    // Ordem: confirma o login antes de buscar qualquer dado
    async function iniciar() {
        const logado = await verificarSessaoVereador();

        if (logado) {
            await carregarStatus();
            carregarIndicacoes();
        }
    }

    iniciar();
});
