<?php
include 'includes/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de venda inválido.");
}

$venda_id = $_GET['id'];
$venda = null;
$itens_venda = [];

$sql_venda = "SELECT 
                 v.id, 
                 v.data_venda, 
                 v.vencimento_venda, 
                 v.valor_total, 
                 v.valor_desconto, 
                 v.forma_pagamento,
                 v.data_cadastro,
                 v.nome_cliente_avulso, 
                 v.cpf_cnpj_avulso,   
                 c.nome_cliente,
                 c.cnpj_cpf
               FROM vendas v
               LEFT JOIN clientes c ON v.cliente_id = c.id
               WHERE v.id = ?";
             
if ($stmt = $conn->prepare($sql_venda)) {
    $stmt->bind_param("i", $venda_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $venda = $result->fetch_assoc();
    } else {
        die("Venda não encontrada.");
    }
    $stmt->close();
}

$sql_itens = "SELECT 
                 iv.quantidade,
                 iv.valor_unitario_venda,
                 iv.valor_total_item,
                 p.nome_produto,
                 u.sigla AS unidade_sigla
               FROM itens_venda iv
               JOIN produtos p ON iv.produto_id = p.id
               JOIN unidades u ON p.unidade_id = u.id
               WHERE iv.venda_id = ?";

$total_bruto = 0; 
if ($stmt_itens = $conn->prepare($sql_itens)) {
    $stmt_itens->bind_param("i", $venda_id);
    $stmt_itens->execute();
    $result_itens = $stmt_itens->get_result();
    while($row = $result_itens->fetch_assoc()) {
        $itens_venda[] = $row;
        $total_bruto += $row['valor_total_item']; 
    }
    $stmt_itens->close();
}

$conn->close();

$data_venda_formatada = date("d/m/Y H:i:s", strtotime($venda['data_venda'])); 
$vencimento_formatado = $venda['vencimento_venda'] ? date("d/m/Y", strtotime($venda['vencimento_venda'])) : "À Vista";
$total_bruto_formatado = number_format($total_bruto, 2, ',', '.');
$desconto = $venda['valor_desconto'] ?? 0; 
$desconto_formatado = number_format($desconto, 2, ',', '.');
$valor_total_formatado = number_format($venda['valor_total'], 2, ',', '.'); 

if (!empty($venda['nome_cliente_avulso'])) {
    $nome_cliente = htmlspecialchars($venda['nome_cliente_avulso']) . ' (Avulso)';
    $cpf_cnpj = htmlspecialchars($venda['cpf_cnpj_avulso'] ?? 'Não Informado');
} else {
    $nome_cliente = htmlspecialchars($venda['nome_cliente'] ?? 'Cliente Padrão');
    $cpf_cnpj = htmlspecialchars($venda['cnpj_cpf'] ?? 'Não Informado');
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Venda #<?php echo $venda_id; ?></title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>

        

        .recibo-container {
            width: 300px; 
            margin: 0 auto;
            padding: 10px;
            font-family: monospace; 
            font-size: 11px;
            border: 1px dashed #ccc;
        }

       
        .cabecalho-rodape {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

       
        .tabela-itens {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .tabela-itens th, .tabela-itens td {
            padding: 2px 0;
            text-align: right;
            border: none;
        }
        .tabela-itens th:nth-child(1), .tabela-itens td:nth-child(1) {
            text-align: left; 
        }
        
        
        @media print {
            body * {
                visibility: hidden; 
            }
            .recibo-container, .recibo-container * {
                visibility: visible; 
            }
            .recibo-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%; 
                border: none;
                font-size: 10pt;
                padding: 5px;
            }
            
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="recibo-container">
    
    <div class="cabecalho-rodape">
        <span style="font-size: 14px;">HH Mekaru Hortifruti</span><br>
        Av. Benjamim Constant, s/n - Centro campinas<br>
        Telefone: (99) 99999-9999
    </div>

    <div style="text-align: center; margin: 10px 0; font-size: 12px; font-weight: bold;">
        RECIBO DE VENDA #<?php echo $venda_id; ?>
    </div>
    
    <hr style="border-top: 1px dashed #000; margin: 5px 0;">

    <div style="line-height: 1.5;">
        **Data/Hora:** <?php echo $data_venda_formatada; ?><br>
        **Cliente:** <?php echo $nome_cliente; ?><br>
        **CPF/CNPJ:** <?php echo $cpf_cnpj; ?><br>
        **Operador:** [NOME DO OPERADOR LOGADO] <br>
    </div>
    
    <hr style="border-top: 1px dashed #000; margin: 5px 0;">

    <table class="tabela-itens">
        <thead>
            <tr>
                <th style="text-align: left; width: 45%;">Produto</th>
                <th style="width: 15%;">Qtd</th>
                <th style="width: 20%;">Unit.</th>
                <th style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens_venda as $item): 
                $qtd_formatada = number_format($item['quantidade'], 2, ',', '.') . $item['unidade_sigla'];
                $unit_formatado = number_format($item['valor_unitario_venda'], 2, ',', '.');
                $total_item_formatado = number_format($item['valor_total_item'], 2, ',', '.');
            ?>
            <tr>
                <td style="text-align: left;"><?php echo htmlspecialchars($item['nome_produto']); ?></td>
                <td><?php echo $qtd_formatada; ?></td>
                <td><?php echo $unit_formatado; ?></td>
                <td><?php echo $total_item_formatado; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr style="border-top: 1px dashed #000; margin: 5px 0;">

    <div style="line-height: 1.5; font-weight: bold; text-align: right;">
        **SUBTOTAL (BRUTO):** R$ <?php echo $total_bruto_formatado; ?><br>
        **DESCONTO:** R$ <?php echo $desconto_formatado; ?> <br>
        <span style="font-size: 14px;">TOTAL A PAGAR: R$ <?php echo $valor_total_formatado; ?></span>
    </div>
    
    <hr style="border-top: 1px dashed #000; margin: 5px 0;">
    
    <div style="text-align: right; line-height: 1.5;">
        **Forma de Pgto.:** <?php echo htmlspecialchars($venda['forma_pagamento']); ?><br>
        **Vencimento:** <?php echo $vencimento_formatado; ?>
    </div>
    
    <div style="margin-top: 30px; padding-bottom: 20px; text-align: center;">
        <p style="border-top: 1px solid #000; width: 80%; margin: 40px auto 5px auto;"></p>
        <p style="margin: 0;">**Assinatura do Cliente / Recebemos a Mercadoria**</p>
    </div>

    <div class="cabecalho-rodape" style="border-top: 1px dashed #000; border-bottom: none; padding-top: 5px;">
        Obrigado e volte sempre!<br>
        Sistema Hortifruti V.1.0
    </div>
    
</div>

<div class="text-center mt-3 btn-print">
    <button onclick="window.print()" class="btn btn-success"><i class="fas fa-print"></i> Imprimir Recibo</button>
    <a href="venda_listagem.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
</div>

</body>
</html>