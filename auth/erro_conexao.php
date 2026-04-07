<?php
session_start();
$mensagem = isset($_SESSION['db_error']) ? $_SESSION['db_error'] : "Erro desconhecido.";
unset($_SESSION['db_error']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Erro de Conexão</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff3f3;
            color: #a00;
            text-align: center;
            padding: 50px;
        }
        .erro-box {
            border: 2px solid #a00;
            background-color: #ffe0e0;
            padding: 30px;
            display: inline-block;
            border-radius: 8px;
        }
        .erro-box h1 {
            margin-top: 0;
        }
        .botao-voltar {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #a00;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .botao-voltar:hover {
            background-color: #800;
        }
    </style>
</head>
<body>
    <div class="erro-box">
        <h1>Erro de Conexão</h1>
        <p><?= htmlspecialchars($mensagem) ?></p>
        <a href="../index.php" class="botao-voltar">Voltar para a tela de entrada</a>
    </div>
</body>
</html>
