<?php
include 'includes/db_connect.php';

$destino = "venda_listagem.php";
$message = "Erro desconhecido ao processar a exclusão.";
$status = "error";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . $destino . "?status=error&message=ID de venda inválido.");
    exit();
}

$venda_id = $_GET['id'];


$conn->begin_transaction();

try {
    
 
    $sql_itens = "SELECT produto_id, quantidade FROM itens_venda WHERE venda_id = ?";
    $stmt_itens = $conn->prepare($sql_itens);
    $stmt_itens->bind_param("i", $venda_id);
    $stmt_itens->execute();
    $result_itens = $stmt_itens->get_result();
    
    $itens_para_reverter = [];
    while($row = $result_itens->fetch_assoc()) {
        $itens_para_reverter[] = $row;
    }
    $stmt_itens->close();
    
    if (empty($itens_para_reverter)) {

        throw new Exception("Venda não possui itens registrados. Exclusão abortada por segurança.");
    }
    
  
    $sql_reverter_estoque = "UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id = ?";
    $stmt_reverter = $conn->prepare($sql_reverter_estoque);
    
    foreach ($itens_para_reverter as $item) {
        $quantidade = (float)$item['quantidade'];
        $produto_id = (int)$item['produto_id'];

 
        $stmt_reverter->bind_param("di", $quantidade, $produto_id);
        
        if (!$stmt_reverter->execute()) {
            throw new Exception("Erro ao reverter estoque do produto {$produto_id}.");
        }
    }
    $stmt_reverter->close();
    

    $sql_delete_venda = "DELETE FROM vendas WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete_venda);
    $stmt_delete->bind_param("i", $venda_id);
    
    if (!$stmt_delete->execute()) {
        throw new Exception("Erro ao excluir o registro da venda.");
    }
    
    $stmt_delete->close();
    

    $conn->commit();
    $message = "Venda #{$venda_id} excluída e estoque revertido com sucesso!";
    $status = "success";
    
} catch (Exception $e) {

    $conn->rollback();
    $message = "ERRO FATAL NA EXCLUSÃO: " . $e->getMessage() . ". A venda e o estoque não foram alterados.";
    $status = "error";
    
} finally {
    $conn->close();
}


header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
exit();
?>