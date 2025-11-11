<?php

include 'includes/db_connect.php'; 
$produtos = [];
$sql = "SELECT p.nome_produto, p.valor_venda, u.sigla AS unidade_sigla 
        FROM produtos p
        JOIN unidades u ON p.unidade_id = u.id
        ORDER BY p.nome_produto ASC"; 
        
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
}
$conn->close();

$data_atualizacao = date('d/m/Y H:i'); 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabela de Preços - <?php echo $data_atualizacao; ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f4f7f6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #28a745; text-align: center; margin-bottom: 5px; }
        .data-info { text-align: center; color: #6c757d; font-size: 0.9em; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background-color: #e9ecef; color: #495057; font-weight: bold; }
        .unidade { width: 15%; text-align: center; }
        .preco { width: 25%; text-align: right; font-weight: bold; color: #dc3545; }
        tr:hover { background-color: #f8f9fa; }
        @media (max-width: 600px) {
            .container { padding: 15px; }
            th, td { padding: 8px 10px; font-size: 0.9em; }
            .preco { font-size: 1em; }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Tabela de Preços - <?php echo strtoupper('HH Mekaru'); ?></h1>
        <div class="data-info">
            Lista de preços válida para o dia de hoje.
            Última atualização: <?php echo $data_atualizacao; ?>
        </div>

        <?php if (!empty($produtos)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th class="unidade">Unidade</th>
                        <th class="preco">Preço de Venda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['nome_produto']); ?></td>
                            <td class="unidade"><?php echo htmlspecialchars($p['unidade_sigla']); ?></td>
                            <td class="preco">R$ <?php echo number_format($p['valor_venda'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #dc3545;">Desculpe, a tabela de preços não está disponível no momento.</p>
        <?php endif; ?>

    </div>

</body>
</html>