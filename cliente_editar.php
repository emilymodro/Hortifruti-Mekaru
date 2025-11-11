<?php
include 'includes/header.php'; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: clientes.php?status=error&message=ID de cliente inválido para edição.");
    exit();
}

$cliente_id = $_GET['id'];
$cliente = null;
$contatos = [];
$chaves_pix = [];

$sql_cliente = "SELECT nome_cliente, nome_fantasia, cnpj_cpf, inscricao_estadual, email, endereco FROM clientes WHERE id = ?";
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

function is_selected($valor_campo, $valor_atual) {
    return (trim($valor_campo) === trim($valor_atual)) ? 'selected' : '';
}
?>

<h1 class="h3 mb-4 text-gray-800">Editar Cliente: <?php echo htmlspecialchars($cliente['nome_cliente']); ?></h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Informações Básicas e Contato</h6>
    </div>
    <div class="card-body">
        <form action="processa_edicao_cliente.php" method="POST">
            
            <input type="hidden" name="cliente_id" value="<?php echo $cliente_id; ?>">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nome_cliente" class="form-label">Nome do Cliente (Razão Social)</label>
                    <input type="text" class="form-control" id="nome_cliente" name="nome_cliente" value="<?php echo htmlspecialchars($cliente['nome_cliente']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                    <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia" value="<?php echo htmlspecialchars($cliente['nome_fantasia']); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="cnpj_cpf" class="form-label">CNPJ/CPF</label>
                    <input type="text" class="form-control" id="cnpj_cpf" name="cnpj_cpf" value="<?php echo htmlspecialchars($cliente['cnpj_cpf']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="inscricao_estadual" class="form-label">Inscrição Estadual</label>
                    <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual" value="<?php echo htmlspecialchars($cliente['inscricao_estadual']); ?>">
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">E-mail Principal</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <label for="endereco" class="form-label">Endereço Completo</label>
                    <textarea class="form-control" id="endereco" name="endereco" rows="2"><?php echo htmlspecialchars($cliente['endereco']); ?></textarea>
                </div>
            </div>

            <hr>
            
            <h5 class="mt-4">Contatos (Telefones, E-mails Adicionais)</h5>
            <div id="contatos_area">
                <?php 
                if (count($contatos) > 0) {
                    foreach ($contatos as $contato) {
                        ?>
                        <div class="row mb-2 contato-item">
                            <div class="col-md-4">
                                <select name="contato_tipo[]" class="form-control" required>
                                    <option value="">Selecione o Tipo</option>
                                    <option value="Celular" <?php echo is_selected('Celular', $contato['tipo']); ?>>Celular</option>
                                    <option value="Telefone" <?php echo is_selected('Telefone', $contato['tipo']); ?>>Telefone</option>
                                    <option value="Email" <?php echo is_selected('Email', $contato['tipo']); ?>>Email</option>
                                    <option value="Outro" <?php echo is_selected('Outro', $contato['tipo']); ?>>Outro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="contato_valor[]" class="form-control" placeholder="Valor do Contato" value="<?php echo htmlspecialchars($contato['valor_contato']); ?>" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remover-contato">Remover</button>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="row mb-2 contato-item">
                        <div class="col-md-4">
                            <select name="contato_tipo[]" class="form-control" required>
                                <option value="">Selecione o Tipo</option>
                                <option value="Celular">Celular</option>
                                <option value="Telefone">Telefone</option>
                                <option value="Email">Email</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="contato_valor[]" class="form-control" placeholder="Valor do Contato" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remover-contato" style="display:none;">Remover</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
            <button type="button" id="adicionar_contato" class="btn btn-sm btn-success mt-2 mb-4">
                <i class="fas fa-plus"></i> Adicionar Contato
            </button>

            <hr>

            <h5 class="mt-4">Chaves PIX</h5>
            <div id="pix_area">
                <?php 
                if (count($chaves_pix) > 0) {
                    foreach ($chaves_pix as $pix) {
                        ?>
                        <div class="row mb-2 pix-item">
                            <div class="col-md-4">
                                <select name="pix_tipo[]" class="form-control">
                                    <option value="">Selecione o Tipo</option>
                                    <option value="CPF" <?php echo is_selected('CPF', $pix['tipo']); ?>>CPF</option>
                                    <option value="CNPJ" <?php echo is_selected('CNPJ', $pix['tipo']); ?>>CNPJ</option>
                                    <option value="Email" <?php echo is_selected('Email', $pix['tipo']); ?>>Email</option>
                                    <option value="Telefone" <?php echo is_selected('Telefone', $pix['tipo']); ?>>Telefone</option>
                                    <option value="Aleatória" <?php echo is_selected('Aleatória', $pix['tipo']); ?>>Chave Aleatória</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="pix_chave[]" class="form-control" placeholder="Valor da Chave PIX" value="<?php echo htmlspecialchars($pix['chave']); ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remover-pix">Remover</button>
                            </div>
                        </div>
                        <?php
                    }
                } else { 
                    ?>
                    <div class="row mb-2 pix-item">
                        <div class="col-md-4">
                            <select name="pix_tipo[]" class="form-control">
                                <option value="">Selecione o Tipo</option>
                                <option value="CPF">CPF</option>
                                <option value="CNPJ">CNPJ</option>
                                <option value="Email">Email</option>
                                <option value="Telefone">Telefone</option>
                                <option value="Aleatória">Chave Aleatória</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="pix_chave[]" class="form-control" placeholder="Valor da Chave PIX">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remover-pix" style="display:none;">Remover</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
            <button type="button" id="adicionar_pix" class="btn btn-sm btn-success mt-2 mb-4">
                <i class="fas fa-plus"></i> Adicionar Chave PIX
            </button>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-warning btn-lg">Salvar Edições</button>
            </div>
        </form>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>