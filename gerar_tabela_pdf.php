<?php

require 'vendor/autoload.php'; 
include 'includes/db_connect.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;


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


$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tabela de Preços - ' . date('d/m/Y') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; font-size: 10pt; }
        h1 { text-align: center; color: #333; font-size: 16pt; margin-bottom: 5px; }
        .data { text-align: center; margin-bottom: 20px; font-size: 9pt; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px 10px; 
            text-align: left;
        }
        th { background-color: #f2f2f2; font-weight: bold; color: #333; }
        .preco { text-align: right; width: 25%; }
        .unidade { width: 10%; text-align: center; }
    </style>
</head>
<body>
    <h1>TABELA DE PREÇOS</h1>
    <div class="data">Atualizado em: ' . date('d/m/Y H:i:s') . '</div>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th class="unidade">Unidade</th>
                <th class="preco">Preço de Venda (R$)</th>
            </tr>
        </thead>
        <tbody>
';

if (!empty($produtos)) {
    foreach ($produtos as $p) {
        $html .= '
            <tr>
                <td>' . htmlspecialchars($p['nome_produto']) . '</td>
                <td class="unidade">' . htmlspecialchars($p['unidade_sigla']) . '</td>
                <td class="preco">R$ ' . number_format($p['valor_venda'], 2, ',', '.') . '</td>
            </tr>
        ';
    }
} else {
    $html .= '
        <tr>
            <td colspan="3" style="text-align: center;">Nenhum produto cadastrado.</td>
        </tr>
    ';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';



$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false); 

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->render();

$filename = "Tabela_Precos_" . date('Ymd_His') . ".pdf";
$dompdf->stream($filename, ["Attachment" => false]);

exit(0);
?>