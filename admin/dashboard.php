<?php
include("../database/conexao.php");

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}

$titulo_pagina = "Bem-vindo à tela de Menu Principal";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fazenda Etec</title>
    <link rel="stylesheet" href="../assets/css/atividades_padronizado.css">
    <style>
        /* Container que organiza os grupos de menus na horizontal */
        .container-nav {
            display: flex;                     /* Usa Flexbox para alinhar os itens */
            justify-content: center;           /* Centraliza os itens no container */
            gap: 50px;                         /* Espaço horizontal entre os grupos */
            margin-top: 0;                     /* Remove margem superior */
            flex-wrap: wrap;                   /* Permite que itens quebrem linha se não couberem */
        }

        /* Define a posição relativa para que os submenus possam ser posicionados em relação a esse elemento */
        .grupo {
            position: relative;
        }

        /* Estilo para os títulos dos grupos (os botões de cada menu) */
        .grupo h3 {
            background-color: #4CAF50;       /* Cor de fundo verde */
            color: white;                      /* Cor do texto branca */
            padding: 5px;                      /* Espaçamento interno: 5px em cima/baixo e nas laterais */
            border-radius: 6px;                /* Bordas arredondadas */
            cursor: pointer;                   /* Cursor de mãozinha ao passar o mouse */
            margin: 0;                         /* Remove margem padrão */
            font-size: 16px;                   /* Tamanho da fonte */
            text-align: center;                /* Centraliza o texto */
            white-space: nowrap;               /* Evita quebra de linha no título */
        }

        /* Define o submenu escondido inicialmente */
        .grupo ul {
            display: none;                     /* Esconde a lista inicialmente */
            position: absolute;                /* Posiciona de forma absoluta, em relação ao .grupo */
            top: 105%;                         /* Posiciona logo abaixo do título (5% a mais do que a altura) */
            left: 0;                           /* Alinha à esquerda */
            background-color: #f9f9f9;         /* Cor de fundo clara */
            padding: 0;                        /* Remove padding padrão */
            margin: 0;                         /* Remove margem padrão */
            list-style: none;                  /* Remove os marcadores de lista */
            box-shadow: 0 4px 8px rgba(0,0,0,0.2); /* Adiciona sombra ao submenu */
            border-radius: 6px;                /* Bordas arredondadas */
            min-width: 100%;                  /* Largura mínima do submenu */
            z-index: 10;                       /* Garante que o submenu fique acima dos outros elementos */
        }

        /* Define cada item da lista de submenu */
        .grupo ul li {
            border-bottom: 1px solid #ddd;     /* Linha separadora entre itens */
        }

        /* Estilo dos links dentro dos itens de submenu */
        .grupo ul li a {
            display: flexbox;                   /* Faz o link ocupar todo o espaço do item centralizado verticalmente */
            padding: 5px;                       /* Espaçamento interno acima/abaixo e nas laterais*/
            height: auto;                       /* Altura automática para se ajustar ao conteúdo */
            color: #333;                        /* Cor do texto */
            text-decoration: none;              /* Remove o sublinhado */
            transition: background-color 0.3s;  /* Animação suave na mudança de fundo */
        }

        /* Efeito ao passar o mouse sobre os links */
        .grupo ul li a:hover {
            background-color: #4CAF50;         /* Cor de fundo ao passar o mouse */
        }

        /* Mostra o submenu ao passar o mouse sobre o grupo */
        .grupo:hover ul {
            display: block;
        }

        /* Estiliza a seção onde fica a imagem */
        section {
            display: flex;                     /* Flexbox para centralizar */
            justify-content: center;           /* Centraliza horizontalmente */
            align-items: center;               /* Centraliza verticalmente */
            height: 75vh;                      /* Altura de 75% da viewport */
        }

        img {
            max-width: 100%;                   /* Imagem ocupa no máximo 100% da largura do container */
            height: auto;                      /* Altura proporcional */
            border-radius: 8px;                /* Bordas arredondadas na imagem */
            box-shadow: 0 4px 8px rgba(0,0,0,0.2); /* Adiciona sombra ao submenu */
        }

        /* Responsividade para telas até 1024px de largura */
        @media (max-width: 1024px) {
            .container-nav {
                gap: 20px;                     /* Diminui o espaço entre os grupos */
            }

            .grupo h3 {
                font-size: 14px;               /* Diminui o tamanho da fonte */
                padding: 6px 10px;             /* Ajusta o padding para economizar espaço */
            }

            .grupo ul {
                min-width: 150px;              /* Diminui a largura mínima do submenu */
            }
        }

        /* Responsividade para telas até 768px de largura */
        @media (max-width: 768px) {
            .container-nav {
                flex-direction: column;        /* Empilha os grupos na vertical */
                align-items: center;           /* Centraliza os grupos */
                gap: 10px;                     /* Espaço entre grupos */
            }

            .grupo ul {
                position: static;              /* Remove o posicionamento absoluto */
                display: none;                 /* Esconde os submenus inicialmente */
                box-shadow: none;              /* Remove a sombra */
                border: 1px solid #ccc;        /* Adiciona borda simples */
                border-radius: 6px;            /* Bordas arredondadas */
                width: 100%;                   /* Ocupa toda a largura do container pai */
                margin-top: 5px;               /* Espaço entre o título e o submenu */
            }

            .grupo:hover ul {
                display: block;                /* Mostra o submenu ao passar o mouse (ou ao tocar em dispositivos touch) */
            }

            .grupo ul li a {
                padding: 8px 10px;             /* Ajuste no padding dos itens */
            }

            section {
                height: auto;                  /* Altura automática para se ajustar ao conteúdo */
                padding: 20px;                 /* Espaço interno */
            }

            section img {
                max-width: 90%;                /* Imagem ocupa no máximo 90% da largura disponível */
                height: auto;                  /* Altura proporcional */
            }
        }
    </style>
