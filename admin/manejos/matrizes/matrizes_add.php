<?php

$titulo_pagina = "Bem-vindo à tela de Adicionar Matriz";
$diretorioRetorno = '/prjFazendaEtec033/admin/manejos/matrizes/matrizes_select.php';


include_once(__DIR__ . "/../../../auth/auth.php");
include_once(__DIR__ . "/../../../database/conexao.php");
include_once(__DIR__ . "/../../../include/funcoes.php");
global $conn;


$racas = [];
if (isset($conn) && $conn) {
    $sqlRacas = "SELECT id, nome FROM racas_suinas ORDER BY nome";
    $resultRacas = $conn->query($sqlRacas);
    if ($resultRacas) {
        while ($row = $resultRacas->fetch_assoc()) {
            $racas[] = $row;
        }
    }
} else {
    mostrarMsg("Erro de conexão ao cadastrar matriz '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "'. Verifique o arquivo conexao.php.", 'erro', $diretorioRetorno);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($conn) || !$conn) {
        mostrarMsg("Erro de conexão ao cadastrar matriz '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "'. Verifique o arquivo conexao.php.", 'erro', $diretorioRetorno);
    } else {
        $nome = trim($_POST['nome']);
        $raca_id = intval($_POST['raca_id']);
        $data_nascimento = $_POST['data_nascimento'];
        $usuario_id = intval($_SESSION['usuario_id']);

        // Verifica se já existe matriz com o mesmo nome
        $testa_regras = 0; // Tudo ok
        $stmt_verifica = $conn->prepare("SELECT id FROM matrizes WHERE nome = ?");
        if ($stmt_verifica === false) {
            mostrarMsg("Erro ao verificar existência da matriz '$nome': " . $conn->error, 'erro');
        } else {
            $stmt_verifica->bind_param("s", $nome);
            $stmt_verifica->execute();
            $stmt_verifica->store_result();
            if ($stmt_verifica->num_rows > 0) {
                mostrarMsg("Já existe uma matriz cadastrada com o nome '$nome'. Por favor, escolha outro nome.", 'atencao', 'matrizes_add.php');
                $testa_regras = 1; // Nome já existe
            } else {
                $stmt_verifica->close();
            }
        }

        // Verifica se a data de nascimento é válida
        if ($data_nascimento > date('Y-m-d')) {
            mostrarMsg("A data de nascimento não pode ser superior à data atual.", 'atencao', 'matrizes_add.php');
            $testa_regras = 1; // Data inválida
        }

        if ($testa_regras === 0) { 
            // Insere matriz
            $stmt = $conn->prepare("INSERT INTO matrizes (nome, raca_id, data_nascimento, usuario_criacao_id, usuario_atualizacao_id, data_criacao, data_atualizacao)
                                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            if ($stmt === false) {
                mostrarMsg("Erro na preparação da declaração para matriz '$nome': " . $conn->error, 'erro');
            } else {
                $stmt->bind_param("sisii", $nome, $raca_id, $data_nascimento, $usuario_id, $usuario_id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    registrarLog($conn, $usuario_id, 'matrizes', 'inclusao');
                    mostrarMsg("Matriz '$nome' cadastrada com sucesso!", 'acerto', $diretorioRetorno);
                } else {
                    mostrarMsg("Não foi possível cadastrar a matriz '$nome'.", 'erro');
                }
                $stmt->close();
            }
            $conn->close();
        }
    }
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
    <?php 
        include '../../../include/header.php';
        include '../../../include/modal_msg.php';
    ?>

    <form method="post">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="raca_id">Raça:</label>
        <select id="raca_id" name="raca_id" required>
            <option value="">Selecione...</option>
            <?php foreach ($racas as $raca): ?>
                <option value="<?php echo $raca['id']; ?>"><?php echo htmlspecialchars($raca['nome']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" name="data_nascimento" required>

        <button type="submit" class="btn">Adicionar</button>
    </form>

    <?php
    $titulo_ajuda = "Ajuda - Adicionar Matriz";
    $descricao_ajuda = "Esta tela permite o cadastro de uma nova matriz no sistema.";
    $itens_ajuda = [
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Adicionar', 'descricao' => 'Salva os dados da nova matriz.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: Apenas usuários com permissão de inclusão podem acessar esta tela.";
    include '../../../include/modal_ajuda.php';
    ?>
</div>

<?php include '../../../include/footer.php'; ?>
</body>
</html>
