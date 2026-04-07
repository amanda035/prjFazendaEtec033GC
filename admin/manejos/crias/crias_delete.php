<?php
include '../../../database/conexao.php';


$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!isset($conn) || !$conn) {
    mostrarMsg("Erro de conexão ao excluir cria ID: " . $id, 'erro', 'crias.php');
    exit;
}
$sql = "DELETE FROM crias WHERE id=$id";
if ($conn->query($sql) === TRUE) {
    // Registro no log usando tipo_acao_id
    $usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : null;
    $tabela = 'crias';
    $tipo_acao_id = null;
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
    if ($stmt_tipo) {
        $nome_acao = 'exclusao';
        $stmt_tipo->bind_param("s", $nome_acao);
        $stmt_tipo->execute();
        $stmt_tipo->bind_result($tipo_acao_id);
        $stmt_tipo->fetch();
        $stmt_tipo->close();
    }
    if ($tipo_acao_id) {
        $stmt_log = $conn->prepare("INSERT INTO logs (usuario_id, tabela, tipo_acao_id, data_acao) VALUES (?, ?, ?, NOW())");
        if ($stmt_log) {
            $stmt_log->bind_param("isi", $usuario_id, $tabela, $tipo_acao_id);
            $stmt_log->execute();
            $stmt_log->close();
        }
    }
    mostrarMsg("Cria ID: " . $id . " excluída com sucesso!", 'acerto', 'crias.php');
    exit;
} else {
    mostrarMsg("Erro ao excluir cria ID: " . $id . ": " . $conn->error, 'erro', 'crias.php');
    exit;
}
?>