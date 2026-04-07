<?php
$titulo_pagina = "Bem-vindo à tela de Procedimentos";
include(__DIR__ . "/../../../auth/auth.php");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    if (strlen($search) > 100) {
    mostrarMsg("Erro: o termo de busca '" . $search . "' é muito longo.", 'erro', 'procedimentos_select.php');
    exit;
    }

    if (!preg_match("/^[\p{L}\p{N}\s]+$/u", $search)) {
    mostrarMsg("Erro: o termo de busca '" . $search . "' contém caracteres inválidos.", 'erro', 'procedimentos_select.php');
    exit;
    }
}

$sql = "SELECT * FROM procedimentos WHERE nome LIKE ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    mostrarMsg("Erro na preparação da consulta de procedimentos: " . $conn->error, 'erro', 'procedimentos_select.php');
    exit;
}

$searchParam = "%" . $search . "%";
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    mostrarMsg("Erro na execução da consulta de procedimentos: " . $stmt->error, 'erro', 'procedimentos_select.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Procedimentos</title>
    <link rel="stylesheet" href="../../../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <?php include '../../../include/header.php'; ?>

    <div class="top-bar">
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Buscar procedimento..."
                   aria-label="Buscar procedimento pelo nome"
                   autocomplete="off"
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <?php if (in_array('inclusao', $usuario_permissoes)): ?>
            <button class="btn" onclick="window.location.href='procedimentos_add.php'"
                    title="Adicionar novo procedimento"
                    aria-label="Adicionar novo procedimento">Adicionar novo Procedimento</button>
        <?php endif; ?>
    </div>

    <div style="overflow-x: auto;">
        <table id="procedimentosTable" role="table">
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
                        Nenhum procedimento encontrado para o termo "<?php echo htmlspecialchars($search); ?>"
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
                                <?php $nomeProcEscapado = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8'); ?>

                                <?php if (in_array('alteracao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Editar procedimento <?php echo $nomeProcEscapado; ?>"
                                            aria-label="Editar procedimento <?php echo $nomeProcEscapado; ?>"
                                            onclick="window.location.href='procedimentos_edit.php?id=<?php echo $row['id']; ?>'">Editar</button>
                                <?php endif; ?>

                                <?php if (in_array('exclusao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Excluir procedimento <?php echo $nomeProcEscapado; ?>"
                                            aria-label="Excluir procedimento <?php echo $nomeProcEscapado; ?>"
                                            onclick="if(confirm('Deseja realmente excluir o procedimento <?php echo $nomeProcEscapado; ?>?')) window.location.href='delete_procedimentos.php?id=<?php echo $row['id']; ?>'">Excluir</button>
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
    $titulo_ajuda = "Ajuda - Tela de Procedimentos";
    $descricao_ajuda = "Esta tela exibe uma lista de todos os procedimentos cadastrados no sistema.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Ajudar', 'descricao' => 'Abre esta tela de auxílio.'],
        ['titulo' => 'Buscar Procedimento', 'descricao' => 'Exibe na tela dados específicos. Para mostrar tudo, apague o texto do campo de busca.'],
        ['titulo' => 'Adicionar', 'descricao' => 'Permite registrar um novo procedimento.'],
        ['titulo' => 'Editar', 'descricao' => 'Permite alterar os dados de um procedimento existente.'],
        ['titulo' => 'Excluir', 'descricao' => 'Remove o registro de um procedimento.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: As ações só aparecem se o usuário tiver permissão para executá-las.";
    include '../../../include/modal_ajuda.php';
    ?>

</div>

<?php include '../../../include/footer.php'; ?>
<script src="../../../assets/js/ordenaTabela.js"></script>
</body>
</html>
