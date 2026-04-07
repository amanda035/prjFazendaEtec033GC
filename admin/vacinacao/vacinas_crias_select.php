<?php
$titulo_pagina = "Bem-vindo à tela de Vacinação de Crias";
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

$sql = "SELECT vc.id, c.nome AS cria_nome, v.nome AS vacina_nome, vc.data_aplicacao
        FROM vacinas_crias vc
        JOIN crias c ON vc.cria_id = c.id
        JOIN vacinas v ON vc.vacina_id = v.id
        WHERE c.nome LIKE ? OR v.nome LIKE ?";
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
    <title>Vacinas de Crias</title>
    <link rel="stylesheet" href="../../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <?php include '../../include/header.php'; ?>

    <div class="top-bar">
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Buscar cria ou vacina..."
                   aria-label="Buscar cria ou vacina"
                   autocomplete="off"
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <?php if (in_array('inclusao', $usuario_permissoes)): ?>
            <button class="btn" onclick="window.location.href='vacinas_crias_add.php'"
                    title="Adicionar vacina para cria"
                    aria-label="Adicionar vacina para cria">Adicionar Vacina para Cria</button>
        <?php endif; ?>
    </div>

    <div style="overflow-x: auto;">
        <table id="vacinasCriasTable" role="table">
            <thead>
            <tr>
                <th scope="col" onclick="sortTable(0)">ID</th>
                <th scope="col" onclick="sortTable(1)">Cria</th>
                <th scope="col" onclick="sortTable(2)">Vacina</th>
                <th scope="col" onclick="sortTable(3)">Data de Aplicação</th>
                <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                    <th scope="col">Ações</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">
                        Nenhum registro encontrado para "<?php echo htmlspecialchars($search); ?>"
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['cria_nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['vacina_nome']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['data_aplicacao'])); ?></td>
                        <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                            <td>
                                <?php $nomeCria = htmlspecialchars($row['cria_nome'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (in_array('alteracao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Editar vacina da cria <?php echo $nomeCria; ?>"
                                            aria-label="Editar vacina da cria <?php echo $nomeCria; ?>"
                                            onclick="window.location.href='vacinas_crias_edit.php?id=<?php echo $row['id']; ?>'">Editar</button>
                                <?php endif; ?>
                                <?php if (in_array('exclusao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Excluir vacina da cria <?php echo $nomeCria; ?>"
                                            aria-label="Excluir vacina da cria <?php echo $nomeCria; ?>"
                                            onclick="if(confirm('Deseja realmente excluir o registro de vacina da cria <?php echo $nomeCria; ?>?')) window.location.href='delete_vacinas_crias.php?id=<?php echo $row['id']; ?>'">Excluir</button>
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
    $titulo_ajuda = "Ajuda - Vacinas de Crias";
    $descricao_ajuda = "Esta tela exibe uma lista das vacinas aplicadas às crias.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Ajudar', 'descricao' => 'Abre esta tela de auxílio.'],
        ['titulo' => 'Buscar', 'descricao' => 'Filtra os registros por nome da cria ou vacina.'],
        ['titulo' => 'Adicionar', 'descricao' => 'Permite registrar uma nova vacina aplicada a uma cria.'],
        ['titulo' => 'Editar', 'descricao' => 'Permite alterar os dados de uma vacina aplicada.'],
        ['titulo' => 'Excluir', 'descricao' => 'Remove o registro de uma vacina aplicada.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: As ações só aparecem se o usuário tiver permissão para executá-las.";
    include '../../include/modal_ajuda.php';
    ?>

</div>

<?php include '../../include/footer.php'; ?>
<script src="../../assets/js/ordenaTabela.js"></script>
</body>
</html>
