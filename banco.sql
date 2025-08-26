CREATE DATABASE Projeto;

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY_KEY,
    nome_produto VARCHAR(255) NOT NULL,
    estoque_inicial INT NOT NULL,
    custo_produto DECIMAL(10, 2) NOT NULL,
    valor_venda_produto DECIMAL(10, 2) NOT NULL,
    fornecedor_produto VARCHAR(255) NOT NULL,
    cod_barras VARCHAR(100)
);
