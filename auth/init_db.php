<?php
$servername = "localhost";
$dbname = "fazenda_etec"; 
$username = "root";
$password = "";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Criptografar a senha do usuário administrador
$senhaCriptografada = password_hash('Etec123@', PASSWORD_DEFAULT);

// Inserir o usuário administrador no banco de dados
$stmt = $conn->prepare("INSERT INTO usuarios (nome, senha, email, nivel_acesso, data_criacao) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $nome, $senhaCriptografada, $email, $nivel_acesso);

// Definir os valores
$nome = 'adminEtec';
$email = 'admin@example.com';
$nivel_acesso = 0; // 0 para administrador

// Executar a inserção
$stmt->execute();

// Fechar a declaração e a conexão
$stmt->close();
$conn->close();

echo "Usuário administrador criado com sucesso!";
?>
