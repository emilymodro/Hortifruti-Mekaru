<?php
include 'includes/header.php'; 

$clientes = [];
$sql_clientes = "SELECT id, nome_cliente FROM clientes ORDER BY nome_cliente ASC"; 
$result_clientes = $conn->query($sql_clientes);
if ($result_clientes->num_rows > 0) {
    while($row = $result_clientes->fetch_assoc()) {
        $clientes[] = ['id' => $row['id'], 'nome' => $row['nome_cliente']];
    }
}

$produtos = [];
$produtos_json = []; 
$sql_produtos = "SELECT p.id, p.nome_produto, p.valor_venda, u.sigla AS unidade_sigla 
                  FROM produtos p
                  JOIN unidades u ON p.unidade_id = u.id
                  ORDER BY p.nome_produto ASC";
$result_produtos = $conn->query($sql_produtos);
if ($result_produtos->num_rows > 0) {
    while($row = $result_produtos->fetch_assoc()) {
        $produtos[] = $row;
        $produtos_json[$row['id']] = [
            'preco' => $row['valor_venda'],
            'unidade' => $row['unidade_sigla']
        ];
    }
}
?>

<h1 class="h3 mb-4 text-gray-800">Registrar Nova Venda</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalhes da Venda</h6>
    </div>
    <div class="card-body">
        
<form action="processa_venda.php" method="POST" id="formVenda">
    <div class="row mb-4">
        <div class="col-md-3">
            <label for="cliente_id" class="form-label">Cliente</label>
            <select class="form-control" id="cliente_id" name="cliente_id" required onchange="toggleCamposAvulsos()">
                <option value="">Selecione o Cliente</option>
                
                <option value="1">** VENDA AVULSA / BALCÃO **</option> 
                
                <?php foreach ($clientes as $cli): ?>
                    <?php if ($cli['id'] != 1): ?>
                        <option value="<?php echo $cli['id']; ?>"><?php echo htmlspecialchars($cli['nome']); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-3" id="div_nome_avulso" style="display: none;">
            <label for="nome_avulso" class="form-label">Nome Cliente (Avulso - Opcional)</label>
            <input type="text" class="form-control" id="nome_avulso" name="nome_cliente_avulso" placeholder="Nome para recibo">
        </div>

        <div class="col-md-3" id="div_cpf_avulso" style="display: none;">
            <label for="cpf_cnpj_avulso" class="form-label">CPF/CNPJ (Avulso - Opcional)</label>
            <input type="text" class="form-control" id="cpf_cnpj_avulso" name="cpf_cnpj_avulso" placeholder="CPF/CNPJ na Nota">
        </div>
        
        <div class="col-md-3">
            <label for="data_venda" class="form-label">Data da Venda</label>
            <input type="date" class="form-control" id="data_venda" name="data_venda" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        
        <div class="col-md-3">
            <label for="vencimento_venda" class="form-label">Vencimento (Se a Prazo)</label>
            <input type="date" class="form-control" id="vencimento_venda" name="vencimento_venda">
            <small class="form-text text-muted">Deixe vazio se a venda for à vista.</small>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
            <select class="form-control" id="forma_pagamento" name="forma_pagamento" required>
                <option value="Dinheiro">Dinheiro</option>
                <option value="Pix">Pix</option>
                <option value="Debito">Débito</option>
                <option value="Credito">Crédito</option>
                <option value="Boleto/Faturado">Boleto/Faturado (a prazo)</option> 
            </select>
        </div>
        
        <div class="col-md-3">
            <label for="valor_desconto" class="form-label text-danger">Desconto em R$</label>
            <input type="number" step="0.01" class="form-control" id="valor_desconto" name="valor_desconto" value="0.00" min="0.00" oninput="calcularTotalVenda()">
        </div>
        
        <div class="col-md-3">
            <label for="valor_total" class="form-label font-weight-bold text-success">TOTAL LÍQUIDO (R$)</label>
            <input type="text" class="form-control form-control-lg text-success font-weight-bold" id="valor_total_display" value="R$ 0,00" disabled>
            <input type="hidden" name="valor_total" id="valor_total_hidden" value="0.00">
        </div>
        
        <input type="hidden" name="valor_total_bruto" id="valor_total_bruto_hidden" value="0.00">
    </div>
    
    <hr>
              <h5 class="mt-4 mb-3 text-primary">Itens Vendidos (Saída de Estoque)</h5>
            
            <table class="table table-bordered" id="tabelaItensVenda">
                <thead>
                    <tr>
                        <th style="width: 40%;">Produto</th>
                        <th style="width: 15%;">Unidade</th>
                        <th style="width: 15%;">Preço Unitário</th>
                        <th style="width: 15%;">Quantidade</th>
                        <th style="width: 10%;">Subtotal</th>
                        <th style="width: 5%;">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
            
            <button type="button" class="btn btn-primary btn-sm mb-4" onclick="adicionarLinhaItem()">
                <i class="fas fa-plus"></i> Adicionar Produto
            </button>
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-success btn-lg">Finalizar Venda e Atualizar Estoque</button>
            </div>
        </form>
    </div>
</div>

<?php 

?>

