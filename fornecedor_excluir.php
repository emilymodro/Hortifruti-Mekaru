<?php
include 'includes/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: fornecedores.php?status=error&message=ID de fornecedor inválido para exclusão.");
    exit();
}

$fornecedor_id = $_GET['id'];
$message = "";
$status = "error";

$conn->begin_transaction(); 

try {
    
    $sql_delete_contatos = "DELETE FROM fornecedor_contatos WHERE fornecedor_id = ?";
    $stmt_delete_contatos = $conn->prepare($sql_delete_contatos);
    $stmt_delete_contatos->bind_param("i", $fornecedor_id);
    
    if (!$stmt_delete_contatos->execute()) {
        throw new Exception("Erro ao excluir contatos: " . $stmt_delete_contatos->error);
    }
    $stmt_delete_contatos->close();
    
    
    $sql_delete_pix = "DELETE FROM fornecedor_pix WHERE fornecedor_id = ?";
    $stmt_delete_pix = $conn->prepare($sql_delete_pix);
    $stmt_delete_pix->bind_param("i", $fornecedor_id);

    if (!$stmt_delete_pix->execute()) {
        throw new Exception("Erro ao excluir chaves PIX: " . $stmt_delete_pix->error);
    }
    $stmt_delete_pix->close();


    $sql_delete_fornecedor = "DELETE FROM fornecedores WHERE id = ?";
    $stmt_delete_fornecedor = $conn->prepare($sql_delete_fornecedor);
    $stmt_delete_fornecedor->bind_param("i", $fornecedor_id);

    if (!$stmt_delete_fornecedor->execute()) {
        throw new Exception("Erro ao excluir fornecedor principal: " . $stmt_delete_fornecedor->error);
    }
    $stmt_delete_fornecedor->close();

  
    $conn->commit();
    $message = "Fornecedor e todos os dados relacionados excluídos com sucesso!";
    $status = "success";

} catch (Exception $e) {
    $conn->rollback();
    $message = "Falha ao excluir o fornecedor. Erro: " . $e->getMessage();
    $status = "error";
}

$conn->close();

header("Location: fornecedores.php?status=" . $status . "&message=" . urlencode($message));
exit();
?>