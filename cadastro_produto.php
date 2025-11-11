<?php
// Inclui o header, que já contém a conexão com o DB
include 'includes/header.php'; 

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

?>

<h1 class="h3 mb-4 text-gray-800">Cadastro de Novo Produto</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Informações da Compra e do Produto</h6>
    </div>
    <div class="card-body">
        <form action="processa_produto.php" method="POST">
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="nome_produto" class="form-label">Nome do Produto</label>
                    <input type="text" class="form-control" id="nome_produto" name="nome_produto" required>
                </div>
                <div class="col-md-3">
                    <label for="ncm" class="form-label">NCM (8 dígitos)</label>
                    <input type="text" class="form-control" id="ncm" name="ncm" maxlength="8">
                </div>
                <div class="col-md-3">
                    <label for="data_compra" class="form-label">Data da Compra</label>
                    <input type="date" class="form-control" id="data_compra" name="data_compra" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-5">
                    <label for="fornecedor_id" class="form-label">Fornecedor</label>
                    <select class="form-control" id="fornecedor_id" name="fornecedor_id" required>
                        <option value="">Selecione o Fornecedor</option>
                        <?php foreach ($fornecedores as $forn): ?>
                            <option value="<?php echo $forn['id']; ?>">
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
                            <option value="<?php echo $cat['id']; ?>">
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
                            <option value="<?php echo $unid['id']; ?>">
                                <?php echo htmlspecialchars($unid['sigla']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="valor_compra" class="form-label">Valor da Compra por Unidade (R$)</label>
                    <input type="number" step="0.01" class="form-control" id="valor_compra" name="valor_compra" min="0.01" required>
                </div>
                <div class="col-md-4">
                    <label for="valor_venda" class="form-label">Preço de Venda por Unidade (R$)</label>
                    <input type="number" step="0.01" class="form-control" id="valor_venda" name="valor_venda" min="0.01" required>
                </div>
                <div class="col-md-4">
                    <label for="quantidade_comprada" class="form-label">Quantidade Comprada</label>
                    <input type="number" class="form-control" id="quantidade_comprada" name="quantidade_comprada" min="1" required>
                    <small class="form-text text-muted">Essa quantidade será somada ao estoque.</small>
                </div>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Cadastrar Produto e Atualizar Estoque</button>
            </div>
        </form>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>