<?php
include 'includes/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: produtos.php?status=error&message=ID de produto inválido para exclusão.");
    exit();
}

$produto_id = $_GET['id'];
$message = "";
$status = "error";

try {
    
    $sql_delete_produto = "DELETE FROM produtos WHERE id = ?";
    $stmt_delete_produto = $conn->prepare($sql_delete_produto);
    $stmt_delete_produto->bind_param("i", $produto_id);

    if ($stmt_delete_produto->execute()) {
        
        if ($stmt_delete_produto->affected_rows > 0) {
            $message = "Produto excluído (e seu estoque removido) com sucesso!";
            $status = "success";
        } else {
            $message = "Produto não encontrado, ou já foi excluído.";
            $status = "warning";
        }
    } else {
        throw new Exception("Erro ao excluir o produto: " . $stmt_delete_produto->error);
    }
    
    $stmt_delete_produto->close();

} catch (Exception $e) {
    $message = "Falha ao excluir o produto. Erro: " . $e->getMessage();
    $status = "error";
}

$conn->close();

header("Location: produtos.php?status=" . $status . "&message=" . urlencode($message));
exit();
?>