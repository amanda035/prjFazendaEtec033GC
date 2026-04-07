<?php
    $titulo_pagina = "Bem-vindo à tela de Atividades do Sistema";

    include("../auth/auth.php");
    include("../include/header.php");


?>
<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // session_start();

    include("../database/conexao.php");

    date_default_timezone_set('America/Sao_Paulo');
    $dataHoje = date('Y-m-d');
    $dataLimite = date('Y-m-d', strtotime('+5 days'));

    $atividades = [];

    // Função auxiliar para adicionar atividade
    function adicionarAtividade(&$atividades, $descricao, $dataPrevista, $dataEfetiva) {
        global $dataHoje;

        // Se não houver data prevista, não há como gerar atividade
        if (!$dataPrevista) return;

        $dataPrevistaObj = new DateTime($dataPrevista);
        $dataHojeObj = new DateTime($dataHoje);

        if (!empty($dataEfetiva)) {
            $status = 'REALIZADA';
            $corBase = 'verde';
            $dataAcao = $dataEfetiva;
        } else {
            if ($dataPrevistaObj < $dataHojeObj) {
                $status = 'ATRASADA';
                $corBase = 'vermelha';
            } else {
                $status = 'PREVISTA';
                $corBase = 'amarela';
            }
            $dataAcao = $dataPrevista;
        }

        // Define classe CSS com base no tipo de atividade
        $classe = $corBase;

        $atividades[] = [
            'descricao' => $descricao,
            'status' => $status,
            'classe' => $classe,
            'data_acao' => $dataAcao
        ];
    }

    // Exemplo com a tabela de partos
    $sql = "SELECT 
                p.id, 
                p.matriz_id, 
                m.nome AS nome_matriz,
                p.data_prevista_parto, 
                p.data_efetiva_parto, 
                p.data_prevista_desmame, 
                p.data_efetiva_desmame, 
                p.data_prevista_maternidade, 
                p.data_efetiva_maternidade 
            FROM partos p
            JOIN matrizes m ON p.matriz_id = m.id";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $matriz = $row['nome_matriz'];

        adicionarAtividade($atividades, "Verificar parto da matriz ($matriz)", $row['data_prevista_parto'], $row['data_efetiva_parto']);
        adicionarAtividade($atividades, "Verificar desmame da matriz ($matriz)", $row['data_prevista_desmame'], $row['data_efetiva_desmame']);
        adicionarAtividade($atividades, "Transferir matriz ($matriz) para maternidade", $row['data_prevista_maternidade'], $row['data_efetiva_maternidade']);
    }

    // Outras tabelas podem ser adicionadas aqui com lógica semelhante...

?>

<!-- HTML da tabela -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Atividades Pendentes</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">

    <style>

/* Zebra striping for table rows */
#tabelaAtividades tbody tr:nth-child(odd) {
    background-color: #ffffff;
}
#tabelaAtividades tbody tr:nth-child(even) {
    background-color: #d4edda;
}

/* Font color based on status */
.status-realizada {
    color:rgb(0, 133, 0);
    font-weight: bold;
}
.status-prevista {
    color:rgb(0, 0, 255);
    font-weight: bold;
}
.status-atrasada {
    color:rgb(255, 0, 0);
    font-weight: bold;
}
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        .paginacao {
            margin-top: 15px;
            text-align: center;
        }
        .paginacao button {
            margin: 0 5px;
            padding: 5px 10px;
            cursor: pointer;
        }

        th.sortable {
            cursor: pointer;
            user-select: none;
        }
        th.sortable::after {
            content: ' ⇅';
            font-size: 0.8em;
            color: #888;
        }

    </style>

</head>
<body>

    <div style="margin-bottom: 15px;">
        <label for="filtroStatus">Status:</label>
        <select id="filtroStatus">
            <option value="">Todos</option>
            <option value="ATRASADA">ATRASADA</option>
            <option value="PREVISTA">PREVISTA</option>
            <option value="REALIZADA">REALIZADA</option>
        </select>

        <label for="filtroDescricao" style="margin-left: 15px;">Descrição:</label>
        <input type="text" id="filtroDescricao" placeholder="Buscar...">

        <label for="filtroData" style="margin-left: 15px;">Data Ação:</label>
        <input type="date" id="filtroData">
    </div>

    <table id="tabelaAtividades">
        <thead>
            <tr>
                <th class="sortable">Nro Atividade</th>
                <th class="sortable">Descrição</th>
                <th class="sortable">Status</th>
                <th class="sortable">Data Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($atividades as $index => $atividade): ?>
<tr>
                    <td><?= str_pad($index + 1, 3, '0', STR_PAD_LEFT) ?></td>
                    <td><?= $atividade['descricao'] ?></td>
