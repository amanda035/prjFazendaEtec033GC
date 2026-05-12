<?php

$titulo_pagina = "Bem-vindo à tela de Matrizes";
$diretorioRetorno = '/prjFazendaEtec033GC/admin/manejos/matrizes/matrizes_select.php';

include_once(__DIR__ . "/../../../auth/auth.php");
include_once(__DIR__ . "/../../../database/conexao.php");
include_once(__DIR__ . "/../../../include/funcoes.php");
global $conn;

// Exclusão via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    $id = intval($_POST['excluir_id']);
    $usuario_id = intval($_SESSION['usuario_id']);
    $nome = buscarNomeMatriz($conn, $id);
    $stmt = $conn->prepare("DELETE FROM matrizes WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            registrarLog($conn, $usuario_id, 'matrizes', 'exclusao');
            mostrarMsg("Matriz '$nome' excluída com sucesso!", "acerto", $diretorioRetorno);
        } else {
            mostrarMsg("Erro ao excluir matriz '$nome'.", "erro", $diretorioRetorno);
        }
        $stmt->close();
    } else {
        mostrarMsg("Erro ao preparar exclusão da matriz '$nome'.", "erro", $diretorioRetorno);
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
// Validação básica
if (!empty($search)) {
    if (strlen($search) > 100) {
        mostrarMsg("Erro: o termo de busca '" . $search . "' é muito longo.", 'erro', $diretorioRetorno);
    }
    if (!preg_match("/^[\p{L}\p{N}\s]+$/u", $search)) {
        mostrarMsg("Erro: o termo de busca '" . $search . "' contém caracteres inválidos.", 'erro', $diretorioRetorno);
    }
}

$sql = "SELECT m.id, m.nome, r.nome AS raca, m.data_nascimento, m.data_criacao, m.data_atualizacao,
    uc.nome AS usuario_criacao, ua.nome AS usuario_atualizacao
    FROM matrizes m
    LEFT JOIN racas_suinas r ON m.raca_id = r.id
    LEFT JOIN usuarios uc ON m.usuario_criacao_id = uc.id
    LEFT JOIN usuarios ua ON m.usuario_atualizacao_id = ua.id
    WHERE m.nome LIKE ? OR r.nome LIKE ?
    ORDER BY m.id DESC";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    mostrarMsg("Erro na preparação da consulta de matrizes: " . $conn->error, 'erro', $diretorioRetorno);
}
$searchParam = "%" . $search . "%";
$stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();
if ($result === false) {
    mostrarMsg("Erro na execução da consulta de matrizes: " . $stmt->error, 'erro', $diretorioRetorno);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Matrizes</title>
    <link rel="stylesheet" href="../../../assets/css/estilo.css">
</head>
<body>
<div class="container">

    <?php
    $diretorioRetorno = '/../prjFazendaEtec033GC/admin/dashboard.php?';
    include_once(__DIR__ . '/../../../include/header.php');
    include_once(__DIR__ . '/../../../include/modal_msg.php');
    ?>
    <?php include '../../../include/modal_msg.php'; ?>

    <div class="top-bar">
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Buscar matriz..."
                   aria-label="Buscar matriz pelo nome ou raça"
                   autocomplete="off"
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <?php if (in_array('inclusao', $usuario_permissoes)): ?>
            <button class="btn" onclick="window.location.href='matrizes_add.php'"
                    title="Adicionar nova matriz"
                    aria-label="Adicionar nova matriz">Adicionar nova Matriz</button>
        <?php endif; ?>
    </div>

    <div style="overflow-x: auto;">
        <table id="matrizTable" role="table">
            <thead>
            <tr>
                <th scope="col" onclick="sortTable(0)">ID</th>
                <th scope="col" onclick="sortTable(1)">Nome</th>
                <th scope="col" onclick="sortTable(2)">Raça</th>
                <th scope="col" onclick="sortTable(3)">Nascimento</th>
                <th scope="col" onclick="sortTable(4)">Adicionada por</th>
                <th scope="col" onclick="sortTable(5)">Adicionada em</th>
                <th scope="col" onclick="sortTable(6)">Última Atualização</th>
                <th scope="col" onclick="sortTable(7)">Atualizado por</th>
                <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                    <th scope="col">Ações</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">
                        Nenhuma matriz encontrada para o termo "<?php echo htmlspecialchars($search); ?>"
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['raca']); ?></td>
                        <td>
                            <?php
                            echo $row['data_nascimento'] ? date('d/m/Y', strtotime($row['data_nascimento'])) : '-';
                            ?>
                        </td>
                        <td><?php echo isset($row['usuario_criacao']) ? htmlspecialchars($row['usuario_criacao']) : '-'; ?></td>
                        <td><?php echo $row['data_criacao'] ? date('d/m/Y H:i', strtotime($row['data_criacao'])) : '-'; ?></td>
                        <td>
                            <?php
                            echo $row['data_atualizacao'] ? date('d/m/Y H:i', strtotime($row['data_atualizacao'])) : '-';
                            ?>
                        </td>
                        <td><?php echo isset($row['usuario_atualizacao']) ? htmlspecialchars($row['usuario_atualizacao']) : '-'; ?></td>
                        <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                            <td>
                                <?php $nomeMatrizEscapado = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8'); ?>

                                <?php if (in_array('alteracao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Editar matriz <?php echo $nomeMatrizEscapado; ?>"
                                            aria-label="Editar matriz <?php echo $nomeMatrizEscapado; ?>"
                                            onclick="window.location.href='matrizes_edit.php?id=<?php echo $row['id']; ?>'">Editar</button>
                                <?php endif; ?>

                                <?php if (in_array('exclusao', $usuario_permissoes)): ?>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta matriz <?php echo htmlspecialchars($row['nome']); ?>?');">
                                        <input type="hidden" name="excluir_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn"
                                                title="Excluir matriz <?php echo $nomeMatrizEscapado; ?>"
                                                aria-label="Excluir matriz <?php echo $nomeMatrizEscapado; ?>">Excluir</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
    $titulo_ajuda = "Ajuda - Tela de Matrizes";
    $descricao_ajuda = "Esta tela exibe uma lista de todas as matrizes cadastradas no sistema.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Ajudar', 'descricao' => 'Abre esta tela de auxílio.'],
        ['titulo' => 'Buscar Matriz', 'descricao' => 'Exibe na tela dados específicos. Para mostrar tudo, apague o texto do campo de busca.'],
        ['titulo' => 'Adicionar', 'descricao' => 'Permite registrar uma nova matriz.'],
        ['titulo' => 'Editar', 'descricao' => 'Permite alterar os dados de uma matriz existente.'],
        ['titulo' => 'Excluir', 'descricao' => 'Remove o registro de uma matriz.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: As ações só aparecem se o usuário tiver permissão para executá-las.";
    include '../../../include/modal_ajuda.php';
    ?>

</div>

<?php include '../../../include/footer.php'; ?>
<script src="../../../assets/js/ordenaTabela.js"></script>
</body>
</html>
