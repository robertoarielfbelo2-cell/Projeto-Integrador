// Abas reaproveitáveis.
// Marcação esperada:
//   <div role="tablist">
//     <button role="tab" aria-controls="painel-a" data-hash="a">A</button> ...
//   </div>
//   <section id="painel-a" role="tabpanel"> ... </section>
//
// - Clicar numa aba mostra o painel dela e esconde os outros.
// - Setas esquerda/direita, Home e End trocam de aba pelo teclado.
// - A aba aberta fica no endereço (ex.: index.html#projetos): dá para
//   mandar o link de uma aba e o botão Voltar do navegador funciona.
// - Outros scripts sabem quando uma aba abriu pelo evento "abaAberta":
//     painel.addEventListener('abaAberta', ...)

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

        // Abre a aba indicada no endereço (#projetos) ou a que já vem marcada no HTML
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
