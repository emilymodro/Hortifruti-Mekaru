<?php
include 'includes/header.php'; 
include 'includes/db_connect.php'; 

$status = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';

$contatos = [];
$sql = "SELECT * FROM agenda_servicos ORDER BY nome_contato ASC";
$result = $conn->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        $contatos[] = $row;
    }
} else {
    $error_message = "Erro ao buscar contatos: " . $conn->error;
}
$conn->close();
?>

<h1 class="h3 mb-4 text-gray-800">Agenda de Prestadores de Serviços</h1>

<?php if ($status == 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
<?php elseif ($status == 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-warning"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Contatos Cadastrados</h6>
        <a href="agenda_cadastro.php" class="btn btn-success btn-icon-split">
            <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
            <span class="text">Novo Contato</span>
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th style="width: 25%;">Nome / Empresa</th>
                        <th style="width: 20%;">Serviço Principal</th>
                        <th style="width: 15%;">Telefone</th>
                        <th style="width: 30%;">Observações</th>
                        <th style="width: 10%;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contatos)): ?>
                        <?php foreach ($contatos as $contato): ?>
                        <tr class="align-middle">
                            <td class="font-weight-bold"><?php echo htmlspecialchars($contato['nome_contato']); ?></td>
                            <td><span class="badge bg-info text-white p-2"><?php echo htmlspecialchars($contato['servico_prestado']); ?></span></td>
                            <td><?php echo htmlspecialchars($contato['telefone_contato']); ?></td>
                            <td class="text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($contato['observacoes']); ?></td>
                            <td>
                                <a href="agenda_cadastro.php?id=<?php echo $contato['id']; ?>" class="btn btn-warning btn-sm" title="Editar Contato">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="processa_agenda.php?action=delete&id=<?php echo $contato['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este contato?')" title="Excluir Contato">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-4">
                                <i class="fas fa-info-circle"></i> Nenhuma prestador de serviço cadastrado. 
                                <a href="agenda_cadastro.php" class="font-weight-bold">Clique aqui para adicionar um.</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>