<?php

// Define o diretório de retorno padrão
$diretorioRetorno = '/prjFazendaEtec033/index.php';

// Inclui arquivos auxiliares 
require_once __DIR__ . '/include/funcoes.php';
require_once __DIR__ . '/include/modal_msg.php';

// Inclui a conexão com o banco de dados
require_once __DIR__ . '/database/conexao.php';

// Verifica se a sessão já foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se há mensagem de erro de conexão na sessão
if (isset($_SESSION['erro_conexao'])) {

    // Exibe a mensagem de erro e redireciona para o diretório padrão
    mostrarMsg("Erro de conexão para usuário '" . (isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '') . "': " . $_SESSION['erro_conexao'], 'erro', $diretorioRetorno);
    
    // Limpa a mensagem de erro da sessão
    unset($_SESSION['erro_conexao']);
}

// A variável $conn está disponível globalmente
$conn = $GLOBALS['conn'];

// Verifica o cookie "lembrar_token" e autentica o usuário se necessário
if (!isset($_SESSION['loggedin']) && isset($_COOKIE['lembrar_token'])) {
    $token = $_COOKIE['lembrar_token'];
    $stmt = $conn->prepare("SELECT id, nome, email, nivel_acesso FROM usuarios WHERE lembrar_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($id, $nome, $email, $nivel_acesso);
    if ($stmt->fetch()) {
        $_SESSION['usuario_id'] = $id;
        $_SESSION['nome'] = $nome;
        $_SESSION['email'] = $email;
        $_SESSION['nivel_acesso'] = $nivel_acesso;
        $_SESSION['loggedin'] = true;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Fazenda Etec</title>

    <link rel="stylesheet" href="./assets/css/form.css">
    <link rel="stylesheet" href="./assets/css/style.css">

    <!-- Link de importação da biblioteca css de autorização de cookies -->
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.css">
</head>

<body>
    <div class="container">
        <h2>Login</h2>
        <form action="./auth/entrar.php" method="POST">
            <label for="usuario">Usuário:</label>
            <input type="text" id="usuario" name="usuario" required pattern="^[a-zA-Z0-9]{3,20}$">

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,20}$">

            <div class="options">
                <label><input type="checkbox">Lembrar pra mim</label>
                <a href="./auth/esqueci_senha.php">Esqueci minha senha</a>
            </div>

            <button type="submit" class="btn" value="Submit" autofocus>Entrar</button>
            <div class="create-account">
                <a href="./public/registro.php">Não tem usuário? Cadastre-se</a>
            </div>
        </form>
    </div>
    <!-- Script de autorização de cookies -->
    <script>
        window.cookieconsent.initialise({
            "palette": {
                "popup": {
                    "background": "#f5f5f5",
                    "text": "#000000"
                },
                "button": {
                    "background": "#009900",
                    "text": "#ffffff"
                }
            },
            "content": {
                "message": "Este site usa cookies para garantir que você obtenha a melhor experiência de navegação. Desativar os cookies do site pode prejudicar a funcionalidade de alguns recursos.",
                "dismiss": "Concordar e fechar",
                "link": "Ler mais",
                // a referência abaixo precisa ser desenvolvida por isto está como um exemplo
                // "href": "https://seu-dominio-de-site.com.br/pagina-da-politica"
            }
        });
    </script>
</body>

</html>