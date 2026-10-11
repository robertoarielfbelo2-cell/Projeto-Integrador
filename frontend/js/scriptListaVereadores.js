function criarFoto(vereador) {
    if (!vereador.foto) {
        const div = document.createElement('div');
        div.className = 'foto-vereador foto-vazia';
        div.textContent = 'Sem foto';
        return div;
    }

    const img = document.createElement('img');
    img.className = 'foto-vereador';
    img.alt = 'Foto de ' + vereador.nome_parlamentar;

    img.onerror = function () {
        const div = document.createElement('div');
        div.className = 'foto-vereador foto-vazia';
        div.textContent = 'Sem foto';

        img.replaceWith(div);
    };

    img.src = vereador.foto;

    return img;
}

async function carregarVereadores() {
    const lista = document.getElementById('listaVereadores');
    const mensagem = document.getElementById('mensagem');

    lista.replaceChildren();
    mensagem.textContent = 'Carregando vereadores...';

    try {
        const response = await fetch(
            '../../backend/php/listarVereadores.php'
        );

        if (!response.ok) {
            throw new Error('Erro HTTP: ' + response.status);
        }

        const vereadores = await response.json();

        if (!Array.isArray(vereadores)) {
            throw new Error('Resposta inválida do servidor.');
        }

        mensagem.textContent = '';

        if (vereadores.length === 0) {
            mensagem.textContent = 'Nenhum vereador cadastrado.';
            return;
        }

        vereadores.forEach(vereador => {
            const article = document.createElement('article');
            article.className = 'card-vereador';

            const nome = document.createElement('h3');
            nome.textContent = vereador.nome_parlamentar;

            const partido = document.createElement('p');
            partido.className = 'partido';
            partido.textContent = 'Partido: ' +
                (vereador.sigla || 'Não informado');

            const descricao = document.createElement('p');
            descricao.className = 'descricao';
            descricao.textContent = vereador.descricao ||
                'Sem descrição cadastrada.';

            article.append(
                criarFoto(vereador),
                nome,
                partido,
                descricao
            );

            lista.appendChild(article);
        });

    } catch (error) {
        console.error('Erro ao carregar vereadores:', error);
        mensagem.textContent = 'Não foi possível carregar os vereadores.';
    }
}

document.addEventListener('DOMContentLoaded', carregarVereadores);