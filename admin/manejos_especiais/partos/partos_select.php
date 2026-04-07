<?php
$titulo_pagina = "Bem-vindo à tela de Partos";
include(__DIR__ . "/../../../auth/auth.php");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    if (strlen($search) > 100) {
        die("Erro: o termo de busca é muito longo.");
    }

    if (!preg_match("/^[\p{L}\p{N}\s]+$/u", $search)) {
        die("Erro: o termo de busca contém caracteres inválidos.");
    }
}

$sql = "SELECT p.*, m.nome AS nome_matriz 
        FROM partos p 
        JOIN matrizes m ON p.matriz_id = m.id
        WHERE m.nome LIKE ? OR p.qtd_crias LIKE ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Erro na preparação da consulta: " . $conn->error);
}

$searchParam = "%" . $search . "%";
$stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Erro na execução da consulta: " . $stmt->error);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Partos</title>
    <link rel="stylesheet" href="../../../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <?php include '../../../include/header.php'; ?>

    <div class="top-bar">
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Buscar parto..."
                   aria-label="Buscar parto por matriz ou quantidade de crias"
                   autocomplete="off"
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <?php if (in_array('inclusao', $usuario_permissoes)): ?>
            <button class="btn" onclick="window.location.href='partos_add.php'"
                    title="Adicionar novo parto"
                    aria-label="Adicionar novo parto">Adicionar novo Parto</button>
        <?php endif; ?>
    </div>

    <div style="overflow-x: auto;">
        <table id="partosTable" role="table">
            <thead>
            <tr>
                <th scope="col" onclick="sortTable(0)">ID</th>
                <th scope="col" onclick="sortTable(1)">Matriz</th>
                <th scope="col" onclick="sortTable(2)">Data Prevista Parto</th>
                <th scope="col" onclick="sortTable(3)">Data Efetiva Parto</th>
                <th scope="col" onclick="sortTable(4)">Data Prevista Desmame</th>
                <th scope="col" onclick="sortTable(5)">Data Efetiva Desmame</th>
                <th scope="col" onclick="sortTable(6)">Data Prevista Maternidade</th>
                <th scope="col" onclick="sortTable(7)">Data Efetiva Maternidade</th>
                <th scope="col" onclick="sortTable(8)">Qtd. Crias</th>
                <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                    <th scope="col">Ações</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="10" style="text-align: center;">
                        Nenhum parto encontrado para o termo "<?php echo htmlspecialchars($search); ?>"
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nome_matriz']) ?></td>
                        <td><?= $row['data_prevista_parto'] ? date('d/m/Y', strtotime($row['data_prevista_parto'])) : '—' ?></td>
                        <td><?= $row['data_efetiva_parto'] ? date('d/m/Y', strtotime($row['data_efetiva_parto'])) : '—' ?></td>
                        <td><?= $row['data_prevista_desmame'] ? date('d/m/Y', strtotime($row['data_prevista_desmame'])) : '—' ?></td>
                        <td><?= $row['data_efetiva_desmame'] ? date('d/m/Y', strtotime($row['data_efetiva_desmame'])) : '—' ?></td>
                        <td><?= $row['data_prevista_maternidade'] ? date('d/m/Y', strtotime($row['data_prevista_maternidade'])) : '—' ?></td>
                        <td><?= $row['data_efetiva_maternidade'] ? date('d/m/Y', strtotime($row['data_efetiva_maternidade'])) : '—' ?></td>
                        <td><?= $row['qtd_crias'] ?></td>
                        <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                            <td>
                                <?php if (in_array('alteracao', $usuario_permissoes)): ?>
                                    <button class="btn" onclick="window.location.href='partos_edit.php?id=<?= $row['id'] ?>'"
                                            title="Editar parto"
                                            aria-label="Editar parto">Editar</button>
                                <?php endif; ?>
                                <?php if (in_array('exclusao', $usuario_permissoes)): ?>
                                    <button class="btn" onclick="if(confirm('Deseja realmente excluir este parto?')) window.location.href='delete_parto.php?id=<?= $row['id'] ?>'"
                                            title="Excluir parto"
                                            aria-label="Excluir parto">Excluir</button>
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
    $titulo_ajuda = "Ajuda - Tela de Partos";
    $descricao_ajuda = "Esta tela exibe uma lista de todos os partos cadastrados no sistema.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Ajudar', 'descricao' => 'Abre esta tela de auxílio.'],
        ['titulo' => 'Buscar Parto', 'descricao' => 'Exibe na tela dados específicos. Para mostrar tudo, apague o texto do campo de busca.'],
        ['titulo' => 'Adicionar', 'descricao' => 'Permite registrar um novo parto.'],
        ['titulo' => 'Editar', 'descricao' => 'Permite alterar os dados de um parto existente.'],
        ['titulo' => 'Excluir', 'descricao' => 'Remove o registro de um parto.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: As ações só aparecem se o usuário tiver permissão para executá-las.";
    include '../../../include/modal_ajuda.php';
    ?>

</div>

<?php include '../../../include/footer.php'; ?>
<script src="../../../assets/js/ordenaTabela.js"></script>
</body>
</html>
