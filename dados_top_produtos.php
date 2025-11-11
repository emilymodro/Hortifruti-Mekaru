<?php
require_once 'includes/db_connect.php'; 

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$query = "
    SELECT 
        p.nome_produto AS produto, 
        SUM(iv.valor_total_item) AS total_vendido  -- CORRIGIDO AQUI!
    FROM 
        itens_venda iv
    INNER JOIN 
        produtos p ON iv.produto_id = p.id  -- NOTA: Assumindo p.id e iv.produto_id
    GROUP BY 
        p.nome_produto
    ORDER BY 
        total_vendido DESC
    LIMIT 5
";
$resultado = $conn->query($query); 

$dados_grafico = [
    'labels' => [], 
    'data' => []   
];

if ($resultado && $resultado->num_rows > 0) {
    while ($linha = $resultado->fetch_assoc()) {
        $dados_grafico['labels'][] = $linha['produto'];
        $dados_grafico['data'][] = (float)$linha['total_vendido'];
    }
}

echo json_encode($dados_grafico);


?>