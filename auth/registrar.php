<?php
session_start();

// Verifica se há dados de login válidos
$usuario_logado = isset($_SESSION['nome']) && $_SESSION['nome'] !== ''
               && isset($_SESSION['email']) && $_SESSION['email'] !== '';
if ($usuario_logado) {
    $usuario = htmlspecialchars($_SESSION['nome']);
    $email = htmlspecialchars($_SESSION['email']);
} else {
    $usuario = 'sem usuário';
    $email = '';
}

include("../database/conexao.php");
include("../include/funcoes.php");

$conn = $GLOBALS['conn'];
if ($conn === null) {
    mostrarMsg("O sistema está temporariamente indisponível para registro de usuário devido a problemas de conexão com o banco de dados.", 'erro', '../index.php');
}

// Verifica se o método é POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    mostrarMsg("O método passado para o registro de usuário é inválido!", 'erro', '../index.php');
}

// Verifica campos obrigatórios
$camposObrigatorios = ["usuario", "senha", "confirma_senha", "email", "csrf"];
foreach ($camposObrigatorios as $campo) {
    if (!isset($_POST[$campo])) {
    mostrarMsg("Campo obrigatório $campo não enviado para cadastro de usuário.", 'atencao', '../public/registro.php');
    }
}

// Sanitização
$usuario = htmlspecialchars(strip_tags(trim($_POST['usuario'])));
$senha = strip_tags(trim($_POST['senha']));
$confirma_senha = strip_tags(trim($_POST["confirma_senha"]));
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$csrf = strip_tags(trim($_POST["csrf"]));

// Validações
if (!validarCSRF($csrf)) {
    mostrarMsg("Token CSRF inválido ao tentar cadastrar usuário.", 'erro', '../index.php');
} 
if (!validarUsuario($usuario)) {
    mostrarMsg("Nome de usuário $usuario inválido. Deve conter apenas letras e números, entre 3 e 20 caracteres.", 'atencao', '../public/registro.php');
}
if ($senha !== $confirma_senha) {
    mostrarMsg("As senhas não conferem.", 'atencao', '../public/registro.php');
}
if (!validarSenha($senha)) {
    mostrarMsg("Senha inválida para cadastro de usuário. Deve conter pelo menos 6 caracteres, <br>
    incluindo letras maiúsculas, minúsculas, números e símbolos.", 'atencao', '../public/registro.php');
}
if (!validarEmail($email)) {
    mostrarMsg("Email informado para cadastro de usuário é inválido.", 'atencao', '../public/registro.php');
}
if (verificarContaExiste($usuario, $email)) {
    mostrarMsg("Usuário $usuario ou email já cadastrados.", 'atencao', '../public/registro.php');
}
// Impede duplicação com usuário logado (somente se ambos os dados forem iguais)
if ($usuario_logado && $_SESSION['nivel_acesso'] != 0) {
    if ($usuario === $_SESSION['nome'] && $email === $_SESSION['email']) {
    mostrarMsg("Não é permitido cadastrar o usuário $usuario com os mesmos dados do usuário logado.", 'atencao', '../public/registro.php');
    }
}

// Criptografa a senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Verifica se já existe algum usuário no sistema
$usuarios_existem = verificarUsuariosExistentes();

// Define o nível de acesso
if (!$usuarios_existem) {
    $nivel_acesso = 0; // Primeiro usuário é administrador
} elseif (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 0 && isset($_POST['nivel_acesso'])) {
    // Administrador logado pode definir o nível de acesso
    $nivel_acesso = intval($_POST['nivel_acesso']);
    if ($nivel_acesso < 0 || $nivel_acesso > 3) {
        $nivel_acesso = 3; // Padrão: Aluno
    }
} else {
    $nivel_acesso = 3; // Padrão: Aluno
}

// Prepara e executa a inserção no banco
$stmt = $conn->prepare("INSERT INTO usuarios (nome, senha, email, nivel_acesso, data_criacao) VALUES (?, ?, ?, ?, NOW())");
if (!$stmt) {
    mostrarMsg("Erro ao preparar a query para cadastro do usuário $usuario: " . $conn->error . ".", 'erro', '../public/registro.php');
}

$stmt->bind_param("sssi", $usuario, $senha_hash, $email, $nivel_acesso);

if ($stmt->execute()) {
    $stmt->close();
    mostrarMsg("Usuário $usuario cadastrado com sucesso!", 'acerto', '../index.php');
} else {
    mostrarMsg("Erro ao cadastrar usuário $usuario: " . $stmt->error . ".", 'erro', '../public/registro.php');
}

$conn->close();
?>
