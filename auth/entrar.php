<?php
// Verifica se existe uma sessão aberta, se não existir, inicia uma nova sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("../database/conexao.php");
include("../include/funcoes.php");

if ($conn === null) {
    // Redirecionar ou mostrar aviso
    mostrarMsg("O sistema está temporariamente indisponível para login devido a problemas de conexão com o banco de dados.", 'erro', '../index.php');
    // exit; // Só use se quiser impedir ações específicas
    echo("11");
} else {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Verificar se tem todos os dados obrigatórios
        if (!isset($_POST["usuario"], $_POST["senha"])) {
            mostrarMsg("Campos obrigatórios para login não enviados.", 'erro', '../index.php');
            echo("3");
        }

        //Sanitizar os dados recebidos por POST
        $usuario = strip_tags(trim($_POST['usuario']));
        $senha = strip_tags(trim($_POST['senha']));

        // Validar usuário que vem do formulário
        if (!validarUsuario($usuario) == true) {
            mostrarMsg("Nome de usuário '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "' inválido.", 'atencao', '../index.php');
            echo("2");
        }

        //Validar senha que vem do formulário
        if (!validarSenha($senha) == true) {
            mostrarMsg("Senha informada para login do usuário '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "' inválida.", 'atencao', '../index.php');
            echo("1");
        }

        // Validar para saber se os dados chegaram corretamente depois das validações
        if (!empty($usuario) && !empty($senha)) {
            try {
                $select = "SELECT id, nome, email, nivel_acesso, senha FROM usuarios WHERE nome = ?";
                $stmt = $conn->prepare($select);
                $stmt->bind_param("s", $usuario);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows() <= 0) {
                    mostrarMsg("Usuário '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "' ou senha inválidos.", 'erro', '../index.php');
                    echo("4");
                }
                $stmt->bind_result($id, $usuario_db, $email, $nivel_acesso, $senha_db);
                if ($stmt->fetch()) {

                    if (!empty($usuario_db) && password_verify($senha, $senha_db)) {
                        $_SESSION['usuario_id'] = $id;
                        $_SESSION['nome'] = $usuario_db;
                        $_SESSION['email'] = $email;
                        $_SESSION['nivel_acesso'] = $nivel_acesso;
                        $_SESSION['loggedin'] = true;

                           // LEMBRAR PRA MIM
                           if (isset($_POST['lembrar'])) {
                               $token = bin2hex(random_bytes(32));
                               setcookie('lembrar_token', $token, time() + (86400 * 30), '/', '', false, true); // 30 dias
                               // Salve o token no banco vinculado ao usuário
                               $stmtToken = $conn->prepare("UPDATE usuarios SET lembrar_token = ? WHERE id = ?");
                               $stmtToken->bind_param("si", $token, $id);
                               $stmtToken->execute();
                               $stmtToken->close();
                           }

                        header("Location: ../admin/atividades.php");
                        //exit;
                        echo("5");
                    } else {
                        mostrarMsg("Usuário '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "' ou senha incorretos.", 'erro', '../index.php');
                        echo("6");
                    }
                } else {
                    mostrarMsg("Os parâmetros para login do usuário '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "' não chegaram corretamente! fetch", 'erro', '../index.php');
                    echo("7");
                }
                $stmt->close();
            } catch (Exception $erro) {
                mostrarMsg("Erro ao tentar login do usuário '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "': " . $erro->getCode(), 'erro', '../index.php');
                echo("8");
            }
        } else {
            mostrarMsg("Os parâmetros para login do usuário '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "' não chegaram corretamente!", 'erro', '../index.php');
            echo("9");
        }
    } else {
    mostrarMsg("Método inválido para login do usuário '" . (isset($_POST['nome']) ? $_POST['nome'] : '') . "'!", 'erro', '../index.php');
    echo("10");
    }

    $conn->close();
}
?>
