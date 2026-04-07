<?php
// Verifico se a sessão já foi iniciada, se não, inicio a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações de conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bd_fazendagc";

// Configura o MySQLi para lançar exceções em caso de erro
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Inicializa a variável de conexão
$conn = null;

// Tenta conectar ao banco de dados
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $GLOBALS['conn'] = $conn; // Torna a conexão globalmente acessível
} catch (mysqli_sql_exception $e) { // Captura exceções de conexão
    $GLOBALS['conn'] = null; // Garante que a conexão global seja nula em caso de erro

    // Exibe modal de erro diretamente
    $msg_tipo = 'erro';
    $msg_texto = "⚠️ Não foi possível conectar ao banco de dados. Verifique a conexão ou contate o suporte.";
    include_once(__DIR__ . '/../include/modal_msg.php');
    mostrarMsg($msg_texto, $msg_tipo, '/prjFazendaEtec033/index.php');
    }
?>
