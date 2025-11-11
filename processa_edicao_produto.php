<?php
include 'includes/db_connect.php';

$destino = "produtos.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $produto_id = (int)$_POST['produto_id'];
    $nome_produto = trim($_POST['nome_produto']);
    $categoria_id = (int)$_POST['categoria_id'];
    $unidade_id = (int)$_POST['unidade_id'];
    $fornecedor_id = (int)$_POST['fornecedor_id'];
    $ncm = trim($_POST['ncm'] ?? '');
    $valor_compra = (float)$_POST['valor_compra'];
    $valor_venda = (float)$_POST['valor_venda'];
    $estoque_atual = (int)$_POST['estoque_atual']; 
    $data_compra = $_POST['data_compra'];

    $status = "error";
    $message = "Erro desconhecido ao processar a edição do produto.";
    $sql_update = "UPDATE produtos SET 
                    nome_produto = ?, 
                    categoria_id = ?, 
                    unidade_id = ?, 
                    fornecedor_id = ?, 
                    ncm = ?, 
                    valor_compra = ?, 
                    valor_venda = ?, 
                    estoque_atual = ?, 
                    data_compra = ? 
                    WHERE id = ?";
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("siiisddisi", 
                      $nome_produto, 
                      $categoria_id, 
                      $unidade_id, 
                      $fornecedor_id, 
                      $ncm, 
                      $valor_compra, 
                      $valor_venda, 
                      $estoque_atual, 
                      $data_compra, 
                      $produto_id);

    if ($stmt->execute()) {
        $message = "Produto '{$nome_produto}' editado com sucesso!";
        $status = "success";
    } else {
        $message = "Erro ao editar o produto: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();

    header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
    exit();

} else {
    header("Location: produtos.php");
    exit();
}
?>