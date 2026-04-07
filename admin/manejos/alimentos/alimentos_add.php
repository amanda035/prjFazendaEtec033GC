<?php
include(__DIR__ . "/../../../auth/auth.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $tipo_alimento = $_POST['tipo_alimento'];

    $stmt = $conn->prepare("INSERT INTO alimentos (nome, descricao, tipo_alimento) VALUES (?, ?, ?)");
    if ($stmt === false) {
        mostrarMsg("Erro ao preparar declaração para cadastro do alimento '" . $nome . "': " . $conn->error, 'erro', 'alimentos.php');
        exit;
    }
    $stmt->bind_param("sss", $nome, $descricao, $tipo_alimento);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao cadastrar alimento '" . $nome . "': " . $stmt->error, 'erro', 'alimentos.php');
        exit;
    }
    $alimento_id = $stmt->insert_id;
    $stmt->close();

    // Registro no log usando tipo_acao_id
    $usuario_id = intval($_SESSION['usuario_id']);
    $tabela = 'alimentos';
    $tipo_acao_id = null;
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
    if ($stmt_tipo === false) {
        mostrarMsg("Erro ao preparar consulta de tipo de ação para cadastro do alimento '" . $nome . "': " . $conn->error, 'atencao', 'alimentos.php');
        exit;
    }
    $nome_acao = 'inclusao';
    $stmt_tipo->bind_param("s", $nome_acao);
    $stmt_tipo->execute();
    $stmt_tipo->bind_result($tipo_acao_id);
    $stmt_tipo->fetch();
    $stmt_tipo->close();

    $stmt_log = $conn->prepare("INSERT INTO logs (usuario_id, tabela, tipo_acao_id, data_acao) VALUES (?, ?, ?, NOW())");
    if ($stmt_log === false) {
        mostrarMsg("Erro ao preparar log para cadastro do alimento '" . $nome . "': " . $conn->error, 'atencao', 'alimentos.php');
        exit;
    }
    $stmt_log->bind_param("isi", $usuario_id, $tabela, $tipo_acao_id);
    if (!$stmt_log->execute()) {
        mostrarMsg("Alimento '" . $nome . "' cadastrado, mas não foi possível registrar o log.", 'atencao', 'alimentos.php');
        exit;
    }
    $stmt_log->close();

    $conn->close();
    mostrarMsg("Alimento '" . $nome . "' cadastrado com sucesso!", 'acerto', 'alimentos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Alimento</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <h1>Adicionar Alimento</h1>
    <form method="post">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>
        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao" required></textarea>
        <label for="tipo_alimento">Tipo Alimento:</label>
        <input type="text" id="tipo_alimento" name="tipo_alimento" required>
        <button type="submit">Adicionar</button>
    </form>
</body>
</html>