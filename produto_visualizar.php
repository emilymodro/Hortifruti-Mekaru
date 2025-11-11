<?php

include 'includes/header.php'; 


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: produtos.php?status=error&message=ID de produto inválido.");
    exit();
}

$produto_id = $_GET['id'];
$produto = null;

$sql_produto = "SELECT 
                    p.nome_produto, 
                    p.ncm,
                    p.valor_compra,
                    p.valor_venda,
                    p.quantidade_comprada,
                    p.estoque_atual,
                    p.data_compra,
                    p.data_cadastro,
                    c.nome_categoria,
                    u.sigla AS unidade_sigla,
                    f.nome_fantasia AS fornecedor_nome
                FROM produtos p
                JOIN categorias c ON p.categoria_id = c.id
                JOIN unidades u ON p.unidade_id = u.id
                JOIN fornecedores f ON p.fornecedor_id = f.id
                WHERE p.id = ?";
                
if ($stmt = $conn->prepare($sql_produto)) {
    $stmt->bind_param("i", $produto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $produto = $result->fetch_assoc();
    } else {
        
        echo '<div class="alert alert-danger">Produto não encontrado.</div>';
        include 'includes/footer.php';
        exit();
    }
    $stmt->close();
}

$data_compra_formatada = date("d/m/Y", strtotime($produto['data_compra']));
$data_cadastro_formatada = date("d/m/Y H:i:s", strtotime($produto['data_cadastro']));
$valor_compra_formatado = "R$ " . number_format($produto['valor_compra'], 2, ',', '.');
$valor_venda_formatado = "R$ " . number_format($produto['valor_venda'], 2, ',', '.');
$estoque_atual = $produto['estoque_atual'];
$estoque_class = 'text-success';
if ($estoque_atual < 50) $estoque_class = 'text-warning';
if ($estoque_atual <= 10) $estoque_class = 'text-danger';
?>

<h1 class="h3 mb-4 text-gray-800">Visualizar Produto</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Detalhes de: <?php echo htmlspecialchars($produto['nome_produto']); ?></h6>
        <div>
            <a href="produto_editar.php?id=<?php echo $produto_id; ?>" class="btn btn-warning btn-sm me-2">
                <i class="fas fa-edit"></i> Editar Dados
            </a>
            <a href="produtos.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar à Lista
            </a>
        </div>
    </div>
    
    <div class="card-body">
        
        <h5 class="mb-3 text-info">Identificação e Classificação</h5>
        <form>
            <div class="row mb-3">
                <div class="col-md-5">
                    <label class="form-label">Nome do Produto</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($produto['nome_produto']); ?>" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label">NCM</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($produto['ncm']); ?>" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Categoria</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($produto['nome_categoria']); ?>" disabled>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label">Unidade de Medida</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($produto['unidade_sigla']); ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fornecedor Principal</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($produto['fornecedor_nome']); ?>" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cadastrado em</label>
                    <input type="text" class="form-control" value="<?php echo $data_cadastro_formatada; ?>" disabled>
                </div>
            </div>
        </form>

        <hr>
        
        <h5 class="mt-4 mb-3 text-info">Preços e Situação do Estoque</h5>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Preço de Custo (Última Compra)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $valor_compra_formatado; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Preço de Venda
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $valor_venda_formatado; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tag fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Última Compra (Quantidade)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($produto['quantidade_comprada']) . ' ' . htmlspecialchars($produto['unidade_sigla']); ?></div>
                                <div class="text-xs mt-1 text-muted">Em: <?php echo $data_compra_formatada; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-<?php echo str_replace('text-', '', $estoque_class); ?> shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold <?php echo $estoque_class; ?> text-uppercase mb-1">
                                    ESTOQUE ATUAL
                                </div>
                                <div class="h5 mb-0 font-weight-bold <?php echo $estoque_class; ?>"><?php echo $estoque_atual . ' ' . htmlspecialchars($produto['unidade_sigla']); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>