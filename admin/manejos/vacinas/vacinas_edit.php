<?php
include(__DIR__ . "/../../../auth/auth.php");
include_once(__DIR__ . "/../../../include/funcoes.php");

$id = $_GET['id'];
$sql = "SELECT * FROM vacinas WHERE id=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];

    $stmt = $conn->prepare("UPDATE vacinas SET nome=?, descricao=? WHERE id=?");
    if ($stmt === false) {
        mostrarMsg("Erro ao preparar declaração para edição da vacina '" . $nome . "': " . $conn->error, 'erro', 'vacinas_select.php');
        exit;
    }
    $stmt->bind_param("ssi", $nome, $descricao, $id);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao editar vacina '" . $nome . "': " . $stmt->error, 'erro', 'vacinas_select.php');
        exit;
    }
    $stmt->close();

    // Registro no log usando tipo_acao_id
    $usuario_id = intval($_SESSION['usuario_id']);
    $tabela = 'vacinas';
    $tipo_acao_id = null;
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
    if ($stmt_tipo === false) {
        mostrarMsg("Erro ao preparar consulta de tipo de ação para edição da vacina '" . $nome . "': " . $conn->error, 'atencao', 'vacinas.php');
        exit;
    }
    $nome_acao = 'alteracao';
    $stmt_tipo->bind_param("s", $nome_acao);
    $stmt_tipo->execute();
    $stmt_tipo->bind_result($tipo_acao_id);
    $stmt_tipo->fetch();
    $stmt_tipo->close();

    $stmt_log = $conn->prepare("INSERT INTO logs (usuario_id, tabela, tipo_acao_id, data_acao) VALUES (?, ?, ?, NOW())");
    if ($stmt_log === false) {
        mostrarMsg("Erro ao preparar log para edição da vacina '" . $nome . "': " . $conn->error, 'atencao', 'vacinas_select.php');
        exit;
    }
    $stmt_log->bind_param("isi", $usuario_id, $tabela, $tipo_acao_id);
    if (!$stmt_log->execute()) {
        mostrarMsg("Vacina '" . $nome . "' editada, mas não foi possível registrar o log.", 'atencao', 'vacinas_select.php');
        exit;
    }
    $stmt_log->close();

    $conn->close();
    mostrarMsg("Vacina '" . $nome . "' editada com sucesso!", 'acerto', 'vacinas_select.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Vacina</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <h1>Editar Vacina</h1>
    <form method="post">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo $row['nome']; ?>" required>
        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao" required><?php echo $row['descricao']; ?></textarea>
        <button type="submit">Salvar</button>
    </form>
</body>
</html>