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
                // A query agora seleciona as novas colunas
                $sql = "SELECT id, data_venda, valor_total, desconto, acrescimo FROM vendas ORDER BY data_venda DESC";
                $resultado = mysqli_query($conexao, $sql);

                if ($resultado && mysqli_num_rows($resultado) > 0) {
                    while ($venda = mysqli_fetch_assoc($resultado)) {
                        echo "<tr id='venda-" . $venda['id'] . "'>";
                        echo "<td>" . $venda['id'] . "</td>";
                        $data_formatada = date('d/m/Y H:i:s', strtotime($venda['data_venda']));
                        echo "<td>" . $data_formatada . "</td>";
                        // Exibe os novos valores formatados
                        echo "<td>" . number_format($venda['desconto'], 2, ',', '.') . "</td>";
                        echo "<td>" . number_format($venda['acrescimo'], 2, ',', '.') . "</td>";
                        echo "<td>" . number_format($venda['valor_total'], 2, ',', '.') . "</td>";
                        echo "<td>";
                        echo "<button onclick='excluirVenda(" . $venda['id'] . ")' class='btn-excluir'>Excluir</button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Nenhum registro de venda encontrado.</td></tr>";
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
