<?php
include 'includes/db_connect.php';

$destino = "venda_listagem.php"; 
$status = "error";
$message = "Erro desconhecido ao editar a venda.";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $venda_id = (int)$_POST['venda_id']; 
    $cliente_id = (int)$_POST['cliente_id'];
    $data_venda = $_POST['data_venda'];
    
    $vencimento_venda = empty($_POST['vencimento_venda']) ? NULL : $_POST['vencimento_venda'];
    
    $valor_total = (float)$_POST['valor_total'];
    $forma_pagamento = $_POST['forma_pagamento'];
    
    $nome_avulso = (
        $cliente_id == 1 && 
        isset($_POST['nome_cliente_avulso']) && 
        !empty(trim($_POST['nome_cliente_avulso']))
    ) ? trim($_POST['nome_cliente_avulso']) : NULL;

    $cpf_cnpj_avulso = (
        $cliente_id == 1 && 
        isset($_POST['cpf_cnpj_avulso']) && 
        !empty(trim($_POST['cpf_cnpj_avulso']))
    ) ? trim($_POST['cpf_cnpj_avulso']) : NULL;
    
    $itens_novos = $_POST['itens'];

    if ($venda_id <= 0) {
         $message = "ID da Venda inválido para edição.";
         header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
         exit();
    }
    
    if (empty($itens_novos) || $valor_total <= 0) {
        $message = "A venda deve ter pelo menos um item e valor total maior que zero.";
        header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
        exit();
    }

    $conn->begin_transaction();

    try {
        
  
        
        $sql_select_itens = "SELECT produto_id, quantidade FROM itens_venda WHERE venda_id = ?";
        $stmt_select_itens = $conn->prepare($sql_select_itens);
        $stmt_select_itens->bind_param("i", $venda_id);
        $stmt_select_itens->execute();
        $result_itens = $stmt_select_itens->get_result();

        
        $sql_estoque_reverter = "UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id = ?";
        $stmt_estoque_reverter = $conn->prepare($sql_estoque_reverter);
        
        while ($item_antigo = $result_itens->fetch_assoc()) {
            $produto_id_antigo = (int)$item_antigo['produto_id'];
            $quantidade_antiga = (float)$item_antigo['quantidade'];

            
            $stmt_estoque_reverter->bind_param("di", $quantidade_antiga, $produto_id_antigo);
            if (!$stmt_estoque_reverter->execute()) {
                throw new Exception("Erro ao reverter estoque do produto {$produto_id_antigo}.");
            }
        }
        $stmt_estoque_reverter->close();
        $stmt_select_itens->close();
        
        
        $sql_delete_itens = "DELETE FROM itens_venda WHERE venda_id = ?";
        $stmt_delete_itens = $conn->prepare($sql_delete_itens);
        $stmt_delete_itens->bind_param("i", $venda_id);
        if (!$stmt_delete_itens->execute()) {
            throw new Exception("Erro ao deletar itens antigos da venda.");
        }
        $stmt_delete_itens->close();


    
        
        
        $sql_update_venda = "UPDATE vendas SET 
                             cliente_id = ?, data_venda = ?, vencimento_venda = ?, 
                             valor_total = ?, forma_pagamento = ?, nome_cliente_avulso = ?, 
                             cpf_cnpj_avulso = ? 
                             WHERE id = ?"; 
        
        $stmt_update = $conn->prepare($sql_update_venda);
        
        if (!$stmt_update->bind_param("issdsssi", 
            $cliente_id, 
            $data_venda, 
            $vencimento_venda, 
            $valor_total, 
            $forma_pagamento, 
            $nome_avulso,
            $cpf_cnpj_avulso,
            $venda_id)) {
            
            throw new Exception("Erro de bind_param ao atualizar a venda.");
        }
            
        if (!$stmt_update->execute()) {
            throw new Exception("Erro ao atualizar o cabeçalho da venda: " . $stmt_update->error);
        }
        $stmt_update->close();
        
        
        $sql_item = "INSERT INTO itens_venda (venda_id, produto_id, quantidade, valor_unitario_venda, valor_total_item) 
                     VALUES (?, ?, ?, ?, ?)";
        $sql_estoque_aplicar = "UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ?";
        
        $stmt_item = $conn->prepare($sql_item);
        $stmt_estoque_aplicar = $conn->prepare($sql_estoque_aplicar);
        
        foreach ($itens_novos as $item) {
            
            $produto_id = (int)$item['produto_id'];
            $quantidade = (float)$item['quantidade'];
            $valor_unitario_venda = (float)$item['valor_unitario_venda'];
            $valor_total_item = (float)$item['valor_total_item'];

            if ($produto_id <= 0 || $quantidade <= 0) {
                 continue;
            }

            $stmt_item->bind_param("iiddd", $venda_id, $produto_id, $quantidade, $valor_unitario_venda, $valor_total_item);
            if (!$stmt_item->execute()) {
                throw new Exception("Erro ao inserir novo item {$produto_id}.");
            }

            $stmt_estoque_aplicar->bind_param("di", $quantidade, $produto_id);
            if (!$stmt_estoque_aplicar->execute()) {
                throw new Exception("Erro ao aplicar nova baixa de estoque do produto {$produto_id}.");
            }
        }
        $stmt_item->close();
        $stmt_estoque_aplicar->close();
        
        
        $conn->commit();
        $message = "Venda #{$venda_id} editada e estoque reajustado com sucesso!";
        $status = "success";
        $destino = "venda_listagem.php?status=" . $status . "&message=" . urlencode($message);
        
    } catch (Exception $e) {
   
        $conn->rollback();
        $mysql_error = isset($conn->error) ? $conn->error : "N/A";
        $message = "ERRO FATAL NA EDIÇÃO: " . $e->getMessage() . " | MySQL Error: " . $mysql_error . ". A transação foi desfeita.";
        $status = "error";
        $destino = "venda_editar.php?id=" . $venda_id; 
    } finally {
        if (isset($conn)) {
        }
    }
    
} else {
    $message = "Acesso inválido ao script de processamento.";
}

header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
exit();
?>