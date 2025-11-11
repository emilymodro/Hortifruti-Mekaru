<?php
include 'includes/header.php'; 

$produtos = [];
$sql = "SELECT p.nome_produto, p.valor_venda, u.sigla AS unidade_sigla 
        FROM produtos p
        JOIN unidades u ON p.unidade_id = u.id
        ORDER BY p.nome_produto ASC"; 
        
$result = $conn->query($sql);
        
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
}
$conn->close();
$url_base = 'http://' . $_SERVER['HTTP_HOST'] . '/'; 
$url_compartilhamento = $url_base . 'hortifruti_system/tabela_precos_publica.php'; 


?>

<h1 class="h3 mb-4 text-gray-800">Tabela de Preços para Clientes</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Preços Atuais</h6>
        <div>
            <button type="button" class="btn btn-success btn-sm me-2" onclick="copiarLink('<?php echo $url_compartilhamento; ?>')">
                <i class="fas fa-share-alt"></i> Copiar Link de Compartilhamento
            </button>
            
            <a href="gerar_tabela_pdf.php" class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf"></i> Gerar PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">Link de Compartilhamento</h4>
            <p>Seus clientes podem visualizar a tabela de preços atualizada em tempo real através deste link:</p>
            <a href="<?php echo $url_compartilhamento; ?>" target="_blank" id="linkCompartilhamento">
                <strong><?php echo $url_compartilhamento; ?></strong>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Unidade</th>
                        <th>Preço de Venda (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($produtos)): ?>
                        <?php foreach ($produtos as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['nome_produto']); ?></td>
                                <td><?php echo htmlspecialchars($p['unidade_sigla']); ?></td>
                                <td>R$ <?php echo number_format($p['valor_venda'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">Nenhum produto cadastrado para gerar a tabela.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function copiarLink(link) {
        // Usa a API Clipboard para copiar o texto
        navigator.clipboard.writeText(link).then(function() {
            alert('Link copiado para a área de transferência com sucesso!');
        }, function(err) {
            console.error('Erro ao copiar o link: ', err);
            alert('Erro ao copiar o link. Por favor, copie manualmente: ' + link);
        });
    }
</script>

<?php 
include 'includes/footer.php'; 
?>