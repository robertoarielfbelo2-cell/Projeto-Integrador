
window.perfilUsuario = fetch('../../backend/php/verificarPerfil.php')
    .then(function (resposta) { return resposta.json(); })
    .catch(function (erro) {
        console.error('Erro ao verificar o perfil:', erro);
        return { perfil: null, nome: '' };
    });

(function () {


    const menuAdmin = [
        { texto: 'Indicações recebidas', pagina: 'painelAdmin.html' },
        { texto: 'Gerenciar vereadores e partidos', pagina: 'gerenciarCadastros.html' },
        { texto: 'Cadastrar vereador', pagina: 'addVereador.html' },
        { texto: 'Cadastrar partido', pagina: 'addPartido.html' },
        { texto: 'Acesso dos vereadores', pagina: 'acessoVereador.html' }
    ];

   
    const saida = {
        cidadao: { php: 'logout.php', destino: 'index.html' },
        administrador: { php: 'logoutAdmin.php', destino: 'loginAdmin.html' },
        vereador: { php: 'logoutVereador.php', destino: 'loginVereador.html' }
    };

    const painel = {
        administrador: { texto: 'Painel administrativo', pagina: 'painelAdmin.html' },
        vereador: { texto: 'Painel do vereador', pagina: 'painelVereador.html' }
    };

    function paginaAtual() {
        return window.location.pathname.split('/').pop() || 'index.html';
    }

    function criarLink(texto, pagina, atual) {
        const link = document.createElement('a');
        link.href = pagina;
        link.textContent = texto;

        if (atual) {
            link.setAttribute('aria-current', 'page');
        }

        return link;
    }


    function montarConta(dados) {
        const cabecalho = document.querySelector('body > header, body > div > header');

        if (!cabecalho) {
            return;
        }

        let conta = cabecalho.querySelector('.conta');

        if (!conta) {
            conta = document.createElement('div');
            conta.classList.add('conta');
            cabecalho.appendChild(conta);
        }

        conta.replaceChildren();

        const pagina = paginaAtual();

 
        if (!dados.perfil) {
            conta.append(
                criarLink('Entrar', 'login.html', pagina === 'login.html'),
                criarLink('Cadastrar-se', 'cadastro.html', pagina === 'cadastro.html')
            );
            return;
        }

        const nome = document.createElement('span');
        nome.classList.add('nome-usuario');
        nome.textContent = dados.nome;
        conta.appendChild(nome);

        const atalho = painel[dados.perfil];
        const dentroDaAreaAdmin = document.body.dataset.area === 'admin';

        if (atalho && !dentroDaAreaAdmin && pagina !== atalho.pagina) {
            conta.appendChild(criarLink(atalho.texto, atalho.pagina, false));
        }

        const botaoSair = document.createElement('button');
        botaoSair.type = 'button';
        botaoSair.textContent = 'Sair';

        botaoSair.addEventListener('click', async function () {
            const regra = saida[dados.perfil];
            botaoSair.disabled = true;

            try {
                await fetch('../../backend/php/' + regra.php, { method: 'POST' });
            } catch (erro) {
                console.error('Erro ao sair:', erro);
            }

            window.location.replace(regra.destino);
        });

        conta.appendChild(botaoSair);
    }

    
    function montarMenuAdmin() {
        const cabecalho = document.querySelector('body > header');

        if (!cabecalho || document.querySelector('.menu-admin')) {
            return;
        }

        const pagina = paginaAtual();


        const editando = new URLSearchParams(window.location.search).has('id');
        const destaque = editando && (pagina === 'addVereador.html' || pagina === 'addPartido.html')
            ? 'gerenciarCadastros.html'
            : pagina;

        const menu = document.createElement('nav');
        menu.classList.add('menu-admin');
        menu.setAttribute('aria-label', 'Menu do administrador');

        const lista = document.createElement('ul');

        menuAdmin.forEach(function (item) {
            const li = document.createElement('li');
            li.appendChild(criarLink(item.texto, item.pagina, item.pagina === destaque));
            lista.appendChild(li);
        });

        menu.appendChild(lista);
        cabecalho.after(menu);
    }

    document.addEventListener('DOMContentLoaded', async function () {
        const dados = await window.perfilUsuario;
        const areaAdmin = document.body.dataset.area === 'admin';

        // Página de administrador sem administrador logado: vai para o login
        if (areaAdmin && dados.perfil !== 'administrador') {
            window.location.replace('loginAdmin.html');
            return;
        }

        montarConta(dados);

        if (areaAdmin) {
            montarMenuAdmin();
        }
    });
})();
