<?php

// Inclui a conexão com o banco de dados
require_once(__DIR__ . '/../database/conexao.php');

// Busca o tipo_acao_id pelo nome da ação
function buscarTipoAcaoId($conn, $nome_acao) {
	$tipo_acao_id = null;
	$stmt = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
	if ($stmt) {
		$stmt->bind_param("s", $nome_acao);
		$stmt->execute();
		$stmt->bind_result($tipo_acao_id);
		$stmt->fetch();
		$stmt->close();
	}
	return $tipo_acao_id;
}

// Registra log de ação
function registrarLog($conn, $usuario_id, $tabela, $nome_acao) {
	$tipo_acao_id = buscarTipoAcaoId($conn, $nome_acao);
	if ($tipo_acao_id) {
		$stmt = $conn->prepare("INSERT INTO logs (usuario_id, tabela, tipo_acao_id, data_acao) VALUES (?, ?, ?, NOW())");
		if ($stmt) {
			$stmt->bind_param("isi", $usuario_id, $tabela, $tipo_acao_id);
			$stmt->execute();
			$stmt->close();
		}
	}
}

// Busca o nome da matriz pelo ID
function buscarNomeMatriz($conn, $id) {
	$nome = null;
	$stmt = $conn->prepare("SELECT nome FROM matrizes WHERE id = ? LIMIT 1");
	if ($stmt) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->bind_result($nome);
		$stmt->fetch();
		$stmt->close();
	}
	return $nome;
}

// Valida o nome de usuário
function validarUsuario($usuario) {
	return preg_match('/^[a-zA-Z0-9]{3,20}$/', $usuario);
}

// Valida a senha
function validarSenha($senha) {
	return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,20}$/', $senha);
}

// Valida o email
function validarEmail($email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Verifica se o nome de usuário ou email já estão cadastrados
function verificarContaExiste($usuario, $email) {
	global $conn;
	$stmt = $conn->prepare("SELECT id FROM usuarios WHERE nome = ? OR email = ?");
	$stmt->bind_param("ss", $usuario, $email);
	$stmt->execute();
	$stmt->store_result();
	$existe = $stmt->num_rows > 0;
	$stmt->close();
	return $existe;
}

// Verifica se já existe algum usuário no sistema
 function verificarUsuariosExistentes() {
	global $conn;
	$sql = "SELECT COUNT(*) as total FROM usuarios";
	$resultado = $conn->query($sql);
	if (!$resultado) return false;
	$dados = $resultado->fetch_assoc();
	return $dados['total'] > 0;
}

// Gera token CSRF
function gerarCSRF() {
	if (!isset($_SESSION["csrf"])) {
		$_SESSION["csrf"] = hash('sha256', openssl_random_pseudo_bytes(32));
	}
	return $_SESSION["csrf"];
}

// Valida token CSRF
function validarCSRF($csrf) {
	return isset($_SESSION["csrf"]) && hash_equals($_SESSION["csrf"], $csrf);
}

// Exibe mensagem de sistema
function mostrarMsg($texto, $tipo = 'erro', $retorno = null, $detalhes = null) {
	global $msg_tipo, $msg_texto, $msg_detalhes;
	$msg_tipo = $tipo;
	$msg_texto = $texto;
	$msg_detalhes = $detalhes;

	echo($msg_texto);
}
?>
