<?php
include 'includes/header.php'; 


function is_selected($valor_campo, $valor_atual) {
    
    return (trim((string)$valor_campo) === trim((string)$valor_atual)) ? 'selected' : '';
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: produtos.php?status=error&message=ID de produto inválido para edição.");
    exit();
}

$produto_id = $_GET['id'];
$produto = null;


$sql_produto = "SELECT * FROM produtos WHERE id = ?";
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

$fornecedores = [];
$sql_fornecedores = "SELECT id, nome_fantasia FROM fornecedores ORDER BY nome_fantasia ASC";
$result_fornecedores = $conn->query($sql_fornecedores);
if ($result_fornecedores->num_rows > 0) {
    while($row = $result_fornecedores->fetch_assoc()) {
        $fornecedores[] = $row;
    }
}

$categorias = [];
$sql_categorias = "SELECT id, nome_categoria FROM categorias ORDER BY nome_categoria ASC";
$result_categorias = $conn->query($sql_categorias);
if ($result_categorias->num_rows > 0) {
    while($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

$unidades = [];
$sql_unidades = "SELECT id, sigla FROM unidades ORDER BY sigla ASC";
$result_unidades = $conn->query($sql_unidades);
if ($result_unidades->num_rows > 0) {
    while($row = $result_unidades->fetch_assoc()) {
        $unidades[] = $row;
    }
}


$data_compra_input = date('Y-m-d', strtotime($produto['data_compra']));

?>

<h1 class="h3 mb-4 text-gray-800">Editar Produto: <?php echo htmlspecialchars($produto['nome_produto']); ?></h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-warning">Atenção: A alteração no campo Estoque Atual é manual.</h6>
    </div>
    <div class="card-body">
        <form action="processa_edicao_produto.php" method="POST">
            
            <input type="hidden" name="produto_id" value="<?php echo $produto_id; ?>">
            
            <div class="row mb-4">
                <div class="col-md-5">
                    <label for="nome_produto" class="form-label">Nome do Produto</label>
                    <input type="text" class="form-control" id="nome_produto" name="nome_produto" value="<?php echo htmlspecialchars($produto['nome_produto']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="ncm" class="form-label">NCM (8 dígitos)</label>
                    <input type="text" class="form-control" id="ncm" name="ncm" maxlength="8" value="<?php echo htmlspecialchars($produto['ncm']); ?>">
                </div>
                <div class="col-md-3">
                    <label for="data_compra" class="form-label">Data da Última Compra</label>
                    <input type="date" class="form-control" id="data_compra" name="data_compra" value="<?php echo $data_compra_input; ?>" required>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-5">
                    <label for="fornecedor_id" class="form-label">Fornecedor Principal</label>
                    <select class="form-control" id="fornecedor_id" name="fornecedor_id" required>
                        <option value="">Selecione o Fornecedor</option>
                        <?php foreach ($fornecedores as $forn): ?>
                            <option value="<?php echo $forn['id']; ?>" <?php echo is_selected($forn['id'], $produto['fornecedor_id']); ?>>
                                <?php echo htmlspecialchars($forn['nome_fantasia']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="categoria_id" class="form-label">Categoria</label>
                    <select class="form-control" id="categoria_id" name="categoria_id" required>
                        <option value="">Selecione a Categoria</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo is_selected($cat['id'], $produto['categoria_id']); ?>>
                                <?php echo htmlspecialchars($cat['nome_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="unidade_id" class="form-label">Unidade</label>
                    <select class="form-control" id="unidade_id" name="unidade_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($unidades as $unid): ?>
                            <option value="<?php echo $unid['id']; ?>" <?php echo is_selected($unid['id'], $produto['unidade_id']); ?>>
                                <?php echo htmlspecialchars($unid['sigla']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="valor_compra" class="form-label">Valor de Custo (Última Compra) (R$)</label>
                    <input type="number" step="0.01" class="form-control" id="valor_compra" name="valor_compra" min="0.01" value="<?php echo htmlspecialchars($produto['valor_compra']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="valor_venda" class="form-label">Preço de Venda (R$)</label>
                    <input type="number" step="0.01" class="form-control" id="valor_venda" name="valor_venda" min="0.01" value="<?php echo htmlspecialchars($produto['valor_venda']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="estoque_atual" class="form-label font-weight-bold text-danger">Estoque Atual (Correção Manual)</label>
                    <input type="number" class="form-control" id="estoque_atual" name="estoque_atual" min="0" value="<?php echo htmlspecialchars($produto['estoque_atual']); ?>" required>
                    <small class="form-text text-danger">Modifique com cautela, pois isso altera a contagem física.</small>
                </div>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-warning btn-lg">Salvar Edições do Produto</button>
            </div>
        </form>
    </div>
</div>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>