<?php
$titulo_pagina = "Bem-vindo à tela de Editar Procedimento";
include("../../../auth/auth.php");
include("../../../include/funcoes.php");
include("../../../database/conexao.php");
global $conn;

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../../auth/entrar.php");
    exit();
}

$usuario_id = intval($_SESSION['usuario_id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);

    $stmt = $conn->prepare("UPDATE procedimentos SET nome = ?, descricao = ?, usuario_atualizacao_id = ?, data_atualizacao = NOW() WHERE id = ?");
    if ($stmt === false) {
        mostrarMsg("Erro na preparação da declaração do procedimento $nome.", 'erro', '/prjFazendaEtec033/admin/manejos/procedimentos/procedimentos_select.php');
    }

    $stmt->bind_param("ssii", $nome, $descricao, $usuario_id, $id);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao atualizar procedimento $nome: " . $stmt->error, 'erro', '/prjFazendaEtec033/admin/manejos/procedimentos/procedimentos_select.php');
    }

    // Buscar o id do tipo de ação "alteracao"
    $tipo_acao_id = null;
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
    if ($stmt_tipo === false) {
        mostrarMsg("Erro ao buscar tipo de ação 'alteracao' para procedimento $nome: " . $conn->error, 'atencao', '/prjFazendaEtec033/admin/manejos/procedimentos/procedimentos_select.php');
        exit;
    }
    $nome_acao = 'alteracao';
    $stmt_tipo->bind_param("s", $nome_acao);
    $stmt_tipo->execute();
    $stmt_tipo->bind_result($tipo_acao_id);
    $stmt_tipo->fetch();
    $stmt_tipo->close();

    // Registrar log
    $tabela = 'procedimentos';
    $stmt_log = $conn->prepare("INSERT INTO logs (usuario_id, tabela, tipo_acao_id, data_acao) VALUES (?, ?, ?, NOW())");
    if ($stmt_log === false) {
        mostrarMsg("Erro ao preparar log para procedimento $nome: " . $conn->error, 'atencao', '/prjFazendaEtec033/admin/manejos/procedimentos/procedimentos_select.php');
        exit;
    }
    $stmt_log->bind_param("isi", $usuario_id, $tabela, $tipo_acao_id);
    if (!$stmt_log->execute()) {
        mostrarMsg("Procedimento $nome editado, mas não foi possível registrar o log.", 'atencao', '/prjFazendaEtec033/admin/manejos/procedimentos/procedimentos_select.php');
        exit;
    }
    $stmt_log->close();

    $stmt->close();
    $stmt_log->close();
    $conn->close();

    mostrarMsg("Procedimento $nome editado com sucesso!", 'acerto', '/prjFazendaEtec033/admin/manejos/procedimentos/procedimentos_select.php');
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM procedimentos WHERE id = ?");
    if ($stmt === false) {
        mostrarMsg("Erro na preparação da consulta: " . $conn->error, 'erro', '/prjFazendaEtec033/admin/manejos/procedimentos/procedimentos_select.php', "Falha ao preparar SELECT do procedimento");
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao buscar procedimento: " . $stmt->error, 'erro', '/prjFazendaEtec033/admin/manejos/procedimentos/procedimentos_select.php', "Falha ao executar SELECT do procedimento");
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if (!$row) {
        mostrarMsg("Procedimento não encontrado.", 'atencao', '/prjFazendaEtec033/admin/manejos/procedimentos/procedimentos_select.php', "ID informado: $id");
    }
} else {
    mostrarMsg("ID do procedimento não fornecido.", 'atencao', '/prjFazendaEtec033/admin/manejos/procedimentos/procedimentos_select.php', "Nenhum ID recebido");
}
?>
