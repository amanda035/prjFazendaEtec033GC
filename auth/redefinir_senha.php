<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Inclusão dos arquivos necessários
require_once __DIR__ . '/../include/funcoes.php';
include_once __DIR__ . '/../database/conexao.php';
global $conn;

$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $senha = $_POST['senha'];
    $confirma = $_POST['confirma_senha'];

    if ($senha !== $confirma) {
        mostrarMsg('As senhas para redefinir o usuário não conferem.', 'atencao', 'redefinir_senha.php?token=' . $token);
    }
    if (!validarSenha($senha)) {
        mostrarMsg('Senha inválida para redefinir usuário. Deve conter pelo menos 6 caracteres, incluindo letras maiúsculas, minúsculas, números e símbolos.', 'atencao', 'redefinir_senha.php?token=' . $token);
    }
    if ($conn === null) {
        mostrarMsg('Erro de conexão ao redefinir senha do usuário.', 'erro', '../index.php');
    }
    $stmt = $conn->prepare('SELECT id, reset_expira FROM usuarios WHERE reset_token = ?');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $expira);
        $stmt->fetch();
        if (strtotime($expira) < time()) {
            mostrarMsg('O link de redefinição de senha do usuário expirou. Solicite novamente.', 'atencao', 'esqueci_senha.php');
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            if ($conn) {
                $stmt2 = $conn->prepare('UPDATE usuarios SET senha = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?');
                if ($stmt2) {
                    $stmt2->bind_param('si', $senha_hash, $id);
                    $stmt2->execute();
                    $stmt2->close();
                    mostrarMsg('Senha do usuário ' . (isset($usuario) ? $usuario : '') . ' redefinida com sucesso! Faça login.', 'acerto', '../index.php');
                } else {
                    mostrarMsg('Erro ao preparar atualização de senha.', 'erro', '../index.php');
                }
            } else {
                mostrarMsg('Erro de conexão ao atualizar senha.', 'erro', '../index.php');
            }
        }
    } else {
        mostrarMsg('Token inválido ou usuário ' . (isset($usuario) ? $usuario : '') . ' não encontrado.', 'erro', '../index.php');
    }
    if ($stmt) {
        $stmt->close();
    }
    if ($conn) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinir senha</title>
    <link rel="stylesheet" href="../assets/css/form.css">
</head>
<body>
    <div class="container">
        <?php include __DIR__ . '/../include/modal_msg.php'; ?>
        <h2>Redefinir senha</h2>
        <?php if ($token): ?>
        <form action="redefinir_senha.php" method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <label for="senha">Nova senha:</label>
            <input type="password" id="senha" name="senha" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,20}$">
            <label for="confirma_senha">Confirmar nova senha:</label>
            <input type="password" id="confirma_senha" name="confirma_senha" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,20}$">
            <input type="submit" value="Redefinir senha">
        </form>
        <?php else: ?>
        <p>Token de redefinição não informado ou inválido.</p>
        <a href="esqueci_senha.php">Solicitar novo link</a>
        <?php endif; ?>
        <a href="../index.php">Voltar ao login</a>
    </div>
</body>
</html>
