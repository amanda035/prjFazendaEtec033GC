<?php
session_start();
include '../../../database/conexao.php';

if (isset($_GET['id'])) {
    $usuario_id = intval($_SESSION['usuario_id']);
    $id = intval($_GET['id']);

    if (!isset($conn) || !$conn) {
        mostrarMsg("Erro de conexão ao excluir alimento ID: " . $id, 'erro', 'alimentos.php');
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM alimentos WHERE id = ?");
    if ($stmt === false) {
        mostrarMsg("Erro na preparação da declaração para exclusão do alimento ID: " . $id . ". " . $conn->error, 'erro', 'alimentos.php');
        exit;
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao excluir alimento ID: " . $id . ". " . $stmt->error, 'erro', 'alimentos.php');
        exit;
    }

    // Registro no log usando tipo_acao_id
    $tabela = 'alimentos';
    $tipo_acao_id = null;
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
    if ($stmt_tipo === false) {
        mostrarMsg("Erro ao preparar consulta de tipo de ação para exclusão do alimento ID: " . $id . ". " . $conn->error, 'atencao', 'alimentos.php');
        exit;
    }
    $nome_acao = 'exclusao';
    $stmt_tipo->bind_param("s", $nome_acao);
    $stmt_tipo->execute();
    $stmt_tipo->bind_result($tipo_acao_id);
    $stmt_tipo->fetch();
    $stmt_tipo->close();

    $stmt_log = $conn->prepare("INSERT INTO logs (usuario_id, tabela, tipo_acao_id, data_acao) VALUES (?, ?, ?, NOW())");
    if ($stmt_log === false) {
        mostrarMsg("Erro na preparação da declaração de log para exclusão do alimento ID: " . $id . ". " . $conn->error, 'atencao', 'alimentos.php');
        exit;
    }
    $stmt_log->bind_param("isi", $usuario_id, $tabela, $tipo_acao_id);
    if (!$stmt_log->execute()) {
        mostrarMsg("Alimento ID: " . $id . " excluído, mas não foi possível registrar o log.", 'atencao', 'alimentos.php');
        exit;
    }

    $stmt->close();
    $stmt_log->close();
    $conn->close();

    mostrarMsg("Alimento ID: " . $id . " excluído com sucesso!", 'acerto', 'alimentos.php');
    exit;
}
?>
