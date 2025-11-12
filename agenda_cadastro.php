<?php
include 'includes/header.php'; 
include 'includes/db_connect.php'; 

$id = $_GET['id'] ?? null;
$titulo = "Novo Contato de Serviço";
$acao = "Cadastrar";
// ATUALIZAÇÃO: Adicionando chave_pix à array de contato
$contato = [
    'nome_contato' => '',
    'servico_prestado' => '',
    'telefone_contato' => '',
    'email_contato' => '',
    'observacoes' => '',
    'tipo_chave_pix' => '', // NOVO CAMPO: Tipo da Chave PIX
    'chave_pix' => ''       // NOVO CAMPO: Valor da Chave PIX
];

if ($id) {
    $id = (int) $id;
    $sql = "SELECT * FROM agenda_servicos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $contato = $result->fetch_assoc();
        $titulo = "Editar Contato: " . htmlspecialchars($contato['nome_contato']);
        $acao = "Salvar Alterações";
    } else {
        header("Location: agenda_listagem.php?status=error&message=Contato não encontrado.");
        exit;
    }
    $stmt->close();
}
$conn->close();
?>

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10 col-md-12">

        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <div class="p-5">
                    
                    <div class="text-center">
                        <h1 class="h4 text-gray-900 mb-4">
                            <i class="fas fa-address-book text-primary mr-2"></i> <?php echo $titulo; ?>
                        </h1>
                    </div>

                    <form class="user" action="processa_agenda.php" method="POST">
                        
                        <?php if ($id): ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="action" value="update">
                        <?php else: ?>
                            <input type="hidden" name="action" value="insert">
                        <?php endif; ?>

                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <label for="nome_contato">Nome / Empresa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-user" id="nome_contato" name="nome_contato" 
                                        value="<?php echo htmlspecialchars($contato['nome_contato']); ?>" required>
                            </div>
                            <div class="col-sm-6">
                                <label for="servico_prestado">Serviço Principal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-user" id="servico_prestado" name="servico_prestado"
                                        value="<?php echo htmlspecialchars($contato['servico_prestado']); ?>" placeholder="Ex: Eletricista, Encanador" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <label for="telefone_contato">Telefone</label>
                                <input type="text" class="form-control form-control-user" id="telefone_contato" name="telefone_contato"
                                        value="<?php echo htmlspecialchars($contato['telefone_contato']); ?>" placeholder="Ex: (99) 99999-9999">
                                <small class="text-muted">Formato opcional</small>
                            </div>
                            <div class="col-sm-6">
                                <label for="email_contato">E-mail</label>
                                <input type="email" class="form-control form-control-user" id="email_contato" name="email_contato"
                                        value="<?php echo htmlspecialchars($contato['email_contato']); ?>" placeholder="email@exemplo.com">
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3 text-gray-900">Informações de Pagamento (PIX)</h5>
                        <div class="form-group row">
                            <div class="col-sm-4 mb-3 mb-sm-0">
                                <label for="tipo_chave_pix">Tipo de Chave</label>
                                <select class="form-control" id="tipo_chave_pix" name="tipo_chave_pix">
                                    <option value="">Nenhum</option>
                                    <option value="CPF" <?php if ($contato['tipo_chave_pix'] == 'CPF') echo 'selected'; ?>>CPF</option>
                                    <option value="CNPJ" <?php if ($contato['tipo_chave_pix'] == 'CNPJ') echo 'selected'; ?>>CNPJ</option>
                                    <option value="Email" <?php if ($contato['tipo_chave_pix'] == 'Email') echo 'selected'; ?>>E-mail</option>
                                    <option value="Telefone" <?php if ($contato['tipo_chave_pix'] == 'Telefone') echo 'selected'; ?>>Telefone</option>
                                    <option value="Aleatoria" <?php if ($contato['tipo_chave_pix'] == 'Aleatoria') echo 'selected'; ?>>Chave Aleatória</option>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <label for="chave_pix">Valor da Chave PIX</label>
                                <input type="text" class="form-control form-control" id="chave_pix" name="chave_pix"
                                        value="<?php echo htmlspecialchars($contato['chave_pix']); ?>" placeholder="Insira o valor da chave PIX">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="observacoes">Observações / Detalhes</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="4" 
                                        placeholder="Anotações importantes sobre a pessoa, valor médio de serviço, ou melhores horários para contato."><?php echo htmlspecialchars($contato['observacoes']); ?></textarea>
                        </div>
                        
                        <div class="form-group pt-3">
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                <i class="fas fa-save mr-2"></i> <?php echo $acao; ?>
                            </button>
                        </div>
                        
                        <a href="agenda_listagem.php" class="btn btn-secondary btn-user btn-block">
                            <i class="fas fa-arrow-left mr-2"></i> Voltar para a Agenda
                        </a>

                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>