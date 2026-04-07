<?php
include(__DIR__ . "/../../../auth/auth.php");

$id = intval($_GET['id']);

// Buscar dados do parto
$stmt = $conn->prepare("SELECT p.*, m.nome AS nome_matriz FROM partos p JOIN matrizes m ON p.matriz_id = m.id WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_parto = !empty($_POST['data_efetiva_parto']) ? $_POST['data_efetiva_parto'] : null;
    $data_desmame = !empty($_POST['data_efetiva_desmame']) ? $_POST['data_efetiva_desmame'] : null;
    $data_maternidade = !empty($_POST['data_efetiva_maternidade']) ? $_POST['data_efetiva_maternidade'] : null;
    $qtd_crias = isset($_POST['qtd_crias']) ? intval($_POST['qtd_crias']) : null;

    $erros = [];

    if (!empty($data_parto) && (empty($qtd_crias) || $qtd_crias <= 0)) {
        $erros[] = "A quantidade de crias é obrigatória quando a Data Efetiva do Parto é informada.";
    }

    // Buscar configuração
    $config = $conn->query("SELECT * FROM configuracoes WHERE id = 1")->fetch_assoc();
    $dias_desmame = intval($config['dia_previsto_desmame']);

    // Calcular data prevista de desmame
    $data_prevista_desmame = null;
    if (!empty($data_parto) && $dias_desmame > 0) {
        $data_prevista_desmame = date('Y-m-d', strtotime($data_parto . " +{$dias_desmame} days"));
    }

    if (empty($erros)) {
        $stmt = $conn->prepare("UPDATE partos SET data_efetiva_parto = ?, data_efetiva_desmame = ?, data_efetiva_maternidade = ?, qtd_crias = ?, data_prevista_desmame = ? WHERE id = ?");
        $stmt->bind_param("sssisi", $data_parto, $data_desmame, $data_maternidade, $qtd_crias, $data_prevista_desmame, $id);

        if ($stmt->execute()) {
            // Log de alteração no parto
            $stmt_log_parto = $conn->prepare("INSERT INTO logs (usuario_id, tabela, acao, data_acao) VALUES (?, 'partos', 'alteracao', NOW())");
            $stmt_log_parto->bind_param("i", $_SESSION['usuario_id']);
            $stmt_log_parto->execute();
            $stmt_log_parto->close();

            // Inserir crias se ainda não existirem
            if (!empty($data_parto) && $qtd_crias > 0) {
                $check = $conn->prepare("SELECT COUNT(*) FROM crias WHERE parto_id = ?");
                $check->bind_param("i", $id);
                $check->execute();
                $check->bind_result($total_crias);
                $check->fetch();
                $check->close();

                if ($total_crias == 0) {
                    for ($i = 1; $i <= $qtd_crias; $i++) {
                        $nome_cria = "Leitão-M{$row['matriz_id']}-P{$id}-N{$i}";
                        $stmt_cria = $conn->prepare("INSERT INTO crias (parto_id, nome, raca, sexo, peso_nascimento, data_nascimento, usuario_id, data_acao) VALUES (?, ?, '', 'Macho', 0, ?, ?, NOW())");
                        $stmt_cria->bind_param("issi", $id, $nome_cria, $data_parto, $_SESSION['usuario_id']);
                        $stmt_cria->execute();
                        $stmt_cria->close();
                    }

                    // Log de inclusão de crias
                    $stmt_log_crias = $conn->prepare("INSERT INTO logs (usuario_id, tabela, acao, data_acao) VALUES (?, 'crias', 'inclusao', NOW())");
                    $stmt_log_crias->bind_param("i", $_SESSION['usuario_id']);
                    $stmt_log_crias->execute();
                    $stmt_log_crias->close();
                }
            }

            header('Location: partos.php');
            exit;
        } else {
            echo "Erro ao atualizar: " . $stmt->error;
        }
    } else {
        foreach ($erros as $erro) {
            echo "<p style='color:red;'>$erro</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Parto</title>
    <link rel="stylesheet" href="../../../assets/css/styles.css">
</head>
<body>
    <h1>Editar Parto</h1>
    <form method="post">
        <label>Matriz:</label>
        <input type="text" value="<?= htmlspecialchars($row['nome_matriz']) ?>" disabled>

        <label>Data Prevista do Parto:</label>
        <input type="date" value="<?= $row['data_prevista_parto'] ?>" disabled>

        <label>Data Efetiva do Parto:</label>
        <input type="date" name="data_efetiva_parto" value="<?= $row['data_efetiva_parto'] ?>">

        <label>Data Prevista do Desmame:</label>
        <input type="date" value="<?= $row['data_prevista_desmame'] ?>" disabled>

        <label>Data Efetiva do Desmame:</label>
        <input type="date" name="data_efetiva_desmame" value="<?= $row['data_efetiva_desmame'] ?>">

        <label>Data Prevista da Maternidade:</label>
        <input type="date" value="<?= $row['data_prevista_maternidade'] ?>" disabled>

        <label>Data Efetiva da Maternidade:</label>
        <input type="date" name="data_efetiva_maternidade" value="<?= $row['data_efetiva_maternidade'] ?>">

        <label>Quantidade de Crias:</label>
        <input type="number" name="qtd_crias" value="<?= $row['qtd_crias'] ?>">

        <button type="submit">Salvar</button>
    </form>
</body>
</html>
