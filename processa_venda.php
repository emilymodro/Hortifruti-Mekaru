<?php
include 'includes/db_connect.php';

$destino = "venda_cadastro.php"; 
$status = "error";
$message = "Erro desconhecido ao cadastrar a venda.";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    $cliente_id = (int)$_POST['cliente_id'];
    $data_venda = $_POST['data_venda'];
    
    $vencimento_venda = empty($_POST['vencimento_venda']) ? NULL : $_POST['vencimento_venda'];
    
    $valor_total = (float)$_POST['valor_total'];
    $forma_pagamento = $_POST['forma_pagamento'];
    
   
    $valor_desconto = isset($_POST['valor_desconto']) ? (float)$_POST['valor_desconto'] : 0.00;
    
    $valor_total_bruto = isset($_POST['valor_total_bruto']) ? (float)$_POST['valor_total_bruto'] : ($valor_total + $valor_desconto);
    
  
    $nome_cliente_avulso_post = isset($_POST['nome_cliente_avulso']) ? $_POST['nome_cliente_avulso'] : NULL;
    
    $nome_avulso = (
        $cliente_id == 1 && 
        !empty(trim($nome_cliente_avulso_post))
    ) ? trim($nome_cliente_avulso_post) : NULL;

    $cpf_cnpj_avulso = (
        $cliente_id == 1 && 
        isset($_POST['cpf_cnpj_avulso']) && 
        !empty(trim($_POST['cpf_cnpj_avulso']))
    ) ? trim($_POST['cpf_cnpj_avulso']) : NULL;
    
    
    $itens = $_POST['itens'];

    if (empty($itens) || $valor_total < 0) { 
        $message = "A venda deve ter pelo menos um item e valor total maior ou igual a zero.";
        header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
        exit();
    }

   
    $conn->begin_transaction();
    $venda_id = 0; 

    try {
   
        $sql_insert_venda = "INSERT INTO vendas 
                             (cliente_id, data_venda, vencimento_venda, valor_total, forma_pagamento, nome_cliente_avulso, cpf_cnpj_avulso, valor_desconto) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"; 
        
        $stmt_insert = $conn->prepare($sql_insert_venda);
        
        
        if (!$stmt_insert->bind_param("issdsssd", 
            $cliente_id, 
            $data_venda, 
            $vencimento_venda, 
            $valor_total,
            $forma_pagamento, 
            $nome_avulso, 
            $cpf_cnpj_avulso,
            $valor_desconto)) { 
            
            throw new Exception("Erro de bind_param ao cadastrar a venda.");
        }
            
        if (!$stmt_insert->execute()) {
            throw new Exception("Erro ao cadastrar o cabeçalho da venda: " . $stmt_insert->error);
        }
        
        $venda_id = $conn->insert_id;
        $stmt_insert->close();
        
       
        $sql_item = "INSERT INTO itens_venda (venda_id, produto_id, quantidade, valor_unitario_venda, valor_total_item) 
                     VALUES (?, ?, ?, ?, ?)";
        $sql_estoque_aplicar = "UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ?";
        
        $stmt_item = $conn->prepare($sql_item);
        $stmt_estoque_aplicar = $conn->prepare($sql_estoque_aplicar);
        
        foreach ($itens as $item) {
            
            $produto_id = (int)$item['produto_id'];
            $quantidade = (float)$item['quantidade'];
            $valor_unitario_venda = (float)$item['valor_unitario_venda'];
            $valor_total_item = (float)$item['valor_total_item'];

            if ($produto_id <= 0 || $quantidade <= 0) {
                 continue;
            }

          
            $stmt_item->bind_param("iiddd", $venda_id, $produto_id, $quantidade, $valor_unitario_venda, $valor_total_item);
            if (!$stmt_item->execute()) {
                throw new Exception("Erro ao inserir item {$produto_id}.");
            }

            $stmt_estoque_aplicar->bind_param("di", $quantidade, $produto_id);
            if (!$stmt_estoque_aplicar->execute()) {
                throw new Exception("Erro ao aplicar baixa de estoque do produto {$produto_id}.");
            }
        }
        $stmt_item->close();
        $stmt_estoque_aplicar->close();
        
        
        $conn->commit();
        $message = "Venda #{$venda_id} cadastrada e estoque atualizado com sucesso!";
        $status = "success";
        $destino = "venda_listagem.php"; 
        
    } catch (Exception $e) {
        $conn->rollback();
        $mysql_error = isset($conn->error) ? $conn->error : "N/A";
        $message = "ERRO FATAL NO CADASTRO: " . $e->getMessage() . " | MySQL Error: " . $mysql_error . ". A transação foi desfeita.";
        $status = "error";
        $destino = "venda_cadastro.php"; 
    } finally {
        if (isset($conn)) {
             $conn->close();
        }
    }
    
} else {
    $message = "Acesso inválido ao script de processamento.";
}

header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
exit();
?>