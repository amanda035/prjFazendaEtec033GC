<?php
$titulo_pagina = "Bem-vindo à tela de Editar Alimento";
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
    $tipo_alimento = trim($_POST['tipo_alimento']);

    $stmt = $conn->prepare("UPDATE alimentos SET nome = ?, descricao = ?, tipo_alimento = ?, usuario_atualizacao_id = ?, data_atualizacao = NOW() WHERE id = ?");
    if ($stmt === false) {
        mostrarMsg("Erro na preparação da declaração do alimento $nome.", 'erro', '/prjFazendaEtec033GC/admin/manejos/alimentos/alimentos_select.php');
    }

    $stmt->bind_param("ssssi", $nome, $descricao, $tipo_alimento, $usuario_id, $id);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao atualizar alimento $nome: " . $stmt->error, 'erro', '/prjFazendaEtec033GC/admin/manejos/alimentos/alimentos_select.php');
    }

    // Buscar o id do tipo de ação "alteracao"
    $tipo_acao_id = null;
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_acao WHERE nome = ? LIMIT 1");
    if ($stmt_tipo === false) {
        mostrarMsg("Erro ao buscar tipo de ação 'alteracao' para alimento $nome: " . $conn->error, 'atencao', '/prjFazendaEtec033GC/admin/manejos/alimentos/alimentos_select.php');
        exit;
    }
    $nome_acao = 'alteracao';
    $stmt_tipo->bind_param("s", $nome_acao);
    $stmt_tipo->execute();
    $stmt_tipo->bind_result($tipo_acao_id);
    $stmt_tipo->fetch();
    $stmt_tipo->close();

    // Registrar log
    $tabela = 'alimentos';
    $stmt_log = $conn->prepare("INSERT INTO logs (usuario_id, tabela, tipo_acao_id, data_acao) VALUES (?, ?, ?, NOW())");
    if ($stmt_log === false) {
        mostrarMsg("Erro ao preparar log para alimento $nome: " . $conn->error, 'atencao', '/prjFazendaEtec033GC/admin/manejos/alimentos/alimentos_select.php');
        exit;
    }
    $stmt_log->bind_param("isi", $usuario_id, $tabela, $tipo_acao_id);
    if (!$stmt_log->execute()) {
        mostrarMsg("Alimento $nome editado, mas não foi possível registrar o log.", 'atencao', '/prjFazendaEtec033GC/admin/manejos/alimentos/alimentos_select.php');
        exit;
    }
    $stmt_log->close();

    $stmt->close();
    $stmt_log->close();
    $conn->close();

    mostrarMsg("Alimento $nome editado com sucesso!", 'acerto', '/prjFazendaEtec033GC/admin/manejos/alimentos/alimentos_select.php');
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM alimentos WHERE id = ?");
    if ($stmt === false) {
        mostrarMsg("Erro na preparação da consulta: " . $conn->error, 'erro', '/prjFazendaEtec033GC/admin/manejos/alimentos/alimentos_select.php', "Falha ao preparar SELECT do alimento");
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        mostrarMsg("Erro ao buscar alimento: " . $stmt->error, 'erro', '/prjFazendaEtec033GC/admin/manejos/alimentos/alimentos_select.php', "Falha ao executar SELECT do alimento");
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if (!$row) {
        mostrarMsg("Alimento não encontrado.", 'atencao', '/prjFazendaEtec033GC/admin/manejos/alimentos/alimentos_select.php', "ID informado: $id");
    }
} else {
    mostrarMsg("ID do alimento não fornecido.", 'atencao', '/prjFazendaEtec033GC/admin/manejos/alimentos/alimentos_select.php', "Nenhum ID recebido");
}
?>
