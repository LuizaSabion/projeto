<?php
header('Content-Type: application/json');
include 'conexao.php';

$acao = $_GET['acao'] ?? '';

if ($acao === 'buscar') {
    $query = $_GET['q'] ?? '';
    $stmt = mysqli_prepare($conexao, "SELECT id, nome_produto, estoque_inicial, valor_venda_produto FROM produtos WHERE nome_produto LIKE ? OR cod_barras LIKE ? LIMIT 10");
    $searchTerm = "%{$query}%";
    mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $produtos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    echo json_encode($produtos);

} elseif ($acao === 'registrar') {
    $dadosVenda = json_decode(file_get_contents('php://input'), true);
    $carrinho = $dadosVenda['carrinho'];
    $desconto = $dadosVenda['desconto'] ?? 0;
    $acrescimo = $dadosVenda['acrescimo'] ?? 0;

    if (empty($carrinho)) {
        echo json_encode(['success' => false, 'message' => 'Carrinho vazio.']);
        exit;
    }

    mysqli_begin_transaction($conexao);

    try {
        $subtotal = 0;
        foreach ($carrinho as $item) {
            $subtotal += $item['valor_venda_produto'] * $item['quantidade'];
        }
        $valor_total = $subtotal - $desconto + $acrescimo;

        $stmt_venda = mysqli_prepare($conexao, "INSERT INTO vendas (valor_total, desconto, acrescimo) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_venda, "ddd", $valor_total, $desconto, $acrescimo);
        mysqli_stmt_execute($stmt_venda);
        $id_venda = mysqli_insert_id($conexao);

        $stmt_item = mysqli_prepare($conexao, "INSERT INTO venda_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt_estoque = mysqli_prepare($conexao, "UPDATE produtos SET estoque_inicial = estoque_inicial - ? WHERE id = ?");

        foreach ($carrinho as $item) {
            mysqli_stmt_bind_param($stmt_item, "iiid", $id_venda, $item['id'], $item['quantidade'], $item['valor_venda_produto']);
            mysqli_stmt_execute($stmt_item);

            mysqli_stmt_bind_param($stmt_estoque, "ii", $item['quantidade'], $item['id']);
            mysqli_stmt_execute($stmt_estoque);
        }

        mysqli_commit($conexao);
        echo json_encode(['success' => true]);

    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($conexao);
        echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $exception->getMessage()]);
    }

} elseif ($acao === 'excluir_venda') {
    $id_venda = $_GET['id'] ?? 0;

    if ($id_venda <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID da venda inválido.']);
        exit;
    }

    mysqli_begin_transaction($conexao);

    try {
        $stmt_itens = mysqli_prepare($conexao, "SELECT id_produto, quantidade FROM venda_itens WHERE id_venda = ?");
        mysqli_stmt_bind_param($stmt_itens, "i", $id_venda);
        mysqli_stmt_execute($stmt_itens);
        $itens_resultado = mysqli_stmt_get_result($stmt_itens);
        $itens_para_repor = mysqli_fetch_all($itens_resultado, MYSQLI_ASSOC);

        $stmt_repor_estoque = mysqli_prepare($conexao, "UPDATE produtos SET estoque_inicial = estoque_inicial + ? WHERE id = ?");
        foreach ($itens_para_repor as $item) {
            mysqli_stmt_bind_param($stmt_repor_estoque, "ii", $item['quantidade'], $item['id_produto']);
            mysqli_stmt_execute($stmt_repor_estoque);
        }

        $stmt_del_itens = mysqli_prepare($conexao, "DELETE FROM venda_itens WHERE id_venda = ?");
        mysqli_stmt_bind_param($stmt_del_itens, "i", $id_venda);
        mysqli_stmt_execute($stmt_del_itens);

        // --- CORREÇÃO APLICADA AQUI ---
        // A coluna na tabela 'vendas' chama-se 'id', e não 'id_venda'.
        $stmt_del_venda = mysqli_prepare($conexao, "DELETE FROM vendas WHERE id = ?");
        mysqli_stmt_bind_param($stmt_del_venda, "i", $id_venda);
        mysqli_stmt_execute($stmt_del_venda);

        mysqli_commit($conexao);
        echo json_encode(['success' => true]);

    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($conexao);
        echo json_encode(['success' => false, 'message' => 'Erro no banco de dados ao excluir a venda: ' . $exception->getMessage()]);
    }
}

mysqli_close($conexao);
?>
