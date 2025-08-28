<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>V1 - Cadastro de Produtos</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <h1>Sistema de Gerenciamento de Estoque</h1>
        <h2>Produtos Cadastrados</h2>
        <a href="historico_vendas.php" class="btn" style="background-color: #17a2b8;">Histórico de Vendas</a>
        <a href="pdv.php" class="btn" style="background-color: #007BFF;">Ir para o PDV</a>
        <a href="gerenciar_produtos.php?acao=adicionar" class="btn">Adicionar Novo Produto</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome do Produto</th>
                    <th>Estoque</th>
                    <th>Custo (R$)</th>
                    <th>Valor de Venda (R$)</th>
                    <th>Fornecedor</th>
                    <th>Cód. Barras</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'conexao.php';
                // Query SQL para selecionar todos os produtos
                // MODIFICADO: Seleciona 'id_produto'
                $sql = "SELECT id_produto, nome_produto, estoque_inicial, custo_produto, valor_venda_produto, fornecedor_produto,
                    cod_barras FROM produtos";
                $resultado = mysqli_query($conexao, $sql);

                // Verifica se a consulta retornou resultados
                if ($resultado && mysqli_num_rows($resultado) > 0) {
                    // Loop para exibir cada produto
                    while ($linha = mysqli_fetch_assoc($resultado)) {
                        echo "<tr>";
                        // MODIFICADO: Exibe 'id_produto'
                        echo "<td>" . $linha['id_produto'] . "</td>";
                        echo "<td>" . htmlspecialchars($linha['nome_produto']) . "</td>";
                        echo "<td>" . $linha['estoque_inicial'] . "</td>";
                        echo "<td>" . number_format($linha['custo_produto'], 2, ',', '.') . "</td>";
                        echo "<td>" . number_format($linha['valor_venda_produto'], 2, ',', '.') . "</td>";
                        echo "<td>" . htmlspecialchars($linha['fornecedor_produto']) . "</td>";
                        echo "<td>" . htmlspecialchars($linha['cod_barras']) . "</td>";
                        echo "<td>";
                        // MODIFICADO: Passa 'id_produto' nos links
                        echo "<a href='gerenciar_produtos.php?acao=editar&id=" . $linha['id_produto'] . "' class='btn-editar'>Editar</a> ";
                        echo "<a href='gerenciar_produtos.php?acao=excluir&id=" . $linha['id_produto'] . "' onclick='return confirm(\"Tem certeza que deseja excluir este produto?\")' class='btn-excluir'>Excluir</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    // Mensagem exibida se não houver produtos
                    echo "<tr><td colspan='8'>Nenhum produto cadastrado.</td></tr>";
                }
                // Fecha a conexão com o banco de dados
                mysqli_close($conexao);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
