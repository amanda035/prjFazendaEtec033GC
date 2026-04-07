<?php
//Adicionei a opção de deletar para os procedimentos, implementei e alterei os diretórios, mas não testei as funcionalidades - Letícia
session_start();
include '../../../database/conexao.php';

if (isset($_GET['id'])) {
    $usuario_id = intval($_SESSION['usuario_id']);
    $id = intval($_GET['id']);

    if (!isset($conn) || !$conn) {
        mostrarMsg("Erro de conexão ao excluir procedimento ID: " . $id, 'erro', 'procedimentos.php');
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM procedimentos WHERE id = ?");
    if ($stmt === false) {
        mostrarMsg("Erro na preparação da declaração para exclusão do procedimento ID: " . $id . ". " . $conn->error, 'erro', 'procedimentos.php');
        exit;
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao excluir procedimento ID: " . $id . ". " . $stmt->error, 'erro', 'procedimentos.php');
        exit;
    }

    // Buscar o id do tipo de ação "exclusao"
    $tipo_acao_id = null;
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
    if ($stmt_tipo === false) {
        mostrarMsg("Erro ao preparar consulta de tipo de ação para exclusão do procedimento ID: " . $id . ". " . $conn->error, 'atencao', 'procedimentos.php');
        exit;
    }
    $nome_acao = 'exclusao';
    $stmt_tipo->bind_param("s", $nome_acao);
    if (!$stmt_tipo->execute()) {
        mostrarMsg("Erro ao buscar tipo de ação para exclusão do procedimento ID: " . $id . ". " . $stmt_tipo->error, 'atencao', 'procedimentos.php');
        exit;
    }
    $result_tipo = $stmt_tipo->get_result();
    if ($row_tipo = $result_tipo->fetch_assoc()) {
        $tipo_acao_id = $row_tipo['id'];
    } else {
        mostrarMsg("Tipo de ação 'exclusao' não encontrado para procedimento ID: " . $id . ".", 'atencao', 'procedimentos.php');
        exit;
    }
    $stmt_tipo->close();

    // Registrar log
    $tabela = 'procedimentos';
    $detalhes = "Exclusão do procedimento ID: $id";
    $stmt_log = $conn->prepare("INSERT INTO logs (usuario_id, tabela, tipo_acao_id, detalhes, data_acao) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt_log === false) {
        mostrarMsg("Erro na preparação da declaração de log para exclusão do procedimento ID: " . $id . ". " . $conn->error, 'atencao', 'procedimentos.php');
        exit;
    }
    $stmt_log->bind_param("isis", $usuario_id, $tabela, $tipo_acao_id, $detalhes);
    if (!$stmt_log->execute()) {
        mostrarMsg("Procedimento ID: " . $id . " excluído, mas não foi possível registrar o log.", 'atencao', 'procedimentos.php');
        exit;
    }

    $stmt->close();
    $stmt_log->close();
    $conn->close();

    mostrarMsg("Procedimento ID: " . $id . " excluído com sucesso!", 'acerto', 'procedimentos.php');
    exit;
} else {
    mostrarMsg("ID do procedimento para exclusão não informado.", 'atencao', 'procedimentos.php');
    exit;
}
?>
