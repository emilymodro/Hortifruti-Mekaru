<?php
include 'includes/header.php'; 

// 1. Verificar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: venda_listagem.php?status=error&message=ID de venda inválido.");
    exit();
}

$venda_id = $_GET['id'];
$venda = null;
$itens_venda = [];

$sql_venda = "SELECT 
                v.id, 
                v.data_venda, 
                v.vencimento_venda, 
                v.valor_total, 
                v.forma_pagamento,
                c.nome_cliente
              FROM vendas v
              JOIN clientes c ON v.cliente_id = c.id
              WHERE v.id = ?";
                
if ($stmt = $conn->prepare($sql_venda)) {
    $stmt->bind_param("i", $venda_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $venda = $result->fetch_assoc();
    } else {
        echo '<div class="alert alert-danger">Venda não encontrada.</div>';
        include 'includes/footer.php';
        exit();
    }
    $stmt->close();
}


$sql_itens = "SELECT 
                iv.quantidade,
                iv.valor_unitario_venda,
                iv.valor_total_item,
                p.nome_produto,
                u.sigla AS unidade_sigla
              FROM itens_venda iv
              JOIN produtos p ON iv.produto_id = p.id
              JOIN unidades u ON p.unidade_id = u.id
              WHERE iv.venda_id = ?";

if ($stmt_itens = $conn->prepare($sql_itens)) {
    $stmt_itens->bind_param("i", $venda_id);
    $stmt_itens->execute();
    $result_itens = $stmt_itens->get_result();
    while($row = $result_itens->fetch_assoc()) {
        $itens_venda[] = $row;
    }
    $stmt_itens->close();
}

$conn->close();


$data_venda_formatada = date("d/m/Y", strtotime($venda['data_venda']));
$vencimento_formatado = $venda['vencimento_venda'] ? date("d/m/Y", strtotime($venda['vencimento_venda'])) : "À Vista";
$valor_total_formatado = "R$ " . number_format($venda['valor_total'], 2, ',', '.');
?>

<h1 class="h3 mb-4 text-gray-800">Detalhes da Venda #<?php echo $venda_id; ?></h1>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Informações da Transação</h6>
        <a href="venda_listagem.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar à Lista
        </a>
    </div>
    
    <div class="card-body">
        
        <div class="row mb-4">
            
            <div class="col-md-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Cliente
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($venda['nome_cliente']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Pagamento / Vencimento
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($venda['forma_pagamento']); ?></div>
                        <div class="text-xs mt-1 text-muted">Vencimento: <?php echo $vencimento_formatado; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Valor Total da Venda (<?php echo $data_venda_formatada; ?>)
                        </div>
                        <div class="h4 mb-0 font-weight-bold text-success"><?php echo $valor_total_formatado; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        
        <h5 class="mt-4 mb-3 text-primary">Produtos Vendidos</h5>
        
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Unidade</th>
                        <th>Qtd. Vendida</th>
                        <th>Valor Unitário</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($itens_venda) > 0): ?>
                        <?php foreach ($itens_venda as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nome_produto']); ?></td>
                            <td><?php echo htmlspecialchars($item['unidade_sigla']); ?></td>
                            <td><?php echo number_format($item['quantidade'], 2, ',', '.'); ?></td>
                            <td><?php echo "R$ " . number_format($item['valor_unitario_venda'], 2, ',', '.'); ?></td>
                            <td><?php echo "R$ " . number_format($item['valor_total_item'], 2, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Nenhum item encontrado para esta venda.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total da Venda:</th>
                        <th><?php echo $valor_total_formatado; ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>