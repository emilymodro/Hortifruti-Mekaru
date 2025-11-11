<?php
include 'includes/header.php'; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: clientes.php?status=error&message=ID de cliente inválido.");
    exit();
}

$cliente_id = $_GET['id'];
$cliente = null;
$contatos = [];
$chaves_pix = [];


$sql_cliente = "SELECT nome_cliente, nome_fantasia, cnpj_cpf, inscricao_estadual, email, endereco, data_cadastro FROM clientes WHERE id = ?";
if ($stmt = $conn->prepare($sql_cliente)) {
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $cliente = $result->fetch_assoc();
    } else {
        echo '<div class="alert alert-danger">Cliente não encontrado.</div>';
        include 'includes/footer.php';
        exit();
    }
    $stmt->close();
}

$sql_contatos = "SELECT tipo, valor_contato FROM cliente_contatos WHERE cliente_id = ?";
if ($stmt = $conn->prepare($sql_contatos)) {
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $contatos[] = $row;
    }
    $stmt->close();
}

$sql_pix = "SELECT tipo, chave FROM cliente_pix WHERE cliente_id = ?";
if ($stmt = $conn->prepare($sql_pix)) {
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $chaves_pix[] = $row;
    }
    $stmt->close();
}

$data_cadastro_formatada = date("d/m/Y", strtotime($cliente['data_cadastro']));

?>

<h1 class="h3 mb-4 text-gray-800">Visualizar Cliente</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Detalhes de: <?php echo htmlspecialchars($cliente['nome_cliente']); ?></h6>
        <div>
            <a href="cliente_editar.php?id=<?php echo $cliente_id; ?>" class="btn btn-warning btn-sm me-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="clientes.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar à Lista
            </a>
        </div>
    </div>
    
    <div class="card-body">
        
        <h5 class="mb-3 text-info">Informações Principais</h5>
        <form>
            <div class="row mb-3">
                <div class="col-md-5">
                    <label for="nome_cliente" class="form-label">Nome do Cliente (Razão Social)</label>
                    <input type="text" class="form-control" id="nome_cliente" value="<?php echo htmlspecialchars($cliente['nome_cliente']); ?>" disabled>
                </div>
                <div class="col-md-5">
                    <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                    <input type="text" class="form-control" id="nome_fantasia" value="<?php echo htmlspecialchars($cliente['nome_fantasia']); ?>" disabled>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="text" class="form-control" value="<?php echo $data_cadastro_formatada; ?>" disabled>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="cnpj_cpf" class="form-label">CNPJ/CPF</label>
                    <input type="text" class="form-control" id="cnpj_cpf" value="<?php echo htmlspecialchars($cliente['cnpj_cpf']); ?>" disabled>
                </div>
                <div class="col-md-4">
                    <label for="inscricao_estadual" class="form-label">Inscrição Estadual</label>
                    <input type="text" class="form-control" id="inscricao_estadual" value="<?php echo htmlspecialchars($cliente['inscricao_estadual']); ?>" disabled>
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">E-mail Principal</label>
                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" disabled>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <label for="endereco" class="form-label">Endereço</label>
                    <textarea class="form-control" id="endereco" rows="2" disabled><?php echo htmlspecialchars($cliente['endereco']); ?></textarea>
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