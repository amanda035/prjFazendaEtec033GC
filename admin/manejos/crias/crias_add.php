<?php
include(__DIR__ . "/../../../auth/auth.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $parto_id = $_POST['parto_id'];
    $nome = $_POST['nome'];
    $peso_nascimento = $_POST['peso_nascimento'];
    $data_nascimento = $_POST['data_nascimento'];

    $stmt = $conn->prepare("INSERT INTO crias (parto_id, nome, peso_nascimento, data_nascimento) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        mostrarMsg("Erro ao preparar declaração para cadastro da cria '" . $nome . "': " . $conn->error, 'erro', 'crias.php');
        exit;
    }
    $stmt->bind_param("isds", $parto_id, $nome, $peso_nascimento, $data_nascimento);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao cadastrar cria '" . $nome . "': " . $stmt->error, 'erro', 'crias.php');
        exit;
    }
    $cria_id = $stmt->insert_id;
    $stmt->close();

    // Registro no log usando tipo_acao_id
    $usuario_id = intval($_SESSION['usuario_id']);
    $tabela = 'crias';
    $tipo_acao_id = null;
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
    if ($stmt_tipo === false) {
        mostrarMsg("Erro ao preparar consulta de tipo de ação para cadastro da cria '" . $nome . "': " . $conn->error, 'atencao', 'crias.php');
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
        mostrarMsg("Erro ao preparar log para cadastro da cria '" . $nome . "': " . $conn->error, 'atencao', 'crias.php');
        exit;
    }
    $stmt_log->bind_param("isi", $usuario_id, $tabela, $tipo_acao_id);
    if (!$stmt_log->execute()) {
        mostrarMsg("Cria '" . $nome . "' cadastrada, mas não foi possível registrar o log.", 'atencao', 'crias.php');
        exit;
    }
    $stmt_log->close();

    $conn->close();
    mostrarMsg("Cria '" . $nome . "' cadastrada com sucesso!", 'acerto', 'crias.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Cria</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <h1>Adicionar Cria</h1>
    <form method="post">
        <label for="parto_id">ID Parto:</label>
        <input type="number" id="parto_id" name="parto_id" required>
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>
        <label for="peso_nascimento">Peso ao Nascimento:</label>
        <input type="number" step="0.01" id="peso_nascimento" name="peso_nascimento" required>
        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" name="data_nascimento" required>
        <button type="submit">Adicionar</button>
    </form>
</body>
</html>