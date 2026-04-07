
<?php
session_start();
$titulo_pagina = "Bem-vindo à tela de Configurações do Sistema";
include(__DIR__ . "../../auth/auth.php");
require_once __DIR__ . '/../include/funcoes.php';
include_once __DIR__ . '/../database/conexao.php';
$conn = $GLOBALS['conn'];

if (!isset($_SESSION['nivel_acesso']) || $_SESSION['nivel_acesso'] != 0) {
    header('Location: ../index.php');
    exit;
}

// Inicializa variáveis para evitar warnings
$dia_previsto_gestacao = $dia_preparacao_parto = $dia_previsto_desmame = $dia_aplicacao_ferro1 = $dia_aplicacao_ferro2 = $dia_desbaste_dentes = $dia_desbaste_cauda = $dia_aplicacao_baycox1 = $dia_aplicacao_baycox2 = $dia_comportamento = '';
$nome_sistema = $cor_primaria = $email_suporte = $acessibilidade = '';

// Salvar configurações no banco
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $campos = [
        'dia_previsto_gestacao', 'dia_preparacao_parto', 'dia_previsto_desmame',
        'dia_aplicacao_ferro1', 'dia_aplicacao_ferro2', 'dia_desbaste_dentes',
        'dia_desbaste_cauda', 'dia_aplicacao_baycox1', 'dia_aplicacao_baycox2',
        'dia_comportamento', 'nome_sistema', 'cor_primaria', 'email_suporte', 'acessibilidade'
    ];
    $dados = [];
    foreach ($campos as $campo) {
        $dados[$campo] = isset($_POST[$campo]) ? $_POST[$campo] : null;
    }
    // Verifica se já existe registro
    $stmt = $conn->prepare("SELECT COUNT(*) FROM configuracoes WHERE id=1");
    $stmt->execute();
    $stmt->bind_result($existe);
    $stmt->fetch();
    $stmt->close();
    if ($existe) {
        $sql = "UPDATE configuracoes SET dia_previsto_gestacao=?, dia_preparacao_parto=?, dia_previsto_desmame=?, dia_aplicacao_ferro1=?, dia_aplicacao_ferro2=?, dia_desbaste_dentes=?, dia_desbaste_cauda=?, dia_aplicacao_baycox1=?, dia_aplicacao_baycox2=?, dia_comportamento=?, nome_sistema=?, cor_primaria=?, email_suporte=?, acessibilidade=?, usuario_atualizacao_id=?, data_atualizacao=NOW() WHERE id=1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiiiiiiissssii",
            $dados['dia_previsto_gestacao'], $dados['dia_preparacao_parto'], $dados['dia_previsto_desmame'],
            $dados['dia_aplicacao_ferro1'], $dados['dia_aplicacao_ferro2'], $dados['dia_desbaste_dentes'],
            $dados['dia_desbaste_cauda'], $dados['dia_aplicacao_baycox1'], $dados['dia_aplicacao_baycox2'],
            $dados['dia_comportamento'], $dados['nome_sistema'], $dados['cor_primaria'], $dados['email_suporte'], $dados['acessibilidade'], $_SESSION['usuario_id'], $_SESSION['usuario_id']
        );
        $stmt->execute();
        $stmt->close();
        mostrarMsg("Configurações atualizadas com sucesso.", "acerto", "configuracoes.php");
    } else {
        $sql = "INSERT INTO configuracoes (id, dia_previsto_gestacao, dia_preparacao_parto, dia_previsto_desmame, dia_aplicacao_ferro1, dia_aplicacao_ferro2, dia_desbaste_dentes, dia_desbaste_cauda, dia_aplicacao_baycox1, dia_aplicacao_baycox2, dia_comportamento, nome_sistema, cor_primaria, email_suporte, acessibilidade, usuario_criacao_id, usuario_atualizacao_id, data_criacao, data_atualizacao) VALUES (1,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiiiiiiissssii",
            $dados['dia_previsto_gestacao'], $dados['dia_preparacao_parto'], $dados['dia_previsto_desmame'],
            $dados['dia_aplicacao_ferro1'], $dados['dia_aplicacao_ferro2'], $dados['dia_desbaste_dentes'],
            $dados['dia_desbaste_cauda'], $dados['dia_aplicacao_baycox1'], $dados['dia_aplicacao_baycox2'],
            $dados['dia_comportamento'], $dados['nome_sistema'], $dados['cor_primaria'], $dados['email_suporte'], $dados['acessibilidade'], $_SESSION['usuario_id'], $_SESSION['usuario_id']
        );
        $stmt->execute();
        $stmt->close();
        mostrarMsg("Configurações cadastradas com sucesso.", "acerto", "configuracoes.php");
    }
}

