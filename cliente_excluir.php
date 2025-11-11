<?php
include 'includes/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: clientes.php?status=error&message=ID de cliente inválido para exclusão.");
    exit();
}

$cliente_id = $_GET['id'];
$message = "";
$status = "error";

$conn->begin_transaction(); 

try {
    

    $sql_delete_contatos = "DELETE FROM cliente_contatos WHERE cliente_id = ?";
    $stmt_delete_contatos = $conn->prepare($sql_delete_contatos);
    $stmt_delete_contatos->bind_param("i", $cliente_id);
    
    if (!$stmt_delete_contatos->execute()) {
        throw new Exception("Erro ao excluir contatos: " . $stmt_delete_contatos->error);
    }
    $stmt_delete_contatos->close();
    

    $sql_delete_pix = "DELETE FROM cliente_pix WHERE cliente_id = ?";
    $stmt_delete_pix = $conn->prepare($sql_delete_pix);
    $stmt_delete_pix->bind_param("i", $cliente_id);

    if (!$stmt_delete_pix->execute()) {
        throw new Exception("Erro ao excluir chaves PIX: " . $stmt_delete_pix->error);
    }
    $stmt_delete_pix->close();



    $sql_delete_cliente = "DELETE FROM clientes WHERE id = ?";
    $stmt_delete_cliente = $conn->prepare($sql_delete_cliente);
    $stmt_delete_cliente->bind_param("i", $cliente_id);

    if (!$stmt_delete_cliente->execute()) {
        throw new Exception("Erro ao excluir cliente principal: " . $stmt_delete_cliente->error);
    }
    $stmt_delete_cliente->close();

    $conn->commit();
    $message = "Cliente e todos os dados relacionados excluídos com sucesso!";
    $status = "success";

} catch (Exception $e) {
    $conn->rollback();
    $message = "Falha ao excluir o cliente. Erro: " . $e->getMessage();
    $status = "error";
}

$conn->close();

header("Location: clientes.php?status=" . $status . "&message=" . urlencode($message));
exit();
?>