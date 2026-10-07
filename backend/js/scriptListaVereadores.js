function criarFoto(v) {
    const img = document.createElement('img');
    img.alt = 'Foto de ' + v.nome;
    img.className = 'foto-vereador';

    // Se a foto não existir ou a URL falhar, mostra um marcador no lugar
    const marcador = () => {
        const div = document.createElement('div');
        div.className = 'foto-vereador foto-vazia';
        div.textContent = 'Sem foto';
        img.replaceWith(div);
    };

    if (!v.foto) {
        // img ainda não está no DOM, então devolve o marcador direto
        const div = document.createElement('div');
        div.className = 'foto-vereador foto-vazia';
        div.textContent = 'Sem foto';
        return div;
    }

    img.onerror = marcador;
    img.src = v.foto;
    return img;
}

function carregarVereadores() {
    fetch('../../backend/php/listarVereadores.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta da rede: ' + response.statusText);
            }
            return response.json();
        })
        .then(vereadores => {
            const lista = document.getElementById('listaVereadores');
            lista.innerHTML = '';

            if (vereadores.length === 0) {
                document.getElementById('mensagem').textContent = 'Nenhum vereador cadastrado.';
                return;
            }

            vereadores.forEach(v => {
                const article = document.createElement('article');
                article.className = 'card-vereador';

                const nome = document.createElement('h2');
                nome.textContent = v.nome;

                const partido = document.createElement('p');
                partido.className = 'partido';
                partido.textContent = 'Partido: ' + v.sigla;

                const descricao = document.createElement('p');
                descricao.className = 'descricao';
                descricao.textContent = v.descricao || 'Sem descrição cadastrada.';

                article.append(criarFoto(v), nome, partido, descricao);
                lista.appendChild(article);
            });
        })
        .catch(error => {
            console.error('Erro ao carregar vereadores:', error);
            document.getElementById('mensagem').textContent = 'Erro ao carregar vereadores.';
        });
}

document.addEventListener('DOMContentLoaded', carregarVereadores);
