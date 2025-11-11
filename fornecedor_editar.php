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

$sql_fornecedor = "SELECT nome_fornecedor, nome_fantasia FROM fornecedores WHERE id = ?";
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

function is_selected($valor_campo, $valor_atual) {
    return ($valor_campo === $valor_atual) ? 'selected' : '';
}
?>

<h1 class="h3 mb-4 text-gray-800">Editar Fornecedor: <?php echo htmlspecialchars($fornecedor['nome_fornecedor']); ?></h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Informações Básicas e Contato</h6>
    </div>
    <div class="card-body">
        <form action="processa_edicao_fornecedor.php" method="POST">
            
            <input type="hidden" name="fornecedor_id" value="<?php echo $fornecedor_id; ?>">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nome_fornecedor" class="form-label">Nome do Fornecedor (Razão Social)</label>
                    <input type="text" class="form-control" id="nome_fornecedor" name="nome_fornecedor" value="<?php echo htmlspecialchars($fornecedor['nome_fornecedor']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                    <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia" value="<?php echo htmlspecialchars($fornecedor['nome_fantasia']); ?>">
                </div>
            </div>

            <hr>
            
            <h5 class="mt-4">Contatos (Telefones, E-mails)</h5>
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

<script>
$(document).ready(function() {
    function getNovoContatoHtml() {
        return `
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
                    <button type="button" class="btn btn-danger btn-sm remover-contato">Remover</button>
                </div>
            </div>
        `;
    }

    function getNovaPixHtml() {
        return `
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
                    <button type="button" class="btn btn-danger btn-sm remover-pix">Remover</button>
                </div>
            </div>
        `;
    }

    $('#adicionar_contato').click(function() {
        $('#contatos_area').append(getNovoContatoHtml());
        updateRemoveButtons('#contatos_area', '.remover-contato');
    });

    $(document).on('click', '.remover-contato', function() {
        $(this).closest('.contato-item').remove();
        updateRemoveButtons('#contatos_area', '.remover-contato');
    });

    $('#adicionar_pix').click(function() {
        $('#pix_area').append(getNovaPixHtml());
        updateRemoveButtons('#pix_area', '.remover-pix');
    });

    $(document).on('click', '.remover-pix', function() {
        $(this).closest('.pix-item').remove();
        updateRemoveButtons('#pix_area', '.remover-pix');
    });
    
    function updateRemoveButtons(area_id, btn_class) {
        if ($(area_id).find('.row.mb-2').length > 1) { 
            $(area_id).find(btn_class).show();
        } else {
            if ($(area_id).find('.row.mb-2').length === 1) {
                $(area_id).find(btn_class).hide();
            } else {
                $(area_id).find(btn_class).show(); 
            }
        }
    }
    
    updateRemoveButtons('#contatos_area', '.remover-contato');
    updateRemoveButtons('#pix_area', '.remover-pix');
});
</script>

<?php 
include 'includes/footer.php'; 
?>