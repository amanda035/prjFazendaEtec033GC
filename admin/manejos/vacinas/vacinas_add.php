<?php
include(__DIR__ . "/../../../auth/auth.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];

    $stmt = $conn->prepare("INSERT INTO vacinas (nome, descricao) VALUES (?, ?)");
    if ($stmt === false) {
        mostrarMsg("Erro ao preparar declaração para cadastro da vacina '" . $nome . "': " . $conn->error, 'erro', 'vacinas.php');
        exit;
    }
    $stmt->bind_param("ss", $nome, $descricao);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao cadastrar vacina '" . $nome . "': " . $stmt->error, 'erro', 'vacinas.php');
        exit;
    }
    $vacina_id = $stmt->insert_id;
    $stmt->close();

    // Registro no log usando tipo_acao_id
    $usuario_id = intval($_SESSION['usuario_id']);
    $tabela = 'vacinas';
    $tipo_acao_id = null;
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
    if ($stmt_tipo === false) {
        mostrarMsg("Erro ao preparar consulta de tipo de ação para cadastro da vacina '" . $nome . "': " . $conn->error, 'atencao', 'vacinas.php');
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
        mostrarMsg("Erro ao preparar log para cadastro da vacina '" . $nome . "': " . $conn->error, 'atencao', 'vacinas.php');
        exit;
    }
    $stmt_log->bind_param("isi", $usuario_id, $tabela, $tipo_acao_id);
    if (!$stmt_log->execute()) {
        mostrarMsg("Vacina '" . $nome . "' cadastrada, mas não foi possível registrar o log.", 'atencao', 'vacinas.php');
        exit;
    }
    $stmt_log->close();

    $conn->close();
    mostrarMsg("Vacina '" . $nome . "' cadastrada com sucesso!", 'acerto', 'vacinas.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
    <meta charset="UTF-8">
    <title>Adicionar Vacina</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <h1>Adicionar Vacina</h1>
    <form method="post">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>
        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao" required></textarea>
        <button type="submit">Adicionar</button>
    </form>
</body>
</html>