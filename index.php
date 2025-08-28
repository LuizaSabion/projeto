<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistema de Gestão</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .menu-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            font-size: 1.1em;
            font-weight: bold;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            color: #0056b3;
        }
        .menu-item .icon {
            font-size: 3em; /* Tamanho do ícone do menu */
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sistema de Gerenciamento de Estoque</h1>

        <div class="menu-grid">
            <!-- Funcionalidades já criadas -->
            <a href="pdv.php" class="menu-item">
                <span class="icon">🛒</span>
                <span>Ponto de Venda (PDV)</span> <!-- area de venda, 'carrinho'-->
            </a>
            <a href="estoque.php" class="menu-item">
                <span class="icon">📦</span>
                <span>Gerenciar Produtos</span> <!-- Cadastrar, editar e excluir os produtos-->
            </a>
            <a href="historico_vendas.php" class="menu-item">
                <span class="icon">📈</span>
                <span>Histórico de Vendas</span><!--vendas cadastradas e botão para excluir vendas -->
            </a>

            <!-- Funcionalidades furuas para o sistema -->
            <a href="#" class="menu-item">
                <span class="icon">👥</span>
                <span>Cadastrar Funcionários</span>
            </a>
            <a href="#" class="menu-item">
                <span class="icon">👤</span>
                <span>Cadastrar Clientes</span>
            </a>
            <a href="#" class="menu-item">
                <span class="icon">🚚</span>
                <span>Cadastrar Fornecedores</span>
            </a>
            <a href="#" class="menu-item">
                <span class="icon">📄</span>
                <span>Relatórios</span>
            </a>
        </div>
    </div>
</body>
</html>