<td><span class="<?php
    echo strtolower('status-' . $atividade['status']);
?>"><?= $atividade['status'] ?></span></td>
                    <td><?= date('Y-m-d', strtotime($atividade['data_acao'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="paginacao" id="paginacao"></div>

    <div style="margin-top: 20px; text-align: center;">
        <form action="dashboard.php" method="get">
            <button type="submit">Ir para o Dashboard</button>
        </form>
    </div>


    <script>
        const filtroStatus = document.getElementById('filtroStatus');
        const filtroDescricao = document.getElementById('filtroDescricao');
        const filtroData = document.getElementById('filtroData');
        const tabela = document.getElementById('tabelaAtividades').getElementsByTagName('tbody')[0];
        const paginacao = document.getElementById('paginacao');

        let linhasPorPagina = 18;
        let paginaAtual = 1;

        
        function filtrarTabela() {
            const status = filtroStatus.value.toUpperCase();
            const descricao = filtroDescricao.value.toLowerCase();
            const data = filtroData.value;
            const linhas = Array.from(tabela.rows);

            linhas.forEach(row => {
                const cellDescricao = row.cells[1].textContent.toLowerCase();
                const cellStatus = row.cells[2].textContent.toUpperCase();
                const cellData = row.cells[3].textContent;

                const matchStatus = !status || cellStatus === status;
                const matchDescricao = !descricao || cellDescricao.includes(descricao);
                const matchData = !data || cellData === data;

                row.dataset.visible = (matchStatus && matchDescricao && matchData) ? "true" : "false";
            });

            paginaAtual = 1;
            aplicarPaginacao();
        }

        function aplicarPaginacao() {
            const linhas = Array.from(tabela.rows).filter(row => row.dataset.visible === "true");
            const totalPaginas = Math.ceil(linhas.length / linhasPorPagina);

            Array.from(tabela.rows).forEach(row => row.style.display = "none");

            linhas.forEach((row, index) => {
                if (index >= (paginaAtual - 1) * linhasPorPagina && index < paginaAtual * linhasPorPagina) {
                row.style.display = "";
                }
            });

            paginacao.innerHTML = '';
            for (let i = 1; i <= totalPaginas; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                if (i === paginaAtual) btn.disabled = true;
                    btn.addEventListener('click', () => {
                    paginaAtual = i;
                    aplicarPaginacao();
                });
                paginacao.appendChild(btn);
            }
        }

        window.onload = () => {
            Array.from(tabela.rows).forEach(row => row.dataset.visible = "true");
            aplicarPaginacao();
        };

        // Adiciona os eventos de filtro
        filtroStatus.addEventListener('change', filtrarTabela);
        filtroDescricao.addEventListener('input', filtrarTabela);
        filtroData.addEventListener('change', filtrarTabela);

        document.querySelectorAll("th.sortable").forEach((header, columnIndex) => {
            let asc = true;
            header.addEventListener("click", () => {
                const tbody = document.querySelector("#tabelaAtividades tbody");
                const rows = Array.from(tbody.querySelectorAll("tr")).filter(row => row.style.display !== "none");

                rows.sort((a, b) => {
                    const cellA = a.cells[columnIndex].textContent.trim();
                    const cellB = b.cells[columnIndex].textContent.trim();

                    // Tenta converter para data
                    const dateA = Date.parse(cellA);
                    const dateB = Date.parse(cellB);

                    if (!isNaN(dateA) && !isNaN(dateB)) {
                        return asc ? dateA - dateB : dateB - dateA;
                    }

                    // Tenta converter para número
                    const numA = parseFloat(cellA);
                    const numB = parseFloat(cellB);
                    if (!isNaN(numA) && !isNaN(numB)) {
                        return asc ? numA - numB : numB - numA;
                    }

                    // Ordenação alfabética
                    return asc ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
                });

                rows.forEach(row => tbody.appendChild(row));
                asc = !asc;
            });
        });

    </script>

<?php
$titulo_ajuda = "Ajuda - Tela de Atividades";
$descricao_ajuda = "Esta tela exibe uma lista de atividades previstas, realizadas ou atrasadas.";
$itens_ajuda = [
    ['titulo' => 'Filtrar', 'descricao' => 'Permite buscar atividades por status, descrição ou data.'],
    ['titulo' => 'Ordenar', 'descricao' => 'Clique nos títulos das colunas para ordenar.'],
    ['titulo' => 'Dashboard', 'descricao' => 'Retorna para o painel principal.']
];
$observacao_ajuda = "OBSERVAÇÃO: As atividades são geradas automaticamente com base nos dados dos partos.";
include '../include/modal_ajuda.php';
?>

<?php include '../include/footer.php'; ?>
<script src="../assets/js/ordenaTabela.js"></script>

</body>
</html>


