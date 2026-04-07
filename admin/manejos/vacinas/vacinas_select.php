<?php
$titulo_pagina = "Bem-vindo à tela de Vacinas";
include(__DIR__ . "/../../../auth/auth.php");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    if (strlen($search) > 100) {
    mostrarMsg("Erro: o termo de busca '" . $search . "' é muito longo.", 'erro', 'vacinas_select.php');
    exit;
    }

    if (!preg_match("/^[\p{L}\p{N}\s]+$/u", $search)) {
    mostrarMsg("Erro: o termo de busca '" . $search . "' contém caracteres inválidos.", 'erro', 'vacinas_select.php');
    exit;
    }
}

$sql = "SELECT * FROM vacinas WHERE nome LIKE ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    mostrarMsg("Erro na preparação da consulta de vacinas: " . $conn->error, 'erro', 'vacinas_select.php');
    exit;
}

$searchParam = "%" . $search . "%";
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    mostrarMsg("Erro na execução da consulta de vacinas: " . $stmt->error, 'erro', 'vacinas_select.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Vacinas</title>
    <link rel="stylesheet" href="../../../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <?php include '../../../include/header.php'; ?>

    <div class="top-bar">
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Buscar vacina..."
                   aria-label="Buscar vacina pelo nome"
                   autocomplete="off"
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <?php if (in_array('inclusao', $usuario_permissoes)): ?>
            <button class="btn" onclick="window.location.href='vacinas_add.php'"
                    title="Adicionar nova vacina"
                    aria-label="Adicionar nova vacina">Adicionar nova Vacina</button>
        <?php endif; ?>
    </div>

    <div style="overflow-x: auto;">
        <table id="vacinasTable" role="table">
            <thead>
            <tr>
                <th scope="col" onclick="sortTable(0)">ID</th>
                <th scope="col" onclick="sortTable(1)">Nome</th>
                <th scope="col" onclick="sortTable(2)">Descrição</th>
                <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                    <th scope="col">Ações</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">
                        Nenhuma vacina encontrada para o termo "<?php echo htmlspecialchars($search); ?>"
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['nome']; ?></td>
                        <td><?php echo $row['descricao']; ?></td>
                        <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                            <td>
                                <?php $nomeVacinaEscapado = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8'); ?>

                                <?php if (in_array('alteracao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Editar vacina <?php echo $nomeVacinaEscapado; ?>"
                                            aria-label="Editar vacina <?php echo $nomeVacinaEscapado; ?>"
                                            onclick="window.location.href='vacinas_edit.php?id=<?php echo $row['id']; ?>'">Editar</button>
                                <?php endif; ?>

                                <?php if (in_array('exclusao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Excluir vacina <?php echo $nomeVacinaEscapado; ?>"
                                            aria-label="Excluir vacina <?php echo $nomeVacinaEscapado; ?>"
                                            onclick="if(confirm('Deseja realmente excluir a vacina <?php echo $nomeVacinaEscapado; ?>?')) window.location.href='delete_vacina.php?id=<?php echo $row['id']; ?>'">Excluir</button>
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
    $titulo_ajuda = "Ajuda - Tela de Vacinas";
    $descricao_ajuda = "Esta tela exibe uma lista de todas as vacinas cadastradas no sistema.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Ajudar', 'descricao' => 'Abre esta tela de auxílio.'],
        ['titulo' => 'Buscar Vacina', 'descricao' => 'Exibe na tela dados específicos. Para mostrar tudo, apague o texto do campo de busca.'],
        ['titulo' => 'Adicionar', 'descricao' => 'Permite registrar uma nova vacina.'],
        ['titulo' => 'Editar', 'descricao' => 'Permite alterar os dados de uma vacina existente.'],
        ['titulo' => 'Excluir', 'descricao' => 'Remove o registro de uma vacina.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: As ações só aparecem se o usuário tiver permissão para executá-las.";
    include '../../../include/modal_ajuda.php';
    ?>

</div>

<?php include '../../../include/footer.php'; ?>
<script src="../../../assets/js/ordenaTabela.js"></script>
</body>
</html>
