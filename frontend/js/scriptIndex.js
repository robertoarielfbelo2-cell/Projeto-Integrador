document.addEventListener('DOMContentLoaded', function () {

    // Elementos da página
    const listaIndicacoes = document.getElementById('listaIndicacoes');
    const mensagem = document.getElementById('mensagem');
    const formFiltro = document.getElementById('formFiltro');
    const novaIndicacao = document.getElementById('novaIndicacao');

    let cidadaoLogado = false;

    // 1. Verifica se quem está logado é um cidadão (só ele apoia, comenta e cria indicações).
    // O cabeçalho (nome, Sair, Entrar/Cadastrar-se) é montado pelo scriptCabecalho.js,
    // que já consultou o perfil: aqui só reaproveitamos a resposta.
    async function verificarSessao() {
        const dados = await window.perfilUsuario;
        cidadaoLogado = dados.perfil === 'cidadao';
    }

    // 2. Busca os comentários de uma indicação
    async function carregarComentarios(idIndicacao, listaComentarios) {

        listaComentarios.textContent = 'Carregando comentários...';

        try {
            const url =
                '../../backend/php/listarComentarios.php?id_indicacao=' +
                encodeURIComponent(idIndicacao);

            const resposta = await fetch(url);

            if (!resposta.ok) {
                throw new Error('Erro ao buscar comentários.');
            }

            const comentarios = await resposta.json();

            if (!Array.isArray(comentarios)) {
                throw new Error('Resposta inválida.');
            }

            listaComentarios.replaceChildren();

            if (comentarios.length === 0) {
                listaComentarios.textContent =
                    'Nenhum comentário ainda. Seja o primeiro!';
                return;
            }

            comentarios.forEach(function (comentario) {

                const divComentario = document.createElement('div');
                divComentario.classList.add('comentario');

                const autorComentario = document.createElement('strong');
                autorComentario.textContent = comentario.autor;

                const textoComentario = document.createElement('p');
                textoComentario.textContent = comentario.texto;

                const dataComentario = document.createElement('small');
                dataComentario.textContent =
                    comentario.data_comentario;

                divComentario.appendChild(autorComentario);
                divComentario.appendChild(textoComentario);
                divComentario.appendChild(dataComentario);

                listaComentarios.appendChild(divComentario);
            });

        } catch (erro) {
            console.error('Erro ao carregar comentários:', erro);

            listaComentarios.textContent =
                'Não foi possível carregar os comentários.';
        }
    }

    // 3. Cria o HTML de cada indicação
    // Converte "2026-10-16 14:30:00" em "16/10/2026 14:30"
    function formatarDataHora(dataBanco) {
        const data = new Date(dataBanco.replace(' ', 'T'));

        if (isNaN(data)) {
            return dataBanco;
        }

        return data.toLocaleDateString('pt-BR') + ' ' +
            data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    }

    // Busca as respostas do vereador a uma indicação (RF15)
    async function carregarRespostasVereador(idIndicacao, listaRespostas) {

        listaRespostas.textContent = 'Carregando respostas...';

        try {

            const resposta = await fetch(
                '../../backend/php/listarRespostas.php?id_indicacao=' +
                encodeURIComponent(idIndicacao)
            );

            const dados = await resposta.json();

            if (!resposta.ok || dados.sucesso !== true) {
                throw new Error(dados.mensagem || 'Erro ao buscar respostas.');
            }

            listaRespostas.replaceChildren();

            if (dados.respostas.length === 0) {
                listaRespostas.textContent = 'O vereador ainda não respondeu esta indicação.';
                return;
            }

            dados.respostas.forEach(function (item) {

                const divResposta = document.createElement('div');
                divResposta.classList.add('comentario', 'resposta-vereador');

                const autorResposta = document.createElement('strong');
                autorResposta.textContent = item.vereador;

                const textoResposta = document.createElement('p');
                textoResposta.textContent = item.texto;

                const dataResposta = document.createElement('small');
                dataResposta.textContent = formatarDataHora(item.data_resposta);

                divResposta.append(autorResposta, textoResposta, dataResposta);
                listaRespostas.appendChild(divResposta);
            });

        } catch (erro) {
            console.error('Erro ao carregar respostas do vereador:', erro);
            listaRespostas.textContent = 'Não foi possível carregar as respostas do vereador.';
        }
    }

    function criarIndicacao(indicacao) {

        const artigo = document.createElement('article');
        artigo.classList.add('indicacao');

        const titulo = document.createElement('h3');
        titulo.textContent = indicacao.titulo;

        const descricao = document.createElement('p');
        descricao.textContent = indicacao.descricao;

        // Exibe o anexo da indicação, caso exista
        const areaAnexo = document.createElement('div');
        areaAnexo.classList.add('anexo-indicacao');

        if (indicacao.foto_ou_anexo) {

            const caminhoAnexo = indicacao.foto_ou_anexo;
            const urlAnexo = '../../' + caminhoAnexo;

            if (/\.(jpg|jpeg|png|webp)$/i.test(caminhoAnexo)) {

                // Se for imagem, exibe na página
                const imagem = document.createElement('img');

                imagem.src = urlAnexo;
                imagem.alt = 'Imagem anexada à indicação';
                imagem.loading = 'lazy';

                areaAnexo.appendChild(imagem);

            } else if (/\.pdf$/i.test(caminhoAnexo)) {

                // Se for PDF, exibe um link
                const link = document.createElement('a');

                link.href = urlAnexo;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.textContent = 'Visualizar documento PDF';

                areaAnexo.appendChild(link);
            }
        }

        const status = document.createElement('p');
        status.textContent = 'Status: ' + indicacao.status;

        const localizacao = document.createElement('p');
        localizacao.textContent =
            'Localização: ' +
            indicacao.bairro + ' - ' +
            indicacao.municipio;

        const autor = document.createElement('p');
        autor.textContent = 'Enviado por: ' + indicacao.autor;

        // Só mostra a linha quando a indicação foi encaminhada a um vereador
        const vereador = document.createElement('p');
        if (indicacao.vereador) {
            vereador.textContent =
                'Encaminhada para: ' + indicacao.vereador +
                ' (' + indicacao.vereador_partido + ')';
        }

        // Quantidade de apoios
        const totalApoios = document.createElement('p');
        totalApoios.textContent =
            'Apoios: ' + indicacao.total_apoios;

        // Botão de apoio
        const botaoApoiar = document.createElement('button');
        botaoApoiar.type = 'button';

        if (indicacao.ja_apoiou === true) {
            botaoApoiar.textContent = 'Apoiado';
            botaoApoiar.disabled = true;
        } else {
            botaoApoiar.textContent = 'Apoiar';
        }

        botaoApoiar.addEventListener('click', async function () {

            if (!cidadaoLogado) {
                window.location.href = 'login.html';
                return;
            }

            botaoApoiar.disabled = true;

            try {
                const resposta = await fetch(
                    '../../backend/php/apoiarIndicacao.php',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_indicacao: indicacao.id_indicacao
                        })
                    }
                );

                const resultado = await resposta.json();

                if (!resposta.ok || resultado.sucesso !== true) {
                    mensagem.textContent =
                        resultado.mensagem ||
                        'Não foi possível apoiar a indicação.';

                    if (resposta.status === 401) {
                        window.location.href = 'login.html';
                    }

                    return;
                }

                totalApoios.textContent =
                    'Apoios: ' + resultado.total_apoios;

                botaoApoiar.textContent = 'Apoiado';

                mensagem.textContent =
                    'Indicação apoiada com sucesso!';

            } catch (erro) {
                console.error('Erro ao apoiar:', erro);

                mensagem.textContent =
                    'Erro ao se comunicar com o servidor.';

            } finally {
                if (botaoApoiar.textContent !== 'Apoiado') {
                    botaoApoiar.disabled = false;
                }
            }
        });

        // 4. Cria a área de comentários
        const tituloComentarios = document.createElement('h4');
        tituloComentarios.textContent = 'Comentários';

        const listaComentarios = document.createElement('div');
        listaComentarios.classList.add('lista-comentarios');

        // Formulário para comentar
        const formComentario = document.createElement('form');
        formComentario.classList.add('form-comentario');

        const campoComentario = document.createElement('textarea');
        campoComentario.placeholder = 'Escreva seu comentário...';
        campoComentario.maxLength = 500;
        campoComentario.required = true;
        campoComentario.rows = 3;

        const botaoComentar = document.createElement('button');
        botaoComentar.type = 'submit';
        botaoComentar.textContent = 'Enviar comentário';

        formComentario.appendChild(campoComentario);
        formComentario.appendChild(botaoComentar);

        // Mensagem específica deste formulário
        const mensagemComentario = document.createElement('p');

        // Envia o comentário ao PHP
        formComentario.addEventListener('submit', async function (event) {

            event.preventDefault();

            if (!cidadaoLogado) {
                window.location.href = 'login.html';
                return;
            }

            const texto = campoComentario.value.trim();

            if (texto === '') {
                mensagemComentario.textContent =
                    'Digite um comentário antes de enviar.';
                return;
            }

            botaoComentar.disabled = true;
            mensagemComentario.textContent = 'Enviando comentário...';

            try {

                const resposta = await fetch(
                    '../../backend/php/cadastrarComentario.php',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_indicacao: indicacao.id_indicacao,
                            texto: texto
                        })
                    }
                );

                const resultado = await resposta.json();

                if (!resposta.ok || resultado.sucesso !== true) {

                    mensagemComentario.textContent =
                        resultado.mensagem ||
                        'Não foi possível enviar o comentário.';

                    if (resposta.status === 401) {
                        window.location.href = 'login.html';
                    }

                    return;
                }

                // Limpa o formulário
                campoComentario.value = '';

                mensagemComentario.textContent =
                    'Comentário enviado com sucesso!';

                // Atualiza a lista sem recarregar a página
                await carregarComentarios(
                    indicacao.id_indicacao,
                    listaComentarios
                );

            } catch (erro) {

                console.error('Erro ao comentar:', erro);

                mensagemComentario.textContent =
                    'Erro ao se comunicar com o servidor.';

            } finally {
                botaoComentar.disabled = false;
            }
        });

        // Monta a indicação completa
        artigo.appendChild(titulo);
        artigo.appendChild(descricao);
        artigo.appendChild(areaAnexo);
        artigo.appendChild(status);
        artigo.appendChild(localizacao);
        artigo.appendChild(autor);
        if (indicacao.vereador) {
            artigo.appendChild(vereador);
        }
        artigo.appendChild(totalApoios);
        artigo.appendChild(botaoApoiar);

        // Respostas do vereador: só existem quando a indicação foi encaminhada a um (RF15)
        if (indicacao.vereador) {

            const tituloRespostas = document.createElement('h4');
            tituloRespostas.textContent = 'Respostas do vereador';

            const listaRespostas = document.createElement('div');
            listaRespostas.classList.add('lista-comentarios');

            artigo.appendChild(tituloRespostas);
            artigo.appendChild(listaRespostas);

            carregarRespostasVereador(indicacao.id_indicacao, listaRespostas);
        }

        artigo.appendChild(tituloComentarios);
        artigo.appendChild(listaComentarios);
        artigo.appendChild(formComentario);
        artigo.appendChild(mensagemComentario);

        // Busca os comentários desta indicação
        carregarComentarios(
            indicacao.id_indicacao,
            listaComentarios
        );

        return artigo;
    }

    // 5. Busca as indicações no PHP
    async function carregarIndicacoes() {

        mensagem.textContent = 'Carregando indicações...';
        listaIndicacoes.replaceChildren();

        const status = document.getElementById('filtroStatus').value;
        const cep = document.getElementById('filtroCep').value.replace(/\D/g, '');
        const bairro = document.getElementById('filtroBairro').value.trim();
        const municipio = document.getElementById('filtroMunicipio').value.trim();

        const parametros = new URLSearchParams();

        if (status !== '') {
            parametros.append('status', status);
        }

        if (cep !== '') {
            parametros.append('cep', cep);
        }

        if (bairro !== '') {
            parametros.append('bairro', bairro);
        }

        if (municipio !== '') {
            parametros.append('municipio', municipio);
        }

        const url =
            '../../backend/php/listarIndicacoes.php?' +
            parametros.toString();

        try {
            const resposta = await fetch(url);

            if (!resposta.ok) {
                throw new Error('Erro ao buscar indicações.');
            }

            const indicacoes = await resposta.json();

            if (!Array.isArray(indicacoes)) {
                throw new Error('Resposta inválida do servidor.');
            }

            listaIndicacoes.replaceChildren();

            if (indicacoes.length === 0) {
                mensagem.textContent = 'Nenhuma indicação encontrada.';
                return;
            }

            indicacoes.forEach(function (indicacao) {
                listaIndicacoes.appendChild(criarIndicacao(indicacao));
            });

            mensagem.textContent = '';

        } catch (erro) {
            console.error('Erro ao carregar indicações:', erro);

            mensagem.textContent =
                'Não foi possível carregar as indicações.';
        }
    }

    // 6. Aplica os filtros
    formFiltro.addEventListener('submit', function (event) {
        event.preventDefault();
        carregarIndicacoes();
    });

    // 7. Controla acesso à criação de indicações
    novaIndicacao.addEventListener('click', function (event) {
        if (!cidadaoLogado) {
            event.preventDefault();
            window.location.href = 'login.html';
        }
    });

    // 8. Inicialização (o botão Sair fica no cabeçalho, no scriptCabecalho.js)
    verificarSessao();
    carregarIndicacoes();

});