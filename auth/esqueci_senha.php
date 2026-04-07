<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusão dos arquivos necessários
require_once __DIR__ . '/../include/funcoes.php';

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
include_once __DIR__ . '/../database/conexao.php';

if ($conn === null) {
    mostrarMsg('Erro de conexão com o banco de dados.', 'erro', '../index.php');
}

$conn = $GLOBALS['conn'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!validarEmail($email)) {
        mostrarMsg('E-mail inválido.', 'atencao', 'esqueci_senha.php');
    }
    $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt2 = $conn->prepare('UPDATE usuarios SET reset_token = ?, reset_expira = ? WHERE email = ?');
        $stmt2->bind_param('sss', $token, $expira, $email);
        $stmt2->execute();
        $stmt2->close();
        // Envio de e-mail real com PHPMailer
        $mail = new PHPMailer(true);
        try {

             /* 
             ATENÇÃO: AS LINHAS ABAIXO PRECISAM SER CONFIGURADAS QUANDO TIVER
             O EMAIL E O DOMINIO CORRETOS A SEREM UTILIZADOS PARA RECUPERAÇÃO DE SENHA
             
            $mail->isSMTP();
            $mail->Host = 'EXEMPLO DE HOST: smtp.office365.com'; // COLOQUE O HOST DO SEU SERVIDOR DE E-MAIL
            $mail->SMTPAuth = true;
            $mail->Username = 'EXEMPLO DE EMAIL: fazendaetec@hotmail.com'; // COLOQUE O SEU EMAIL  
            $mail->Password = 'A SENHA DO SEU EMAIL VAI AQUI'; // COLOQUE A SENHA DO SEU EMAIL
            $mail->SMTPSecure = 'tls'; // ou 'ssl'
            $mail->Port = 587; // ou 465 para SSL

            $mail->setFrom('seuemail@dominio.com', 'Sistema Fazenda Etec'); // COLOQUE O EMAIL DO REMETENTE
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de senha';
            $mail->Body = 'Olá!<br>Recebemos uma solicitação para redefinir sua senha.<br>
                Clique no link abaixo para criar uma nova senha:<br>
                <a href="https://seusite.com/auth/redefinir_senha.php?token=' . $token . '">Redefinir senha</a><br>
                Se não foi você, ignore este e-mail.';

            $mail->send();
            echo '<p>Um link de recuperação foi enviado para seu e-mail.</p>';

            */
            mostrarMsg('Este serviço ainda não foi implantado no sistema', 'atencao', '../index.php');
        } catch (Exception $e) {
            mostrarMsg('Erro ao enviar e-mail.', 'erro', 'esqueci_senha.php');
        }
    } else {
        mostrarMsg('E-mail não encontrado.', 'erro', 'esqueci_senha.php');
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recuperar senha</title>
    <link rel="stylesheet" href="../assets/css/form.css">
</head>
<body>
    <div class="container">
        <h2>Recuperar senha</h2>
        <form action="esqueci_senha.php" method="POST">
            <label for="email">Informe seu e-mail cadastrado:</label>
            <input type="email" id="email" name="email" required>
            <input type="submit" value="Enviar link de recuperação">
        </form>
        <a href="../index.php">Voltar ao login</a>
    </div>
</body>
</html>
