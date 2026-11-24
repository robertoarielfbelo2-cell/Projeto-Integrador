

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[role="tablist"]').forEach(function (lista) {

        const abas = Array.from(lista.querySelectorAll('[role="tab"]'));

        function abrir(abaEscolhida, mudarEndereco) {

            abas.forEach(function (aba) {
                const selecionada = aba === abaEscolhida;
                const painel = document.getElementById(aba.getAttribute('aria-controls'));

                aba.setAttribute('aria-selected', selecionada ? 'true' : 'false');
                // Só a aba aberta recebe o foco com Tab; as outras com as setas
                aba.tabIndex = selecionada ? 0 : -1;

                if (painel) {
                    painel.hidden = !selecionada;

                    if (selecionada) {
                        painel.dispatchEvent(new CustomEvent('abaAberta'));
                    }
                }
            });

            if (mudarEndereco && abaEscolhida.dataset.hash) {
                history.replaceState(null, '', '#' + abaEscolhida.dataset.hash);
            }
        }

        abas.forEach(function (aba, indice) {

            aba.addEventListener('click', function () {
                abrir(aba, true);
            });

            aba.addEventListener('keydown', function (evento) {
                let destino = null;

                if (evento.key === 'ArrowRight') destino = abas[(indice + 1) % abas.length];
                if (evento.key === 'ArrowLeft') destino = abas[(indice - 1 + abas.length) % abas.length];
                if (evento.key === 'Home') destino = abas[0];
                if (evento.key === 'End') destino = abas[abas.length - 1];

                if (destino) {
                    evento.preventDefault();
                    destino.focus();
                    abrir(destino, true);
                }
            });
        });

        function abaDoEndereco() {
            const hash = window.location.hash.replace('#', '');
            return abas.find(function (aba) { return aba.dataset.hash === hash; });
        }

        const marcada = abas.find(function (aba) { return aba.getAttribute('aria-selected') === 'true'; });
        abrir(abaDoEndereco() || marcada || abas[0], false);

        // Um link para "#projetos" dentro da própria página também troca de aba
        window.addEventListener('hashchange', function () {
            const aba = abaDoEndereco();

            if (aba) {
                abrir(aba, false);
            }
        });
    });
});
