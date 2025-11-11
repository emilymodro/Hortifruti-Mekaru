<?php
include 'includes/header.php'; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: fornecedores.php?status=error&message=ID de fornecedor inválido.");
    exit();
}

$fornecedor_id = $_GET['id'];
$fornecedor = null;
$contatos = [];
$chaves_pix = [];

$sql_fornecedor = "SELECT nome_fornecedor, nome_fantasia, data_cadastro FROM fornecedores WHERE id = ?";
if ($stmt = $conn->prepare($sql_fornecedor)) {
    $stmt->bind_param("i", $fornecedor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $fornecedor = $result->fetch_assoc();
    } else {
        echo '<div class="alert alert-danger">Fornecedor não encontrado.</div>';
        include 'includes/footer.php';
        exit();
    }
    $stmt->close();
}

$sql_contatos = "SELECT tipo, valor_contato FROM fornecedor_contatos WHERE fornecedor_id = ?";
if ($stmt = $conn->prepare($sql_contatos)) {
    $stmt->bind_param("i", $fornecedor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $contatos[] = $row;
    }
    $stmt->close();
}

$sql_pix = "SELECT tipo, chave FROM fornecedor_pix WHERE fornecedor_id = ?";
if ($stmt = $conn->prepare($sql_pix)) {
    $stmt->bind_param("i", $fornecedor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $chaves_pix[] = $row;
    }
    $stmt->close();
}

$data_cadastro_formatada = date("d/m/Y", strtotime($fornecedor['data_cadastro']));

?>

<h1 class="h3 mb-4 text-gray-800">Visualizar Fornecedor</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Detalhes de: <?php echo htmlspecialchars($fornecedor['nome_fornecedor']); ?></h6>
        <div>
            <a href="fornecedor_editar.php?id=<?php echo $fornecedor_id; ?>" class="btn btn-warning btn-sm me-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="fornecedores.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar à Lista
            </a>
        </div>
    </div>
    
    <div class="card-body">
        
        <h5 class="mb-3 text-info">Informações Principais</h5>
        <form>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nome_fornecedor" class="form-label">Nome do Fornecedor (Razão Social)</label>
                    <input type="text" class="form-control" id="nome_fornecedor" value="<?php echo htmlspecialchars($fornecedor['nome_fornecedor']); ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                    <input type="text" class="form-control" id="nome_fantasia" value="<?php echo htmlspecialchars($fornecedor['nome_fantasia']); ?>" disabled>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Data de Cadastro</label>
                    <input type="text" class="form-control" value="<?php echo $data_cadastro_formatada; ?>" disabled>
                </div>
            </div>
        </form>

        <hr>
        
        <h5 class="mt-4 mb-3 text-info">Contatos Registrados</h5>
        <?php if (count($contatos) > 0): ?>
            <div class="list-group">
                <?php foreach ($contatos as $contato): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong><?php echo htmlspecialchars($contato['tipo']); ?>:</strong>
                        <span><?php echo htmlspecialchars($contato['valor_contato']); ?></span>
                    </li>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Nenhum contato registrado.</div>
        <?php endif; ?>

        <hr>

        <h5 class="mt-4 mb-3 text-info">Chaves PIX Registradas</h5>
        <?php if (count($chaves_pix) > 0): ?>
            <div class="list-group">
                <?php foreach ($chaves_pix as $pix): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong><?php echo htmlspecialchars($pix['tipo']); ?>:</strong>
                        <span><?php echo htmlspecialchars($pix['chave']); ?></span>
                    </li>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Nenhuma chave PIX registrada.</div>
        <?php endif; ?>

    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>