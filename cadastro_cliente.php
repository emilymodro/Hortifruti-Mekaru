<?php
include 'includes/header.php'; 
?>

<h1 class="h3 mb-4 text-gray-800">Cadastro de Cliente</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Informações Básicas, Endereço e Contato</h6>
    </div>
    <div class="card-body">
        <form action="processa_cliente.php" method="POST">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nome_cliente" class="form-label">Nome do Cliente (Razão Social)</label>
                    <input type="text" class="form-control" id="nome_cliente" name="nome_cliente" required>
                </div>
                <div class="col-md-6">
                    <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                    <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="cnpj_cpf" class="form-label">CNPJ/CPF</label>
                    <input type="text" class="form-control" id="cnpj_cpf" name="cnpj_cpf" required>
                </div>
                <div class="col-md-4">
                    <label for="inscricao_estadual" class="form-label">Inscrição Estadual</label>
                    <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual">
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">E-mail Principal</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <label for="endereco" class="form-label">Endereço Completo</label>
                    <textarea class="form-control" id="endereco" name="endereco" rows="2"></textarea>
                </div>
            </div>

            <hr>
            
            <h5 class="mt-4">Contatos (Telefones, E-mails Adicionais)</h5>
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
                <button type="submit" class="btn btn-primary btn-lg">Salvar Cliente</button>
            </div>
        </form>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>