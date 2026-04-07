<?php
$titulo_pagina = "Bem-vindo à tela de Pesagem de Matrizes";
include(__DIR__ . "../../../auth/auth.php");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    if (strlen($search) > 100) {
        die("Erro: o termo de busca é muito longo.");
    }

    if (!preg_match("/^[\p{L}\p{N}\s]+$/u", $search)) {
        die("Erro: o termo de busca contém caracteres inválidos.");
    }
}

$sql = "SELECT pm.id, m.nome AS matriz_nome, pm.peso, pm.data_pesagem
        FROM pesagem_matrizes pm
        JOIN matrizes m ON pm.matriz_id = m.id
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
    <title>Pesagem de Matrizes</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <?php include '../../include/header.php'; ?>

    <div class="top-bar">
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Buscar matriz..."
                   aria-label="Buscar matriz pelo nome"
                   autocomplete="off"
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <?php if (in_array('inclusao', $usuario_permissoes)): ?>
            <button class="btn" onclick="window.location.href='pesagem_matrizes_add.php'"
                    title="Adicionar nova pesagem"
                    aria-label="Adicionar nova pesagem">Adicionar nova Pesagem</button>
        <?php endif; ?>
    </div>

    <div style="overflow-x: auto;">
        <table id="pesagemMatrizesTable" role="table">
            <thead>
            <tr>
                <th scope="col" onclick="sortTable(0)">ID</th>
                <th scope="col" onclick="sortTable(1)">Matriz</th>
                <th scope="col" onclick="sortTable(2)">Peso</th>
                <th scope="col" onclick="sortTable(3)">Data</th>
                <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                    <th scope="col">Ações</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">
                        Nenhuma pesagem encontrada para o termo "<?php echo htmlspecialchars($search); ?>"
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['matriz_nome']); ?></td>
                        <td><?php echo $row['peso']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['data_pesagem'])); ?></td>
                        <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                            <td>
                                <?php $nomeMatrizEscapado = htmlspecialchars($row['matriz_nome'], ENT_QUOTES, 'UTF-8'); ?>

                                <?php if (in_array('alteracao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Editar pesagem da matriz <?php echo $nomeMatrizEscapado; ?>"
                                            aria-label="Editar pesagem da matriz <?php echo $nomeMatrizEscapado; ?>"
                                            onclick="window.location.href='pesagem_matrizes_edit.php?id=<?php echo $row['id']; ?>'">Editar</button>
                                <?php endif; ?>

                                <?php if (in_array('exclusao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Excluir pesagem da matriz <?php echo $nomeMatrizEscapado; ?>"
                                            aria-label="Excluir pesagem da matriz <?php echo $nomeMatrizEscapado; ?>"
                                            onclick="if(confirm('Deseja realmente excluir esta pesagem da matriz <?php echo $nomeMatrizEscapado; ?>?')) window.location.href='delete_pesagem_matrizes.php?id=<?php echo $row['id']; ?>'">Excluir</button>
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
    $titulo_ajuda = "Ajuda - Tela de Pesagem de Matrizes";
    $descricao_ajuda = "Esta tela exibe uma lista de todas as pesagens de matrizes cadastradas no sistema.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Ajudar', 'descricao' => 'Abre esta tela de auxílio.'],
        ['titulo' => 'Buscar Matriz', 'descricao' => 'Exibe na tela dados específicos. Para mostrar tudo, apague o texto do campo de busca.'],
        ['titulo' => 'Adicionar', 'descricao' => 'Permite registrar uma nova pesagem.'],
        ['titulo' => 'Editar', 'descricao' => 'Permite alterar os dados de uma pesagem existente.'],
        ['titulo' => 'Excluir', 'descricao' => 'Remove o registro de uma pesagem.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: As ações só aparecem se o usuário tiver permissão para executá-las.";
    include '../../include/modal_ajuda.php';
    ?>

</div>

<?php include '../../include/footer.php'; ?>
<script src="../../assets/js/ordenaTabela.js"></script>
</body>
</html>
