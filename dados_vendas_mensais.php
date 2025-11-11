<?php

require_once 'includes/db_connect.php'; 

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$query = "
    SELECT 
        DATE_FORMAT(data_venda, '%Y-%m') AS mes_ano, 
        SUM(valor_total) AS total 
    FROM 
        vendas
    WHERE
        data_venda >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY 
        mes_ano
    ORDER BY 
        mes_ano ASC
";

$resultado = $conn->query($query); 

$dados_grafico = [
    'labels' => [], 
    'data' => []    
];

if ($resultado && $resultado->num_rows > 0) {
    while ($linha = $resultado->fetch_assoc()) {
     
        $mes_formatado = date('M/y', strtotime($linha['mes_ano'] . '-01'));
        
        $dados_grafico['labels'][] = $mes_formatado;
        $dados_grafico['data'][] = (float)$linha['total'];
    }
}


echo json_encode($dados_grafico);


?>