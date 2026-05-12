<?php
session_start();


// Configuração de tipos de mensagem
$tipos = [
    'erro'   => ['titulo' => 'Ocorreu um erro',   'classe' => 'erro'],
    'acerto' => ['titulo' => 'Sucesso!',          'classe' => 'acerto'],
    'atencao'=> ['titulo' => 'Atenção',           'classe' => 'atencao']
];

// Parâmetros para ações (pode ser expandido)
$acoes = [
    'voltar' => [
        'label' => 'Voltar',
        'class' => 'botao-voltar',
        'icon'  => '', // pode adicionar ícone se quiser
    ]
];

if (isset($_SESSION['mensagem_sistema'])) {
    $mensagem = $_SESSION['mensagem_sistema']['texto'];
    $tipo = $_SESSION['mensagem_sistema']['tipo'];
    $detalhes = isset($_SESSION['mensagem_sistema']['detalhes']) ? $_SESSION['mensagem_sistema']['detalhes'] : null;
    // Lógica centralizada para definir página de retorno
    if (isset($_SESSION['pagina_retorno']) && !empty($_SESSION['pagina_retorno'])) {
        $pagina_retorno = $_SESSION['pagina_retorno'];
    } else if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'exibir_msg.php') === false) {
        $pagina_retorno = $_SERVER['HTTP_REFERER'];
    } else {
        $pagina_retorno = 'prjFazendaEtec033GC/index.php';
    }
} else {
    $mensagem = "Erro desconhecido.";
    $tipo = "erro";
    $pagina_retorno = 'prjFazendaEtec033GC/index.php';
}
unset($_SESSION['mensagem_sistema'], $_SESSION['pagina_retorno']);
$config = isset($tipos[$tipo]) ? $tipos[$tipo] : $tipos['erro'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($config['titulo']) ?></title>
    <link rel="stylesheet" href="../assets/css/estilo_msg.css">
</head>
<body>
    <div class="erro-box <?= $config['classe'] ?>">
        <h1><?= htmlspecialchars($config['titulo']) ?></h1>
        <p><?= htmlspecialchars($mensagem) ?></p>
        <?php if (!empty($detalhes)): ?>
            <div class="msg-detalhes"> <?= htmlspecialchars($detalhes) ?> </div>
        <?php endif; ?>
        <a href="<?= htmlspecialchars($pagina_retorno) ?>" class="<?= $acoes['voltar']['class'] ?>"><?= $acoes['voltar']['label'] ?></a>
        <!-- <div style="margin-top:20px;font-size:13px;color:#555;background:#eee;padding:5px 10px;border-radius:5px;">
            <b>DEBUG:</b> Valor de $pagina_retorno: <span style="color:#007700;"> <? /*= htmlspecialchars($pagina_retorno)*/ ?> </span>
        </div> -->
    </div>
</body>
</html>
