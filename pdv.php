<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>PDV - Ponto de Venda</title>
    <link rel="stylesheet" href="estilo.css">
    
</head>
<body>
    <div class="container">
        <h1>PDV - Ponto de Venda</h1>
        
        <a href="index.php" class="btn" style="background-color: #007BFF;">Ir para o Inicio</a>
        <a href="historico_vendas.php" class="btn" style="background-color: #17a2b8;">Histórico de Vendas</a>
        
       
            <div class="pdv-left">
                <h3>Buscar Produto</h3>
                <input type="text" id="busca-produto" placeholder="Digite o nome ou código do produto..." autocomplete="off">
                <div id="busca-resultados"></div>
            </div>
            <div class="pdv-right">
                <h3>Itens da Venda</h3>
                <ul id="lista-produtos-venda"></ul>
                <div class="ajuste-venda">
                    <label>
                        Desconto (R$):
                        <input type="number" id="desconto-venda" class="ajuste-input" step="0.01" min="0" value="0">
                    </label>
                    <label>
                        Acréscimo (R$):
                        <input type="number" id="acrescimo-venda" class="ajuste-input" step="0.01" min="0" value="0">
                    </label>
                </div>
                <div class="pdv-totais">
                    <div id="subtotal-venda">Subtotal: R$ 0,00</div>
                    <div id="total-venda">Total: R$ 0,00</div>
                </div>
                <button id="btn-finalizar-venda" class="btn btn-finalizar" disabled>Finalizar Venda</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const buscaInput = document.getElementById('busca-produto');
        const buscaResultados = document.getElementById('busca-resultados');
        const listaVenda = document.getElementById('lista-produtos-venda');
        const subtotalVendaEl = document.getElementById('subtotal-venda');
        const totalVendaEl = document.getElementById('total-venda');
        const btnFinalizar = document.getElementById('btn-finalizar-venda');
        const descontoInput = document.getElementById('desconto-venda');
        const acrescimoInput = document.getElementById('acrescimo-venda');

        let carrinho = [];

        let timeout = null;
        buscaInput.addEventListener('keyup', function() {
            clearTimeout(timeout);
            const query = this.value;
            if (query.length < 2) {
                buscaResultados.innerHTML = '';
                return;
            }
            timeout = setTimeout(() => {
                fetch(`api_vendas.php?acao=buscar&q=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        buscaResultados.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(produto => {
                                const div = document.createElement('div');
                                div.innerHTML = `${produto.nome_produto} (Estoque: ${produto.estoque_inicial})`;
                                div.className = 'produto-busca-resultado';
                                div.addEventListener('click', () => adicionarAoCarrinho(produto));
                                buscaResultados.appendChild(div);
                            });
                        } else {
                            buscaResultados.innerHTML = '<div class="produto-busca-resultado">Nenhum produto encontrado.</div>';
                        }
                    });
            }, 300);
        });

        function adicionarAoCarrinho(produto) {
            const itemExistente = carrinho.find(item => item.id === produto.id);
            if (itemExistente) {
                if (itemExistente.quantidade < produto.estoque_inicial) {
                    itemExistente.quantidade++;
                } else {
                    alert('Quantidade máxima em estoque atingida!');
                }
            } else {
                 if (produto.estoque_inicial > 0) {
                    carrinho.push({ ...produto, quantidade: 1 });
                } else {
                    alert('Produto sem estoque!');
                }
            }
            buscaInput.value = '';
            buscaResultados.innerHTML = '';
            atualizarCarrinho();
        }
        
        function removerDoCarrinho(produtoId) {
            const itemIndex = carrinho.findIndex(item => item.id === produtoId);
            if (itemIndex > -1) {
                if (carrinho[itemIndex].quantidade > 1) {
                    carrinho[itemIndex].quantidade--;
                } else {
                    carrinho.splice(itemIndex, 1);
                }
            }
            atualizarCarrinho();
        }

        function atualizarCarrinho() {
            listaVenda.innerHTML = '';
            let subtotal = 0;
            carrinho.forEach(item => {
                const li = document.createElement('li');
                const valorItem = item.valor_venda_produto * item.quantidade;
                li.innerHTML = `
                    <span>${item.nome_produto} (Qtd: ${item.quantidade})</span>
                    <span>R$ ${valorItem.toFixed(2).replace('.', ',')}</span>
                    <button onclick="removerDoCarrinho(${item.id})">Remover</button>
                `;
                listaVenda.appendChild(li);
                subtotal += valorItem;
            });

            const desconto = parseFloat(descontoInput.value) || 0;
            const acrescimo = parseFloat(acrescimoInput.value) || 0;
            const total = subtotal - desconto + acrescimo;

            subtotalVendaEl.textContent = `Subtotal: R$ ${subtotal.toFixed(2).replace('.', ',')}`;
            totalVendaEl.textContent = `Total: R$ ${total.toFixed(2).replace('.', ',')}`;
            btnFinalizar.disabled = carrinho.length === 0;
        }
        
        descontoInput.addEventListener('input', atualizarCarrinho);
        acrescimoInput.addEventListener('input', atualizarCarrinho);

        window.removerDoCarrinho = removerDoCarrinho;

        btnFinalizar.addEventListener('click', function() {
            if (confirm('Deseja realmente finalizar esta venda?')) {
                const dadosVenda = {
                    carrinho: carrinho,
                    desconto: parseFloat(descontoInput.value) || 0,
                    acrescimo: parseFloat(acrescimoInput.value) || 0
                };

                fetch('api_vendas.php?acao=registrar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dadosVenda)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Venda registrada com sucesso!');
                        carrinho = [];
                        descontoInput.value = 0;
                        acrescimoInput.value = 0;
                        atualizarCarrinho();
                    } else {
                        alert('Erro ao registrar a venda: ' + data.message);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
