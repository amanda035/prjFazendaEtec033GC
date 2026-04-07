<?php
$titulo_pagina = "Bem-vindo à tela de Logs do Sistema";
include(__DIR__ . "/../auth/auth.php");


// Consulta para buscar os logs com nome do usuário e descrição da ação
$sql = "SELECT l.*, u.nome AS nome_usuario, ta.nome AS nome_acao FROM logs l 
    JOIN usuarios u ON l.usuario_id = u.id 
    JOIN tipos_acao ta ON l.tipo_acao_id = ta.id 
    ORDER BY l.data_acao DESC";

$result = $conn->query($sql);
if ($result === false) {
    die("Erro na consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Logs</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <?php include '../include/header.php'; ?>

    <div style="overflow-x: auto;">
        <table id="logsTable" role="table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Usuário</th>
                    <th scope="col">Tabela</th>
                    <th scope="col">Ação</th>
                    <th scope="col">Data da Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td><?= htmlspecialchars($log['nome_usuario']) ?></td>
                        <td><?= $log['tabela'] ?></td>
                        <td><?= isset($log['nome_acao']) ? $log['nome_acao'] : '-' ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($log['data_acao'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php
    $titulo_ajuda = "Ajuda - Tela de Logs";
    $descricao_ajuda = "Esta tela exibe os registros de ações realizadas no sistema.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Ajudar', 'descricao' => 'Abre esta tela de auxílio.'],
        ['titulo' => 'Tabela de Logs', 'descricao' => 'Mostra os registros de ações realizadas por usuários.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: Os logs são gerados automaticamente pelo sistema.";
    include '../include/modal_ajuda.php';
    ?>

</div>

<?php include '../include/footer.php'; ?>
</body>
</html>
