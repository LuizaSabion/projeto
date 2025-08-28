CREATE DATABASE Projeto;

USE Projeto;


CREATE TABLE produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    nome_produto VARCHAR(255) NOT NULL,
    estoque_inicial INT NOT NULL,
    custo_produto DECIMAL(10, 2) NOT NULL,
    valor_venda_produto DECIMAL(10, 2) NOT NULL,
    fornecedor_produto VARCHAR(255) NOT NULL,
    cod_barras VARCHAR(100)
);


CREATE TABLE vendas (
    id_venda INT AUTO_INCREMENT PRIMARY KEY,
    data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) DEFAULT 0,
    acrescimo DECIMAL(10,2) DEFAULT 0
);


CREATE TABLE venda_itens (
    id_itens_venda INT AUTO_INCREMENT PRIMARY KEY,
    id_venda INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    
    CONSTRAINT fk_venda FOREIGN KEY (id_venda) REFERENCES vendas(id_venda) ON DELETE CASCADE,
    CONSTRAINT fk_produto FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
);