<script>
 
    const PRODUTOS_DATA = <?php echo json_encode($produtos_json); ?>;
    
    let itemCounter = 0; 
    
    function toggleCamposAvulsos() {
        const clienteSelect = document.getElementById('cliente_id');
        const divNomeAvulso = document.getElementById('div_nome_avulso');
        const divCpfAvulso = document.getElementById('div_cpf_avulso');
        
    
        if (clienteSelect.value == '1') { 
            divNomeAvulso.style.display = 'block';
            divCpfAvulso.style.display = 'block';
        } else {
            divNomeAvulso.style.display = 'none';
            divCpfAvulso.style.display = 'none';
            document.getElementById('nome_avulso').value = '';
            document.getElementById('cpf_cnpj_avulso').value = '';
        }
    }
    
    function adicionarLinhaItem() {
        const tabela = document.getElementById('tabelaItensVenda').getElementsByTagName('tbody')[0];
        const novaLinha = tabela.insertRow();
        novaLinha.id = 'linhaItem' + itemCounter;
        
        let celulaProduto = novaLinha.insertCell(0);
        celulaProduto.innerHTML = `
            <select name="itens[${itemCounter}][produto_id]" class="form-control select-produto" data-id="${itemCounter}" onchange="atualizarItem(${itemCounter})" required>
                <option value="">Selecione o Produto</option>
                <?php foreach ($produtos as $prod): ?>
                    <option value="<?php echo $prod['id']; ?>"><?php echo htmlspecialchars($prod['nome_produto']); ?></option>
                <?php endforeach; ?>
            </select>
        `;
        
     
        let celulaUnidade = novaLinha.insertCell(1);
        celulaUnidade.innerHTML = `<span id="unidade_${itemCounter}">--</span>`;
        
 
        let celulaPreco = novaLinha.insertCell(2);
        celulaPreco.innerHTML = `<input type="number" step="0.01" name="itens[${itemCounter}][valor_unitario_venda]" id="preco_${itemCounter}" class="form-control input-preco" oninput="atualizarItem(${itemCounter})" required value="0.00" min="0.01">`;
        
    
        let celulaQuantidade = novaLinha.insertCell(3);
        celulaQuantidade.innerHTML = `<input type="number" step="0.01" name="itens[${itemCounter}][quantidade]" id="quantidade_${itemCounter}" class="form-control input-quantidade" oninput="atualizarItem(${itemCounter})" required value="1.00" min="0.01">`;
        
      
        let celulaSubtotal = novaLinha.insertCell(4);
        celulaSubtotal.innerHTML = `
            <span id="subtotal_display_${itemCounter}">0.00</span>
            <input type="hidden" name="itens[${itemCounter}][valor_total_item]" id="subtotal_hidden_${itemCounter}" value="0.00">
        `;
        
       
        let celulaAcao = novaLinha.insertCell(5);
        celulaAcao.innerHTML = `<button type="button" class="btn btn-danger btn-sm" onclick="removerLinhaItem(${itemCounter})"><i class="fas fa-trash"></i></button>`;
        
        itemCounter++;
        calcularTotalVenda();
    }
    
    function removerLinhaItem(id) {
        document.getElementById('linhaItem' + id).remove();
        calcularTotalVenda();
    }

    function atualizarItem(id) {
        const produtoSelect = document.querySelector(`select[data-id="${id}"]`);
        const produtoId = produtoSelect.value;
        const inputPreco = document.getElementById(`preco_${id}`);
        const inputQuantidade = document.getElementById(`quantidade_${id}`);
        const spanUnidade = document.getElementById(`unidade_${id}`);
        const spanSubtotal = document.getElementById(`subtotal_display_${id}`);
        const hiddenSubtotal = document.getElementById(`subtotal_hidden_${id}`);
        
        let preco = parseFloat(inputPreco.value) || 0;
        let quantidade = parseFloat(inputQuantidade.value) || 0;
        
        if (produtoId && PRODUTOS_DATA[produtoId]) {
            if (inputPreco.value == 0 || inputPreco.value == "" || !inputPreco.getAttribute('data-preenchido')) {
                preco = parseFloat(PRODUTOS_DATA[produtoId].preco);
                inputPreco.value = preco.toFixed(2);
                inputPreco.setAttribute('data-preenchido', 'true'); 
            }
            spanUnidade.textContent = PRODUTOS_DATA[produtoId].unidade;
        }

        const subtotal = preco * quantidade;
        
        spanSubtotal.textContent = subtotal.toFixed(2);
        hiddenSubtotal.value = subtotal.toFixed(2);
        
        calcularTotalVenda();
    }
    

    function calcularTotalVenda() {
        let totalBruto = 0;
        const subtotais = document.querySelectorAll('input[id^="subtotal_hidden_"]');
        
   
        subtotais.forEach(hiddenInput => {
            totalBruto += parseFloat(hiddenInput.value) || 0;
        });

       
        const inputDesconto = document.getElementById('valor_desconto');
        let desconto = parseFloat(inputDesconto.value) || 0;
        
        
        let totalLiquido = totalBruto - desconto;

     
        if (totalLiquido < 0) {
            totalLiquido = 0;
        }
        
     
        document.getElementById('valor_total_display').value = 'R$ ' + totalLiquido.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('valor_total_hidden').value = totalLiquido.toFixed(2);
        
    
        if(document.getElementById('valor_total_bruto_hidden')) {
            document.getElementById('valor_total_bruto_hidden').value = totalBruto.toFixed(2);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        adicionarLinhaItem();
        toggleCamposAvulsos();
    });
</script>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>