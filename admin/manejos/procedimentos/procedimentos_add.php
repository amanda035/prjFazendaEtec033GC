<?php
include(__DIR__ . "/../../../auth/auth.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];

    $stmt = $conn->prepare("INSERT INTO procedimentos (nome, descricao) VALUES (?, ?)");
    if ($stmt === false) {
        mostrarMsg("Erro ao preparar declaração para cadastro do procedimento '" . $nome . "': " . $conn->error, 'erro', '../../public/procedimentos.php');
        exit;
    }
    $stmt->bind_param("ss", $nome, $descricao);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao cadastrar procedimento '" . $nome . "': " . $stmt->error, 'erro', '../../public/procedimentos.php');
        exit;
    }
    $procedimento_id = $stmt->insert_id;
    $stmt->close();

    // Registro no log usando tipo_acao_id
    $usuario_id = intval($_SESSION['usuario_id']);
    $tabela = 'procedimentos';
    $tipo_acao_id = null;
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
    if ($stmt_tipo === false) {
        mostrarMsg("Erro ao preparar consulta de tipo de ação para cadastro do procedimento '" . $nome . "': " . $conn->error, 'atencao', '../../public/procedimentos.php');
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
        mostrarMsg("Erro ao preparar log para cadastro do procedimento '" . $nome . "': " . $conn->error, 'atencao', '../../public/procedimentos.php');
        exit;
    }
    $stmt_log->bind_param("isi", $usuario_id, $tabela, $tipo_acao_id);
    if (!$stmt_log->execute()) {
        mostrarMsg("Procedimento '" . $nome . "' cadastrado, mas não foi possível registrar o log.", 'atencao', '../../public/procedimentos.php');
        exit;
    }
    $stmt_log->close();

    $conn->close();
    mostrarMsg("Procedimento '" . $nome . "' cadastrado com sucesso!", 'acerto', '../../public/procedimentos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Procedimento</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <h1>Adicionar Procedimento</h1>
    <form method="post">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>
        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao" required></textarea>
        <button type="submit">Adicionar</button>
    </form>
</body>
</html>