// Recuperar configurações do banco
$stmt = $conn->prepare("SELECT dia_previsto_gestacao, dia_preparacao_parto, dia_previsto_desmame, dia_aplicacao_ferro1, dia_aplicacao_ferro2, dia_desbaste_dentes, dia_desbaste_cauda, dia_aplicacao_baycox1, dia_aplicacao_baycox2, dia_comportamento, nome_sistema, cor_primaria, email_suporte, acessibilidade FROM configuracoes WHERE id=1");
$stmt->execute();
$stmt->bind_result(
    $dia_previsto_gestacao, $dia_preparacao_parto, $dia_previsto_desmame,
    $dia_aplicacao_ferro1, $dia_aplicacao_ferro2, $dia_desbaste_dentes,
    $dia_desbaste_cauda, $dia_aplicacao_baycox1, $dia_aplicacao_baycox2,
    $dia_comportamento, $nome_sistema, $cor_primaria, $email_suporte, $acessibilidade
);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Configurações</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
<div class="container">
    <?php include '../include/header.php'; ?>


    <div class="admin-container">
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('sistema')">Configurações do Sistema</button>
            <button class="tab-btn" onclick="showTab('acessibilidade')">Opções de Acessibilidade</button>
        </div>
        <div id="tab-sistema" class="tab-content active">
            <h2>Configurações do Sistema</h2>
            <?php if (isset($mensagem) && $mensagem): ?>
                <p style="color: green;"> <?php echo htmlspecialchars($mensagem); ?> </p>
            <?php endif; ?>
            <form method="POST" action="configuracoes.php">
                <label for="nome_sistema">Nome do sistema:</label>
                <input type="text" id="nome_sistema" name="nome_sistema" class="input-admin" value="<?php echo htmlspecialchars($nome_sistema); ?>" required>

                <label for="cor_primaria">Cor primária:</label>
                <input type="color" id="cor_primaria" name="cor_primaria" class="input-admin" value="<?php echo $cor_primaria ? htmlspecialchars($cor_primaria) : '#009900'; ?>">

                <label for="email_suporte">E-mail de suporte:</label>
                <input type="email" id="email_suporte" name="email_suporte" class="input-admin" value="<?php echo htmlspecialchars($email_suporte); ?>">

                <label for="dia_previsto_gestacao">Dias previstos para gestação:</label>
                <input type="number" id="dia_previsto_gestacao" name="dia_previsto_gestacao" class="input-admin" value="<?php echo $dia_previsto_gestacao !== '' ? htmlspecialchars($dia_previsto_gestacao) : '0'; ?>">

                <label for="dia_preparacao_parto">Dias de preparação para parto:</label>
                <input type="number" id="dia_preparacao_parto" name="dia_preparacao_parto" class="input-admin" value="<?php echo $dia_preparacao_parto !== '' ? htmlspecialchars($dia_preparacao_parto) : '0'; ?>">

                <label for="dia_previsto_desmame">Dias previstos para desmame:</label>
                <input type="number" id="dia_previsto_desmame" name="dia_previsto_desmame" class="input-admin" value="<?php echo $dia_previsto_desmame !== '' ? htmlspecialchars($dia_previsto_desmame) : '0'; ?>">

                <label for="dia_aplicacao_ferro1">Dia aplicação ferro 1:</label>
                <input type="number" id="dia_aplicacao_ferro1" name="dia_aplicacao_ferro1" class="input-admin" value="<?php echo $dia_aplicacao_ferro1 !== '' ? htmlspecialchars($dia_aplicacao_ferro1) : '0'; ?>">

                <label for="dia_aplicacao_ferro2">Dia aplicação ferro 2:</label>
                <input type="number" id="dia_aplicacao_ferro2" name="dia_aplicacao_ferro2" class="input-admin" value="<?php echo $dia_aplicacao_ferro2 !== '' ? htmlspecialchars($dia_aplicacao_ferro2) : '0'; ?>">

                <label for="dia_desbaste_dentes">Dia desbaste dentes:</label>
                <input type="number" id="dia_desbaste_dentes" name="dia_desbaste_dentes" class="input-admin" value="<?php echo $dia_desbaste_dentes !== '' ? htmlspecialchars($dia_desbaste_dentes) : '0'; ?>">

                <label for="dia_desbaste_cauda">Dia desbaste cauda:</label>
                <input type="number" id="dia_desbaste_cauda" name="dia_desbaste_cauda" class="input-admin" value="<?php echo $dia_desbaste_cauda !== '' ? htmlspecialchars($dia_desbaste_cauda) : '0'; ?>">

                <label for="dia_aplicacao_baycox1">Dia aplicação Baycox 1:</label>
                <input type="number" id="dia_aplicacao_baycox1" name="dia_aplicacao_baycox1" class="input-admin" value="<?php echo $dia_aplicacao_baycox1 !== '' ? htmlspecialchars($dia_aplicacao_baycox1) : '0'; ?>">

                <label for="dia_aplicacao_baycox2">Dia aplicação Baycox 2:</label>
                <input type="number" id="dia_aplicacao_baycox2" name="dia_aplicacao_baycox2" class="input-admin" value="<?php echo $dia_aplicacao_baycox2 !== '' ? htmlspecialchars($dia_aplicacao_baycox2) : '0'; ?>">

                <label for="dia_comportamento">Dia comportamento:</label>
                <input type="number" id="dia_comportamento" name="dia_comportamento" class="input-admin" value="<?php echo $dia_comportamento !== '' ? htmlspecialchars($dia_comportamento) : '0'; ?>">

                <input type="submit" value="Salvar configurações" class="btn">
            </form>
        </div>
        <div id="tab-acessibilidade" class="tab-content" style="display:none;">
            <h2>Opções de Acessibilidade</h2>
            <form method="POST" action="configuracoes.php">
                <label for="acessibilidade">Modo de acessibilidade:</label>
                <select name="acessibilidade" id="acessibilidade" class="input-admin">
                    <option value="padrão" <?php echo ($acessibilidade === 'padrão') ? 'selected' : ''; ?>>Padrão</option>
                    <option value="alto-contraste" <?php echo ($acessibilidade === 'alto-contraste') ? 'selected' : ''; ?>>Alto Contraste</option>
                    <option value="fonte-grande" <?php echo ($acessibilidade === 'fonte-grande') ? 'selected' : ''; ?>>Fonte Grande</option>
                </select>
                <input type="submit" value="Salvar acessibilidade" class="btn">
            </form>
        </div>
        <a href="dashboard.php" class="btn">Voltar ao painel</a>
    </div>

    <?php
    $titulo_ajuda = "Ajuda - Configurações";
    $descricao_ajuda = "Esta tela permite configurar opções de acessibilidade do sistema.";
    $itens_ajuda = [
        ['titulo' => 'Modo Padrão', 'descricao' => 'Interface padrão do sistema.'],
        ['titulo' => 'Alto Contraste', 'descricao' => 'Melhora a visibilidade para usuários com baixa visão.'],
        ['titulo' => 'Fonte Grande', 'descricao' => 'Aumenta o tamanho da fonte para facilitar a leitura.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: As configurações são salvas na sessão do usuário.";
    include '../include/modal_ajuda.php';
    ?>

    <?php include '../include/footer.php'; ?>
    <script>
        function showTab(tab) {
            document.getElementById('tab-sistema').style.display = tab === 'sistema' ? 'block' : 'none';
            document.getElementById('tab-acessibilidade').style.display = tab === 'acessibilidade' ? 'block' : 'none';
            var btns = document.querySelectorAll('.tab-btn');
            btns[0].classList.toggle('active', tab === 'sistema');
            btns[1].classList.toggle('active', tab === 'acessibilidade');
        }
    </script>
</body>
</html>
