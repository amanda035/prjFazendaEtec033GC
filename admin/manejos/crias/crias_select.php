<?php
$titulo_pagina = "Bem-vindo à tela de Crias";
include(__DIR__ . "/../../../auth/auth.php");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    if (strlen($search) > 100) {
    mostrarMsg("Erro: o termo de busca '" . $search . "' é muito longo.", 'erro', 'crias_select.php');
    exit;
    }

    if (!preg_match("/^[\p{L}\p{N}\s]+$/u", $search)) {
    mostrarMsg("Erro: o termo de busca '" . $search . "' contém caracteres inválidos.", 'erro', 'crias_select.php');
    exit;
    }
}

$sql = "SELECT * FROM crias WHERE nome LIKE ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    mostrarMsg("Erro na preparação da consulta de crias: " . $conn->error, 'erro', 'crias_select.php');
    exit;
}

$searchParam = "%" . $search . "%";
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    mostrarMsg("Erro na execução da consulta de crias: " . $stmt->error, 'erro', 'crias_select.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Crias</title>
    <link rel="stylesheet" href="../../../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <?php include '../../../include/header.php'; ?>

    <div class="top-bar">
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Buscar cria..."
                   aria-label="Buscar cria pelo nome"
                   autocomplete="off"
                   value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <?php if (in_array('inclusao', $usuario_permissoes)): ?>
            <button class="btn" onclick="window.location.href='crias_add.php'"
                    title="Adicionar nova cria"
                    aria-label="Adicionar nova cria">Adicionar nova Cria</button>
        <?php endif; ?>
    </div>

    <div style="overflow-x: auto;">
        <table id="criasTable" role="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>ID Parto</th>
                <th>Nome</th>
                <th>Raça</th>
                <th>Sexo</th>
                <th>Peso Nasc.</th>
                <th>Data Nasc.</th>
                <th>Status</th>
                <th>Baia</th>
                <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                    <th>Ações</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="10" style="text-align: center;">
                        Nenhuma cria encontrada para o termo "<?php echo htmlspecialchars($search); ?>"
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['parto_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['raca']); ?></td>
                        <td><?php echo $row['sexo']; ?></td>
                        <td><?php echo number_format($row['peso_nascimento'], 2, ',', '.'); ?> kg</td>
                        <td><?php echo date('d/m/Y', strtotime($row['data_nascimento'])); ?></td>
                        <td><?php echo $row['status_atual']; ?></td>
                        <td><?php echo $row['baia_id']; ?></td>

                        <?php if (array_intersect(['alteracao', 'exclusao'], $usuario_permissoes)): ?>
                            <td>
                                <?php $nomeCriaEscapado = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8'); ?>

                                <?php if (in_array('alteracao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Editar cria <?php echo $nomeCriaEscapado; ?>"
                                            aria-label="Editar cria <?php echo $nomeCriaEscapado; ?>"
                                            onclick="window.location.href='crias_edit.php?id=<?php echo $row['id']; ?>'">
                                        Editar
                                    </button>
                                <?php endif; ?>

                                <?php if (in_array('exclusao', $usuario_permissoes)): ?>
                                    <button class="btn"
                                            title="Excluir cria <?php echo $nomeCriaEscapado; ?>"
                                            aria-label="Excluir cria <?php echo $nomeCriaEscapado; ?>"
                                            onclick="if(confirm('Deseja realmente excluir a cria <?php echo $nomeCriaEscapado; ?>?')) window.location.href='delete_cria.php?id=<?php echo $row['id']; ?>'">
                                        Excluir
                                    </button>
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
    $titulo_ajuda = "Ajuda - Tela de Crias";
    $descricao_ajuda = "Esta tela exibe uma lista de todas as crias cadastradas no sistema.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Ajudar', 'descricao' => 'Abre esta tela de auxílio.'],
        ['titulo' => 'Buscar Cria', 'descricao' => 'Exibe na tela dados específicos. Para mostrar tudo, apague o texto do campo de busca.'],
        ['titulo' => 'Adicionar', 'descricao' => 'Permite registrar uma nova cria.'],
        ['titulo' => 'Editar', 'descricao' => 'Permite alterar os dados de uma cria existente.'],
        ['titulo' => 'Excluir', 'descricao' => 'Remove o registro de uma cria.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: As ações só aparecem se o usuário tiver permissão para executá-las.";
    include '../../../include/modal_ajuda.php';
    ?>

</div>

<?php include '../../../include/footer.php'; ?>
<script src="../../../assets/js/ordenaTabela.js"></script>
</body>
</html>
