<?php
include 'includes/header.php'; 


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: venda_listagem.php?status=error&message=ID de venda inválido para edição.");
    exit();
}

$venda_id = $_GET['id'];
$venda = null;
$itens_venda_atuais = [];


if (!isset($conn) || $conn->connect_error) {
    die("Conexão com o banco de dados falhou: " . $conn->connect_error);
}

$clientes = [];
$sql_clientes = "SELECT id, nome_cliente FROM clientes ORDER BY nome_cliente ASC";
$result_clientes = $conn->query($sql_clientes);
if ($result_clientes) {
    while($row = $result_clientes->fetch_assoc()) {
        $clientes[] = $row;
    }
}


$produtos = [];
$produtos_json = []; 
$sql_produtos = "SELECT p.id, p.nome_produto, p.valor_venda, u.sigla AS unidade_sigla 
                  FROM produtos p
                  JOIN unidades u ON p.unidade_id = u.id
                  ORDER BY p.nome_produto ASC";
$result_produtos = $conn->query($sql_produtos);
if ($result_produtos) {
    while($row = $result_produtos->fetch_assoc()) {
        $produtos[] = $row;
        // Prepara dados para JS
        $produtos_json[$row['id']] = [
            'preco' => (float)$row['valor_venda'],
            'unidade' => $row['unidade_sigla']
        ];
    }
}


$sql_venda = "SELECT v.* FROM vendas v WHERE v.id = ?"; 
if ($stmt_venda = $conn->prepare($sql_venda)) {
    $stmt_venda->bind_param("i", $venda_id);
    $stmt_venda->execute();
    $result_venda = $stmt_venda->get_result();
    if ($result_venda->num_rows === 1) {
        $venda = $result_venda->fetch_assoc();
    } else {
        header("Location: venda_listagem.php?status=error&message=Venda não encontrada."); exit();
    }
    $stmt_venda->close();
} else {
    header("Location: venda_listagem.php?status=error&message=Erro ao preparar busca da venda."); exit();
}


$sql_itens = "SELECT iv.*, p.nome_produto, u.sigla AS unidade_sigla
             FROM itens_venda iv
             JOIN produtos p ON iv.produto_id = p.id
             JOIN unidades u ON p.unidade_id = u.id
             WHERE iv.venda_id = ?";
$stmt_itens = $conn->prepare($sql_itens);
$stmt_itens->bind_param("i", $venda_id);
$stmt_itens->execute();
$result_itens = $stmt_itens->get_result();
while($row = $result_itens->fetch_assoc()) {
    $itens_venda_atuais[] = $row;
}
$stmt_itens->close();
$conn->close(); 


$data_venda_input = date('Y-m-d', strtotime($venda['data_venda']));

if (
    !empty($venda['vencimento_venda']) && 
    $venda['vencimento_venda'] != '0000-00-00' && 
    $venda['vencimento_venda'] != '0000-00-00 00:00:00'
) {
    $vencimento_input = date('Y-m-d', strtotime($venda['vencimento_venda']));
} else {
    $vencimento_input = ''; 
}


$nome_avulso_salvo = $venda['nome_cliente_avulso'] ?? ''; 
$cpf_cnpj_avulso_salvo = $venda['cpf_cnpj_avulso'] ?? ''; 
$cliente_selecionado = $venda['cliente_id'];


$display_avulso = ($cliente_selecionado == 1) ? 'block' : 'none';


$valor_total_formatado = number_format($venda['valor_total'], 2, ',', '.');
$valor_total_hidden = number_format($venda['valor_total'], 2, '.', ''); 
?>

