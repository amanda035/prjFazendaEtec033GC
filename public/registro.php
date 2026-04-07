<?php
// Inicialização de sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusão das funções auxiliares
require_once __DIR__ . '/../include/funcoes.php';

// Tratamento de erro de conexão
if (isset($_SESSION['erro_conexao'])) {
    mostrarMsg("Erro de conexão ao registrar usuário '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "': " . $_SESSION['erro_conexao'], 'erro', '../index.php');
    unset($_SESSION['erro_conexao']);
}

// Verificação de usuário logado e permissão para cadastro
$temUsuarios = verificarUsuariosExistentes();
if ($temUsuarios) {
    // Se já existem usuários, só permite cadastro se o usuário logado for administrador
    if (isset($_SESSION['nome']) && isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 0) {
        // Permite cadastro, mantém nível de acesso do novo usuário como padrão (Administrador)
        $_SESSION['nivel_acesso'] = 0;
    } else {
        mostrarMsg('Apenas administradores podem cadastrar novos usuários. Cadastro do usuário ' . (isset($_POST['nome']) ? $_POST['nome'] : '') . ' bloqueado.', 'atencao', '../index.php');
        // exit;
    }
} else {
    // Se não houver usuários, permite cadastro do primeiro usuário como administrador
    $_SESSION['nivel_acesso'] = 0;
}

// Geração do token CSRF
if (empty($_SESSION['csrf'])) {
    gerarCSRF();
}
$csrf_token = $_SESSION['csrf'];

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema Fazenda ETEC</title>
</head>
<body>
    <button onclick="history.back()">Voltar para index</button>

    <form action="../auth/registrar.php" method="POST">
        <h2>Cadastrar Usuário</h2>

        <!-- Token CSRF -->
        <input type="hidden" name="csrf" value="<?php echo $csrf_token; ?>">
        <!-- Campo Usuário -->
        <label for="usuario">Usuário:</label>
        <input type="text" id="usuario" name="usuario" required pattern="^[a-zA-Z0-9]{3,20}$">

        <!-- Campo Email -->
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$">

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required
            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,20}$">
        <label for="confirma_senha">Confirmar senha:</label>
        <input type="password" name="confirma_senha" id="confirma_senha" required
            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,20}$">

        <?php 
            // Se não houver usuários, não mostra o campo de nível (será definido como administrador automaticamente)
            if ($temUsuarios && isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 0): 
        ?>
                <label for="nivel_acesso">Nível de Acesso:</label>
                <select name="nivel_acesso" id="nivel_acesso">
                    <option value="0">Administrador</option>
                    <option value="1">Docente</option>
                    <option value="2">Auxiliar Docente</option>
                    <option value="3">Aluno</option>
                </select>
        <?php 
            endif;
        ?>

        <input type="submit" value="Cadastrar">
    </form>
</body>
</html>
