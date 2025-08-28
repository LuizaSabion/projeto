<?php
header('Content-Type: application/json');
include 'conexao.php';

$acao = $_GET['acao'] ?? '';

if ($acao === 'buscar') {//filtro que usa o nome produto e o cod de barras para a busca no sistema
    $query = $_GET['q'] ?? '';
    // MODIFICADO: Seleciona 'id_produto' em vez de 'id'
    $stmt = mysqli_prepare($conexao, "SELECT id_produto, nome_produto, estoque_inicial, valor_venda_produto 
    FROM produtos WHERE nome_produto LIKE ? OR cod_barras LIKE ? LIMIT 10");
    $searchTerm = "%{$query}%";

    mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm); //'ss' quer dizer que os dados da busca são duas strings
    
    mysqli_stmt_execute($stmt); //-- Executa, pega o result set, transforma tudo em array associativo e devolve em JSON.
    $resultado = mysqli_stmt_get_result($stmt);
    $produtos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    echo json_encode($produtos);

} elseif ($acao === 'registrar') {
    $dadosVenda = json_decode(file_get_contents('php://input'), true); //transforma o JSON em array 
    $carrinho = $dadosVenda['carrinho'];
    $desconto = $dadosVenda['desconto'] ?? 0;
    $acrescimo = $dadosVenda['acrescimo'] ?? 0;

    if (empty($carrinho)) { //valida dados do carrinho do pdv
        echo json_encode(['success' => false, 'message' => 'Carrinho vazio.']);
        exit;
    }

    mysqli_begin_transaction($conexao);

    try {//soma valor da venda e subtrai desconto ou soma o acressimo 
        $subtotal = 0;
        foreach ($carrinho as $item) {
            $subtotal += $item['valor_venda_produto'] * $item['quantidade'];
        }
        $valor_total = $subtotal - $desconto + $acrescimo;

        //registra a venda no banco de dados 
        $stmt_venda = mysqli_prepare($conexao, "INSERT INTO vendas (valor_total, desconto, acrescimo) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_venda, "ddd", $valor_total, $desconto, $acrescimo);
        mysqli_stmt_execute($stmt_venda);
        $id_venda = mysqli_insert_id($conexao);

        //Prepara uma query para inserir itens da venda
        $stmt_item = mysqli_prepare($conexao, "INSERT INTO venda_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        
        //Prepara uma query para dar baixar no estoque do produto
        $stmt_estoque = mysqli_prepare($conexao, "UPDATE produtos SET estoque_inicial = estoque_inicial - ? WHERE id_produto = ?");

        foreach ($carrinho as $item) {

            mysqli_stmt_bind_param($stmt_item, "iiid", $id_venda, $item['id_produto'], $item['quantidade'], $item['valor_venda_produto']);
            mysqli_stmt_execute($stmt_item);//grava a venda na tabela 

            mysqli_stmt_bind_param($stmt_estoque, "ii", $item['quantidade'], $item['id_produto']);
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

        // MODIFICADO: Condição WHERE usa 'id_produto'
        $stmt_repor_estoque = mysqli_prepare($conexao, "UPDATE produtos SET estoque_inicial = estoque_inicial + ? WHERE id_produto = ?");
        foreach ($itens_para_repor as $item) {
            mysqli_stmt_bind_param($stmt_repor_estoque, "ii", $item['quantidade'], $item['id_produto']);
            mysqli_stmt_execute($stmt_repor_estoque);
        }
         // --- java script para exclusão das vendas ---
        $stmt_del_itens = mysqli_prepare($conexao, "DELETE FROM venda_itens WHERE id_venda = ?");
        mysqli_stmt_bind_param($stmt_del_itens, "i", $id_venda);
        mysqli_stmt_execute($stmt_del_itens);

        $stmt_del_venda = mysqli_prepare($conexao, "DELETE FROM vendas WHERE id_venda = ?");
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
