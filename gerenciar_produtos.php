<?php
include 'conexao.php';

$acao = $_GET['acao'] ?? 'adicionar';
$id = $_GET['id'] ?? 0;

$produto = [
    'id' => '', 'nome_produto' => '', 'estoque_inicial' => '',
    'custo_produto' => '', 'valor_venda_produto' => '', 'fornecedor_produto' => '',
    'cod_barras' => ''
];

$titulo_pagina = "Adicionar Novo Produto";
$acao_form = "db_criar";

// --- LÓGICA DE PROCESSAMENTO DO FORMULÁRIO (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao_post = $_POST['acao_form'];

    // --- LÓGICA DE CRIAÇÃO (INSERT) ---
    if ($acao_post === 'db_criar') {
        $nome = $_POST['nome_produto'];
        $estoque = $_POST['estoque_inicial'];
        $custo = str_replace(',', '.', $_POST['custo_produto']);
        $valor_venda = str_replace(',', '.', $_POST['valor_venda_produto']);
        $fornecedor = $_POST['fornecedor_produto'];
        $cod_barras = $_POST['cod_barras'];

        $sql = "INSERT INTO produtos (nome_produto, estoque_inicial, custo_produto, valor_venda_produto, fornecedor_produto, cod_barras) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conexao, $sql);
        // Tipos de dados: s=string, d=double, i=integer. Total 6 parâmetros.
        mysqli_stmt_bind_param($stmt, "ssddss", $nome, $estoque, $custo, $valor_venda, $fornecedor, $cod_barras);
        
        if (!mysqli_stmt_execute($stmt)) {
            echo "Erro ao cadastrar produto: " . mysqli_error($conexao);
        }
    
    // --- LÓGICA DE EDIÇÃO (UPDATE) ---
    } elseif ($acao_post === 'db_editar') {
        $id_post = $_POST['id'];
        $nome = $_POST['nome_produto'];
        $estoque = $_POST['estoque_inicial'];
        $custo = str_replace(',', '.', $_POST['custo_produto']);
        $valor_venda = str_replace(',', '.', $_POST['valor_venda_produto']);
        $fornecedor = $_POST['fornecedor_produto'];
        $cod_barras = $_POST['cod_barras'];
        
        $sql = "UPDATE produtos SET nome_produto = ?, estoque_inicial = ?, custo_produto = ?, valor_venda_produto = ?, fornecedor_produto = ?, cod_barras = ? WHERE id = ?";
        
        $stmt = mysqli_prepare($conexao, $sql);
        // Tipos de dados: 6 strings/doubles e 1 integer no final para o ID.
        mysqli_stmt_bind_param($stmt, "ssddssi", $nome, $estoque, $custo, $valor_venda, $fornecedor, $cod_barras, $id_post);

        if (!mysqli_stmt_execute($stmt)) {
            echo "Erro ao atualizar produto: " . mysqli_error($conexao);
        }
    }
    
    mysqli_close($conexao);
    header("Location: index.php");
    exit();

// --- LÓGICA PARA CARREGAR DADOS (GET) ---
} elseif ($acao === 'editar' && $id > 0) {
    $titulo_pagina = "Editar Produto";
    $acao_form = "db_editar";
    
    $sql = "SELECT * FROM produtos WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if ($resultado) {
        $produto = mysqli_fetch_assoc($resultado);
    }

} elseif ($acao === 'excluir' && $id > 0) {
    // --- LÓGICA DE EXCLUSÃO (DELETE) ---
    $sql = "DELETE FROM produtos WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (!mysqli_stmt_execute($stmt)) {
        echo "Erro ao excluir produto: " . mysqli_error($conexao);
    }
    
    mysqli_close($conexao);
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo_pagina; ?></title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <h1><?php echo $titulo_pagina; ?></h1>
        <!-- Formulário sem o enctype -->
        <form action="gerenciar_produtos.php" method="post">
            <input type="hidden" name="acao_form" value="<?php echo $acao_form; ?>">
            <input type="hidden" name="id" value="<?php echo $produto['id']; ?>">
            
            <label for="nome_produto">Nome do Produto:*</label>
            <input type="text" id="nome_produto" name="nome_produto" value="<?php echo htmlspecialchars($produto['nome_produto']); ?>" required>

            <label for="estoque_inicial">Estoque Inicial:*</label>
            <input type="number" id="estoque_inicial" name="estoque_inicial" value="<?php echo $produto['estoque_inicial']; ?>" required>

            <label for="custo_produto">Custo do Produto (R$):*</label>
            <input type="text" inputmode="decimal" id="custo_produto" name="custo_produto" value="<?php echo !empty($produto['custo_produto']) ? number_format($produto['custo_produto'], 2, ',', '.') : ''; ?>" required>

            <label for="valor_venda_produto">Valor de Venda (R$):*</label>
            <input type="text" inputmode="decimal" id="valor_venda_produto" name="valor_venda_produto" value="<?php echo !empty($produto['valor_venda_produto']) ? number_format($produto['valor_venda_produto'], 2, ',', '.') : ''; ?>" required>

            <label for="fornecedor_produto">Fornecedor:*</label>
            <input type="text" id="fornecedor_produto" name="fornecedor_produto" value="<?php echo htmlspecialchars($produto['fornecedor_produto']); ?>" required>

            <label for="cod_barras">Código de Barras (opcional):</label>
            <input type="text" id="cod_barras" name="cod_barras" value="<?php echo htmlspecialchars($produto['cod_barras']); ?>">

            <button type="submit" class="btn">
                <?php echo ($acao_form === 'db_criar') ? 'Cadastrar Produto' : 'Salvar Alterações'; ?>
            </button>
        </form>
    </div>
</body>
</html>
<?php
if ($conexao) {
    mysqli_close($conexao);
}
?>
