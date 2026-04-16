<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form id="credit-card-form">
        <input type="text" placeholder="Informe a chave pública" id="public" style="width: 70%;" required>
        <button type="submit">Gerar</button>
    </form>
    <p id="encripted"></p>
    <script src="https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro.min.js"></script>
    <script>
        let form = document.getElementById("credit-card-form");

        form.addEventListener("submit", (e) => {
            e.preventDefault();

            var card = PagSeguro.encryptCard({
            publicKey: document.getElementById('public').value,
            holder: "JOAQUIM S SILVA",
            number: "5113481063977289",
            expMonth: "04",
            expYear: "2024",
            securityCode: "846"
        });

        var encrypted = card.encryptedCard;
        var text = document.createTextNode(encrypted);
        var paragraph = document.getElementById("encripted");
        paragraph.appendChild(text)
        console.log(encrypted)
        });
    </script>
</body>
</html>
