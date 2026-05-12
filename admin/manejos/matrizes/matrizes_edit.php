<?php

$titulo_pagina = "Bem-vindo à tela de Editar Matriz";
$diretorioRetorno = '/prjFazendaEtec033GC/admin/manejos/matrizes/matrizes_select.php';

include_once(__DIR__ . "/../../../auth/auth.php");
include_once(__DIR__ . "/../../../include/funcoes.php");
include_once(__DIR__ . "/../../../database/conexao.php");
global $conn;

// Sessão já validada pelo auth.php
$usuario_id = intval($_SESSION['usuario_id']);
// ...código existente...
$diretorioRetorno = '/prjFazendaEtec033GC/admin/manejos/matrizes/matrizes_select.php';
include_once(__DIR__ . '/../../../include/header.php');
include_once(__DIR__ . '/../../../include/modal_msg.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nome = trim($_POST['nome']);
    $raca_id = intval($_POST['raca_id']);
    $data_nascimento = $_POST['data_nascimento'];

    $nome_antigo = buscarNomeMatriz($conn, $id);
    $stmt = $conn->prepare("UPDATE matrizes SET nome = ?, raca_id = ?, data_nascimento = ?, usuario_atualizacao_id = ?, data_atualizacao = NOW() WHERE id = ?");
    if ($stmt === false) {
        mostrarMsg("Erro na preparação da declaração da matriz '$nome_antigo'.", 'erro', $diretorioRetorno);
    } else {
        $stmt->bind_param("sissi", $nome, $raca_id, $data_nascimento, $usuario_id, $id);
        if (!$stmt->execute()) {
            mostrarMsg("Erro ao atualizar matriz '$nome_antigo': " . $stmt->error, 'erro', $diretorioRetorno);
        } else {
            $stmt->close();
            registrarLog($conn, $usuario_id, 'matrizes', 'alteracao');
            if ($nome_antigo !== $nome) {
                mostrarMsg("Matriz '$nome_antigo' alterada para '$nome' com sucesso!", 'acerto', $diretorioRetorno);
            } else {
                mostrarMsg("Matriz '$nome' atualizada com sucesso!", 'acerto', $diretorioRetorno);
            }
        }
    }
    // NÃO fechar $conn aqui, pois ele pode ser usado para SELECT abaixo
}

if (isset($_GET['id'])) {
// Buscar raças do banco ANTES de fechar a conexão
$racas_array = [];
if ($conn) {
    $result_racas = $conn->query("SELECT id, nome FROM racas_suinas ORDER BY nome ASC");
    if ($result_racas) {
        while ($raca = $result_racas->fetch_assoc()) {
            $racas_array[] = $raca;
        }
    }
}
    $id = intval($_GET['id']);
    $sql = "SELECT m.*, r.nome AS nome_raca FROM matrizes m LEFT JOIN racas_suinas r ON m.raca_id = r.id WHERE m.id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        mostrarMsg("Erro na preparação da consulta: " . $conn->error, 'erro', $diretorioRetorno);
    } else {
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            mostrarMsg("Erro ao buscar matriz: " . $stmt->error, 'erro', $diretorioRetorno);
        } else {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            if (!$row) {
                mostrarMsg("Matriz não encontrada.", 'atencao', $diretorioRetorno);
            }
        }
    }
} else {
    mostrarMsg("ID da matriz não fornecido.", 'atencao', $diretorioRetorno);
}

// Fechar conexão apenas no final do script
if ($conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo_pagina; ?></title>
    <link rel="stylesheet" href="../../../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <?php include '../../../include/header.php'; ?>
    <?php include '../../../include/modal_msg.php'; ?>

    <?php if (isset($row) && $row): ?>
    <form method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($row['nome']); ?>" required>

        <label for="raca_id">Raça:</label>
        <select id="raca_id" name="raca_id" required>
            <?php
            foreach ($racas_array as $raca) {
                $selected = ($row['raca_id'] == $raca['id']) ? 'selected' : '';
                echo "<option value=\"{$raca['id']}\" $selected>{$raca['nome']}</option>";
            }
            ?>
        </select>

        <label for="data_nascimento">Data de Nascimento:</label>
        <?php
        // Formatar data para o input type="date"
        $data_nasc = $row['data_nascimento'];
        if ($data_nasc && strlen($data_nasc) > 10) {
            $data_nasc = date('Y-m-d', strtotime($data_nasc));
        }
        ?>
        <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($data_nasc); ?>" required>

        <button type="submit" class="btn">Salvar</button>
    </form>
    <?php endif; ?>

    <?php
    $titulo_ajuda = "Ajuda - Editar Matriz";
    $descricao_ajuda = "Esta tela permite editar os dados de uma matriz já cadastrada.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Salvar', 'descricao' => 'Atualiza os dados da matriz.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: Apenas usuários com permissão de edição podem acessar esta tela.";
    include '../../../include/modal_ajuda.php';
    ?>
</div>

<?php include '../../../include/footer.php'; ?>
</body>
</html>