<h1 class="h3 mb-4 text-gray-800">Editar Venda #<?php echo $venda_id; ?></h1>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-warning">Atenção: A edição REVERTE e REAPLICA a baixa de estoque.</h6>
    </div>
    <div class="card-body">
        
        <form action="processa_edicao_venda.php" method="POST"> 
            
            <input type="hidden" name="venda_id" value="<?php echo $venda_id; ?>">

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="cliente_id">Cliente</label>
                    <select id="cliente_id" name="cliente_id" class="form-control" onchange="toggleCamposAvulsos()" required>
                        <?php foreach ($clientes as $cliente): ?>
                            <option 
                                value="<?php echo $cliente['id']; ?>"
                                <?php echo ($cliente['id'] == $cliente_selecionado) ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($cliente['nome_cliente']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group col-md-3" id="div_nome_avulso" style="display: <?php echo $display_avulso; ?>;">
                    <label for="nome_cliente_avulso">Nome Cliente (Avulso - Opcional)</label>
                    <input type="text" id="nome_cliente_avulso" name="nome_cliente_avulso" class="form-control" 
                            value="<?php echo htmlspecialchars($nome_avulso_salvo); ?>">
                </div>

                <div class="form-group col-md-3" id="div_cpf_cnpj_avulso" style="display: <?php echo $display_avulso; ?>;">
                    <label for="cpf_cnpj_avulso">CPF/CNPJ (Avulso - Opcional)</label>
                    <input type="text" id="cpf_cnpj_avulso" name="cpf_cnpj_avulso" class="form-control" 
                            value="<?php echo htmlspecialchars($cpf_cnpj_avulso_salvo); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label for="data_venda">Data da Venda</label>
                    <input type="date" id="data_venda" name="data_venda" class="form-control" 
                            value="<?php echo $data_venda_input; ?>" required>
                </div>

                <div class="form-group col-md-3">
                    <label for="vencimento_venda">Vencimento (Se a Prazo)</label>
                    <input type="date" id="vencimento_venda" name="vencimento_venda" class="form-control" 
                            value="<?php echo $vencimento_input; ?>">
                </div>
            </div>

            <div class="form-row mb-4">
                <div class="form-group col-md-4">
                    <label for="forma_pagamento">Forma de Pagamento</label>
                    <select id="forma_pagamento" name="forma_pagamento" class="form-control" required>
                        <?php 
                        $formas_pagamento = ['Dinheiro', 'Crédito', 'Débito', 'Pix', 'Transferência'];
                        foreach ($formas_pagamento as $forma): ?>
                            <option value="<?php echo $forma; ?>"
                                <?php echo ($venda['forma_pagamento'] == $forma) ? 'selected' : ''; ?>>
                                <?php echo $forma; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>VALOR TOTAL DA VENDA (R$)</label>
                    <input type="text" id="valor_total_display" class="form-control font-weight-bold" 
                            value="R$ <?php echo $valor_total_formatado; ?>" readonly>
                    <input type="hidden" id="valor_total_hidden" name="valor_total" 
                            value="<?php echo $valor_total_hidden; ?>">
                </div>
            </div>
            
            <hr>
            <h5 class="text-primary">Itens Vendidos (Saída de Estoque)</h5>
            
            <table class="table table-bordered" id="itensTable">
                <thead>
                    <tr>
                        <th style="width: 40%;">Produto</th>
                        <th style="width: 10%;">Unidade</th>
                        <th style="width: 15%;">Preço Unitário</th>
                        <th style="width: 15%;">Quantidade</th>
                        <th style="width: 15%;">Subtotal</th>
                        <th style="width: 5%;">Ação</th>
                    </tr>
                </thead>
                <tbody id="itensBody">
                    <?php 
                    $linha_index = 0;
                    foreach ($itens_venda_atuais as $item): 
                        $produto_id_atual = $item['produto_id'];
                        $preco_unitario_item = number_format($item['valor_unitario_venda'], 2, '.', ''); 
                        $quantidade_item = number_format($item['quantidade'], 2, '.', '');
                        $subtotal_item = number_format($item['valor_total_item'], 2, '.', '');
                    ?>
                    <tr data-index="<?php echo $linha_index; ?>">
                        <td>
                            <select name="itens[<?php echo $linha_index; ?>][produto_id]" class="form-control select-produto" required>
                                <option value="">Selecione o Produto</option>
                                <?php foreach ($produtos as $produto): ?>
                                    <option 
                                        value="<?php echo $produto['id']; ?>" 
                                        data-unidade="<?php echo $produto['unidade_sigla']; ?>"
                                        data-preco="<?php echo $produto['valor_venda']; ?>"
                                        <?php echo ($produto['id'] == $produto_id_atual) ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($produto['nome_produto']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" class="form-control unidade" value="<?php echo htmlspecialchars($item['unidade_sigla']); ?>" readonly></td>
                        <td><input type="number" step="0.01" min="0.01" class="form-control preco-unitario" name="itens[<?php echo $linha_index; ?>][valor_unitario_venda]" value="<?php echo $preco_unitario_item; ?>" required></td>
                        <td><input type="number" step="0.01" min="0.01" class="form-control quantidade" name="itens[<?php echo $linha_index; ?>][quantidade]" value="<?php echo $quantidade_item; ?>" required></td>
                        <td>
                            <input type="text" class="form-control subtotal-display" value="<?php echo $subtotal_item; ?>" readonly>
                            <input type="hidden" class="subtotal-hidden" name="itens[<?php echo $linha_index; ?>][valor_total_item]" value="<?php echo $subtotal_item; ?>">
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm remover-item"><i class="fa fa-trash"></i></button></td>
                    </tr>
                    <?php 
                    $linha_index++;
                    endforeach; 
                    ?>
                </tbody>
            </table>

            <button type="button" class="btn btn-primary" id="adicionarItem">
                <i class="fa fa-plus"></i> Adicionar Produto
            </button>
            
            <hr>
            
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fa fa-save"></i> Salvar Edição
            </button>
            <a href="venda_listagem.php" class="btn btn-secondary btn-lg">
                <i class="fa fa-arrow-left"></i> Cancelar
            </a>
            
        </form>
        </div>
</div>

<script>
    const produtosData = <?php echo json_encode($produtos_json); ?>;
    let nextIndex = <?php echo $linha_index; ?>;
    

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
        }
    }


    document.getElementById('cliente_id').addEventListener('change', toggleCamposAvulsos);

    function calcularLinha(row) {
        const precoInput = row.querySelector('.preco-unitario');
        const qtdInput = row.querySelector('.quantidade');
        const subtotalDisplay = row.querySelector('.subtotal-display');
        const subtotalHidden = row.querySelector('.subtotal-hidden');

        const preco = parseFloat(precoInput.value) || 0;
        const qtd = parseFloat(qtdInput.value) || 0;
        const subtotal = preco * qtd;

        subtotalDisplay.value = subtotal.toFixed(2);
        subtotalHidden.value = subtotal.toFixed(2);
        
        calcularTotalGeral();
    }

    function calcularTotalGeral() {
        let total = 0;
        document.querySelectorAll('.subtotal-hidden').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        document.getElementById('valor_total_display').value = 'R$ ' + total.toFixed(2).replace('.', ',');
        document.getElementById('valor_total_hidden').value = total.toFixed(2);
    }
    

    document.getElementById('adicionarItem').addEventListener('click', function() {
        const tableBody = document.getElementById('itensBody');
        const newRow = tableBody.insertRow();
        newRow.setAttribute('data-index', nextIndex);
        

        newRow.innerHTML = `
            <td>
                <select name="itens[${nextIndex}][produto_id]" class="form-control select-produto" required>
                    <option value="">Selecione o Produto</option>
                    <?php foreach ($produtos as $produto): ?>
                        <option 
                            value="<?php echo $produto['id']; ?>" 
                            data-unidade="<?php echo $produto['unidade_sigla']; ?>"
                            data-preco="<?php echo $produto['valor_venda']; ?>"
                        >
                            <?php echo htmlspecialchars($produto['nome_produto']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="text" class="form-control unidade" value="" readonly></td>
            <td><input type="number" step="0.01" min="0.01" class="form-control preco-unitario" name="itens[${nextIndex}][valor_unitario_venda]" value="0.00" required></td>
            <td><input type="number" step="0.01" min="0.01" class="form-control quantidade" name="itens[${nextIndex}][quantidade]" value="1.00" required></td>
            <td>
                <input type="text" class="form-control subtotal-display" value="0.00" readonly>
                <input type="hidden" class="subtotal-hidden" name="itens[${nextIndex}][valor_total_item]" value="0.00">
            </td>
            <td><button type="button" class="btn btn-danger btn-sm remover-item"><i class="fa fa-trash"></i></button></td>
        `;

        adicionarListeners(newRow);
        nextIndex++;
    });


    function adicionarListeners(row) {

        row.querySelector('.remover-item').addEventListener('click', function() {
            row.remove();
            calcularTotalGeral();
        });
        
        row.querySelector('.select-produto').addEventListener('change', function() {
            const produtoId = this.value;
            const precoInput = row.querySelector('.preco-unitario');
            const unidadeInput = row.querySelector('.unidade');

            if (produtosData[produtoId]) {
                const data = produtosData[produtoId];
                precoInput.value = data.preco.toFixed(2);
                unidadeInput.value = data.unidade;
            } else {
                precoInput.value = '0.00';
                unidadeInput.value = '';
            }
            calcularLinha(row);
        });

        const precoQtdInputs = row.querySelectorAll('.preco-unitario, .quantidade');
        precoQtdInputs.forEach(input => {
            input.addEventListener('change', () => calcularLinha(row));
            input.addEventListener('input', () => calcularLinha(row)); 
        });
        

        calcularLinha(row);
    }
    
  
    document.querySelectorAll('#itensBody tr').forEach(adicionarListeners);
    

    document.addEventListener('DOMContentLoaded', () => {
        calcularTotalGeral();
        toggleCamposAvulsos(); 
    });
</script>

<?php include 'includes/footer.php'; ?>