</head>
<body>
<div class="container">
    <?php include '../include/header.php'; ?>

    <nav>
        <div class="container-nav">
            <div class="grupo">
                <h3>Manejos</h3>
                <ul>
                    <li><a href="manejos/matrizes/matrizes_select.php">Matrizes</a></li>
                    <li><a href="manejos/crias/crias_select.php">Crias</a></li>
                    <li><a href="manejos/vacinas/vacinas_select.php">Vacinas</a></li>
                    <li><a href="manejos/procedimentos/procedimentos_select.php">Procedimentos</a></li>
                    <li><a href="manejos/alimentos/alimentos_select.php">Alimentos</a></li>
                </ul>
            </div>

            <div class="grupo">
                <h3>Manejos Especiais</h3>
                <ul>
                    <li><a href="manejos_especiais/partos/partos_select.php">Partos</a></li>
                    <li><a href="manejos_especiais/coberturas/coberturas_select.php">Coberturas</a></li>
                </ul>
            </div>

            <div class="grupo">
                <h3>Vacinação</h3>
                <ul>
                    <li><a href="vacinacao/vacinas_matrizes_select.php">De Matrizes</a></li>
                    <li><a href="vacinacao/vacinas_crias_select.php">De Crias</a></li>
                </ul>
            </div>

            <div class="grupo">
                <h3>Procedimentos</h3>
                <ul>
                    <li><a href="procedimentos/procedimentos_matrizes_select.php">Em Matrizes</a></li>
                    <li><a href="procedimentos/procedimentos_crias_select.php">Em Crias</a></li>
                </ul>
            </div>

            <div class="grupo">
                <h3>Nutrição</h3>
                <ul>
                    <li><a href="nutricao/nutricao_matrizes_select.php">De Matrizes</a></li>
                    <li><a href="nutricao/nutricao_crias_select.php">De Crias</a></li>
                </ul>
            </div>

            <div class="grupo">
                <h3>Pesagem</h3>
                <ul>
                    <li><a href="pesagem/pesagem_matrizes_select.php">De Matrizes</a></li>
                    <li><a href="pesagem/pesagem_crias_select.php">De Crias</a></li>
                </ul>
            </div>

            <?php
            if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == '0') {
                echo '
                    <div class="grupo">
                        <h3>Configurações</h3>
                        <ul>
                            <li><a href="../admin/configuracoes.php">Configurações do Sistema</a></li>
                            <li><a href="../admin/logs.php">Visualizar Logs do Sistema</a></li>
                        </ul>
                    </div>
                ';
            }
            ?>
        </div>
    </nav>
    <section>
        <img src="../public/suino.webp" alt="porquinho">
    </section>

    <?php
    $titulo_ajuda = "Ajuda - Tela de Menu Principal";
    $descricao_ajuda = "Esta tela exibe uma lista de opções de acesso ao sistema.";
    $itens_ajuda = [
        ['titulo' => 'Manejos', 'descricao' => 'Usado para fazer os registros básicos do sistema.'],
        ['titulo' => 'Vacinação', 'descricao' => 'Permite aplicar as vacinas cadastradas em matrizes ou crias.'],
        ['titulo' => 'Manejos Especiais', 'descricao' => 'Registra partos e coberturas de uma matriz.'],
        ['titulo' => 'Procedimentos', 'descricao' => 'Registra outros tipos de procedimentos feitos em matrizes ou crias.'],
        ['titulo' => 'Nutrição', 'descricao' => 'Mantém o registro de tratos feitos em matrizes ou crias.'],
        ['titulo' => 'Pesagem', 'descricao' => 'Mantém o registro de pesagem realizados em matrizes ou crias.'],
        ['titulo' => 'Configurações', 'descricao' => 'Permite alterar as configurações do sistema.'],
        ['titulo' => 'Voltar', 'descricao' => 'Retorna para a tela anterior.'],
        ['titulo' => 'Sair', 'descricao' => 'Encerra a sessão atual.']
    ];
    $observacao_ajuda = "OBSERVAÇÃO: As opções de acesso aparecem de acordo com o nível de permissão do usuário.";
    include '../include/modal_ajuda.php';
    ?>

    <?php include '../include/footer.php'; ?>
</div>
</body>
</html>
