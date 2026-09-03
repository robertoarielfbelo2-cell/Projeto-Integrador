document.getElementById("formCadastro").addEventListener("submit", function(event) {
    event.preventDefault();

    const dados = {
        nome: document.getElementById("nome").value,
        email: document.getElementById("email").value,
        telefone: document.getElementById("telefone").value,
        data_nascimento: document.getElementById("data_nascimento").value,
        senha: document.getElementById("senha").value,
        cpf: document.getElementById("cpf").value
    };

    fetch("../../database/cadastrar.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(dados)
    })
    .then(async response => {
        const texto = await response.text();
        try {
            const json = JSON.parse(texto);
            document.getElementById("mensagem").innerText = json.mensagem;
        } catch (e) {
            console.error("Resposta não é um JSON válido:", texto);
            document.getElementById("mensagem").innerText = "Erro no servidor.";
        }
    })
    .catch(error => {
        console.error("Erro na requisição:", error);
        document.getElementById("mensagem").innerText = "Erro ao conectar.";
    });
});