<?php
include 'includes/header.php'; 
?>

<h1 class="h3 mb-4 text-gray-800">Cadastro de Fornecedor</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informações Básicas e Contato</h6>
        </div>
        <div class="card-body">
            <form action="processa_fornecedor.php" method="POST">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nome_fornecedor" class="form-label">Nome do Fornecedor (Razão Social)</label>
                        <input type="text" class="form-control" id="nome_fornecedor" name="nome_fornecedor" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                        <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia">
                    </div>
                </div>

                <hr>
                
                <h5 class="mt-4">Contatos (Telefones, E-mails)</h5>
                <div id="contatos_area">
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
                </div>
                <button type="button" id="adicionar_contato" class="btn btn-sm btn-success mt-2 mb-4">
                    <i class="fas fa-plus"></i> Adicionar Contato
                </button>

                <hr>

                <h5 class="mt-4">Chaves PIX</h5>
                <div id="pix_area">
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
                </div>
                <button type="button" id="adicionar_pix" class="btn btn-sm btn-success mt-2 mb-4">
                    <i class="fas fa-plus"></i> Adicionar Chave PIX
                </button>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Salvar Fornecedor</button>
                </div>
            </form>
        </div>
    </div>

<script>
$(document).ready(function() {
    $('#adicionar_contato').click(function() {
        var novo_contato = `
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
        $('#contatos_area').append(novo_contato);
        updateRemoveButtons('#contatos_area', '.remover-contato');
    });

    $(document).on('click', '.remover-contato', function() {
        $(this).closest('.contato-item').remove();
        updateRemoveButtons('#contatos_area', '.remover-contato');
    });

    $('#adicionar_pix').click(function() {
        var nova_pix = `
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
        $('#pix_area').append(nova_pix);
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
            $(area_id).find(btn_class).hide();
        }
    }
    
    updateRemoveButtons('#contatos_area', '.remover-contato');
    updateRemoveButtons('#pix_area', '.remover-pix');
});
</script>

<?php 
include 'includes/footer.php'; 
?>