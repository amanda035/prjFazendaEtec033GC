<?php
$titulo_pagina = "Bem-vindo à tela de Coberturas";
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

$sql = "SELECT c.id, m.nome AS matriz_nome, c.data_cobertura 
        FROM coberturas c
        JOIN matrizes m ON c.matriz_id = m.id
        WHERE m.nome LIKE ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Erro na preparação da consulta: " . $conn->error);
}

$searchParam = "%" . $search . "%";
$stmt->bind_param("s", $searchParam);
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
    <title>Coberturas</title>
    <link rel="stylesheet" href="../../../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <?php include '../../../include/header.php'; ?>

    <div class="top-bar">
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Buscar cobertura por matriz..."
                   aria-label="Buscar cobertura por matriz"
                   autocomplete="off"
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <?php if (in_array('inclusao', $usuario_permissoes)): ?>
            <button class="btn" onclick="window.location.href='coberturas_add.php'"
                    title="Adicionar nova cobertura"
                    aria-label="Adicionar nova cobertura">Adicionar nova Cobertura</button>
        <?php endif; ?>
    </div>

    <div style="overflow-x: auto;">
        <table id="coberturasTable" role="table">
            <thead>
            <tr>
                <th scope="col" onclick="sortTable(0)">ID</th>
                <th scope="col" onclick="sortTable(1)">Matriz</th>
                <th scope="col" onclick="sortTable(2)">Data de Cobertura</th>
                <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                    <th scope="col">Ações</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">
                        Nenhuma cobertura encontrada para o termo "<?php echo htmlspecialchars($search); ?>"
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['matriz_nome']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['data_cobertura'])); ?></td>
                        <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                            <td>
                                <?php $matrizEscapada = htmlspecialchars($row['matriz_nome'], ENT_QUOTES, 'UTF-8'); ?>

                                <?php if (in_array('alteracao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Editar cobertura da matriz <?php echo $matrizEscapada; ?>"
                                            aria-label="Editar cobertura da matriz <?php echo $matrizEscapada; ?>"
                                            onclick="window.location.href='coberturas_edit.php?id=<?php echo $row['id']; ?>'">Editar</button>
                                <?php endif; ?>

                                <?php if (in_array('exclusao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Excluir cobertura da matriz <?php echo $matrizEscapada; ?>"
                                            aria-label="Excluir cobertura da matriz <?php echo $matrizEscapada; ?>"
                                            onclick="if(confirm('Deseja realmente excluir a cobertura da matriz <?php echo $matrizEscapada; ?>?')) window.location.href='delete_coberturas.php?id=<?php echo $row['id']; ?>'">Excluir</button>
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
    $titulo_ajuda = "Ajuda - Tela de Coberturas";
    $descricao_ajuda = "Esta tela exibe uma lista de todas as coberturas cadastradas no sistema.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Ajudar', 'descricao' => 'Abre esta tela de auxílio.'],
        ['titulo' => 'Buscar Cobertura', 'descricao' => 'Exibe na tela dados específicos. Para mostrar tudo, apague o texto do campo de busca.'],
        ['titulo' => 'Adicionar', 'descricao' => 'Permite registrar uma nova cobertura.'],
        ['titulo' => 'Editar', 'descricao' => 'Permite alterar os dados de uma cobertura existente.'],
        ['titulo' => 'Excluir', 'descricao' => 'Remove o registro de uma cobertura.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: As ações só aparecem se o usuário tiver permissão para executá-las.";
    include '../../../include/modal_ajuda.php';
    ?>

</div>

<?php include '../../../include/footer.php'; ?>
<script src="../../../assets/js/ordenaTabela.js"></script>
</body>
</html>
