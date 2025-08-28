<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Vendas</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <h1>Histórico de Vendas</h1>
        <a href="index.php" class="btn">Voltar ao Início</a>
        <a href="pdv.php" class="btn" style="background-color: #007BFF;">Ir para o PDV</a>
        <table>
            <thead>
                <tr>
                    <th>ID da Venda</th>
                    <th>Itens da Venda</th>
                    <th>Data e Hora</th>
                    <th>Desconto (R$)</th>
                    <th>Acréscimo (R$)</th>
                    <th>Valor Total (R$)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'conexao.php';
                // Query principal para buscar todas as vendas
                $sql_vendas = "SELECT id_venda, data_venda, valor_total, desconto, acrescimo FROM vendas ORDER BY data_venda DESC";
                $resultado_vendas = mysqli_query($conexao, $sql_vendas);

                if ($resultado_vendas && mysqli_num_rows($resultado_vendas) > 0) {
                    while ($venda = mysqli_fetch_assoc($resultado_vendas)) {
                        echo "<tr id='venda-" . $venda['id_venda'] . "'>";
                        echo "<td>" . $venda['id_venda'] . "</td>";
                        
                        // --- LÓGICA PARA BUSCAR E EXIBIR OS ITENS DA VENDA ---
                        echo "<td>";
                        $id_venda_atual = $venda['id_venda'];
                        // Query para buscar os itens da venda atual, juntando com a tabela de produtos para obter o nome
                        $sql_itens = "SELECT vi.quantidade, p.nome_produto 
                                      FROM venda_itens AS vi 
                                      JOIN produtos AS p ON vi.id_produto = p.id_produto 
                                      WHERE vi.id_venda = ?";
                        
                        $stmt_itens = mysqli_prepare($conexao, $sql_itens);
                        mysqli_stmt_bind_param($stmt_itens, "i", $id_venda_atual);
                        mysqli_stmt_execute($stmt_itens);
                        $resultado_itens = mysqli_stmt_get_result($stmt_itens);
                        
                        if ($resultado_itens && mysqli_num_rows($resultado_itens) > 0) {
                            echo "<ul>";
                            while ($item = mysqli_fetch_assoc($resultado_itens)) {
                                echo "<li>" . $item['quantidade'] . "x " . htmlspecialchars($item['nome_produto']) . "</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "Nenhum item encontrado.";
                        }
                        echo "</td>";
                        // --- FIM DA LÓGICA DOS ITENS ---

                        $data_formatada = date('d/m/Y H:i:s', strtotime($venda['data_venda']));
                        echo "<td>" . $data_formatada . "</td>";
                        echo "<td>" . number_format($venda['desconto'], 2, ',', '.') . "</td>";
                        echo "<td>" . number_format($venda['acrescimo'], 2, ',', '.') . "</td>";
                        echo "<td>" . number_format($venda['valor_total'], 2, ',', '.') . "</td>";
                        echo "<td>";
                        echo "<button onclick='excluirVenda(" . $venda['id_venda'] . ")' class='btn-excluir'>Excluir</button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Nenhum registro de venda encontrado.</td></tr>";
                }
                mysqli_close($conexao);
                ?>
            </tbody>
        </table>
    </div>

    <script>
    function excluirVenda(idVenda) {
        if (confirm('Tem a certeza de que deseja excluir esta venda? Esta ação não pode ser desfeita e o stock dos produtos será reposto.')) {
            fetch(`api_vendas.php?acao=excluir_venda&id=${idVenda}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Venda excluída com sucesso!');
                    document.getElementById('venda-' + idVenda).remove();
                } else {
                    alert('Erro ao excluir a venda: ' + data.message);
                }
            });
        }
    }
    </script>
</body>
</html>
