<?php

// Linha 4 CORRIGIDA: Usa o nome correto do arquivo: db_connect.php
require_once 'includes/db_connect.php'; 

// Prevenção de cache e definição de cabeçalho JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Query para somar o total de vendas por mês nos últimos 12 meses
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

$resultado = $conn->query($query); // Esta é a linha ~25 onde a query é executada

$dados_grafico = [
    'labels' => [], // Nomes dos meses (Eixo X)
    'data' => []    // Valores de venda (Eixo Y)
];

if ($resultado && $resultado->num_rows > 0) {
    while ($linha = $resultado->fetch_assoc()) {
        // Formata o mês/ano para exibição (ex: 2025-01 -> Jan/25)
        $mes_formatado = date('M/y', strtotime($linha['mes_ano'] . '-01'));
        
        $dados_grafico['labels'][] = $mes_formatado;
        $dados_grafico['data'][] = (float)$linha['total'];
    }
}

// Retorna os dados como um objeto JSON
echo json_encode($dados_grafico);

// Nota: A conexão já foi fechada no index.php, 
// mas se for um script separado, é bom fechar aqui também.
// $conn->close(); 
?>