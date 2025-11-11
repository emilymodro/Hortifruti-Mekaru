<?php
include 'includes/db_connect.php';

$destino = "cadastro_produto.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nome_produto = trim($_POST['nome_produto']);
    $categoria_id = (int)$_POST['categoria_id'];
    $unidade_id = (int)$_POST['unidade_id'];
    $fornecedor_id = (int)$_POST['fornecedor_id'];
    $ncm = trim($_POST['ncm'] ?? '');
    $valor_compra = (float)$_POST['valor_compra'];
    $valor_venda = (float)$_POST['valor_venda'];
    $quantidade_comprada = (int)$_POST['quantidade_comprada'];
    $data_compra = $_POST['data_compra'];

    $status = "error";
    $message = "Erro desconhecido ao processar o produto.";

    $sql_check = "SELECT id, estoque_atual FROM produtos WHERE nome_produto = ? AND categoria_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("si", $nome_produto, $categoria_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $produto_existente = $result_check->fetch_assoc();
        $produto_id = $produto_existente['id'];
        $novo_estoque = $produto_existente['estoque_atual'] + $quantidade_comprada;

        $sql_update = "UPDATE produtos SET 
                        fornecedor_id = ?, 
                        ncm = ?, 
                        unidade_id = ?, 
                        valor_compra = ?, 
                        valor_venda = ?, 
                        quantidade_comprada = ?, 
                        estoque_atual = ?, 
                        data_compra = ? 
                        WHERE id = ?";
        
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("isiddiisi", 
                  $fornecedor_id, 
                  $ncm, 
                  $unidade_id, 
                  $valor_compra, 
                  $valor_venda, 
                  $quantidade_comprada, 
                  $novo_estoque,
                  $data_compra, 
                  $produto_id);

        if ($stmt->execute()) {
            $message = "Estoque do produto '{$nome_produto}' atualizado! Novo estoque: {$novo_estoque}.";
            $status = "success";
        } else {
            $message = "Erro ao atualizar o estoque: " . $stmt->error;
        }

    } else {
        
        $estoque_inicial = $quantidade_comprada;
        
        $sql_insert = "INSERT INTO produtos (nome_produto, categoria_id, unidade_id, fornecedor_id, ncm, valor_compra, valor_venda, quantidade_comprada, estoque_atual, data_compra) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("siiisddiis", 
                          $nome_produto, 
                          $categoria_id, 
                          $unidade_id, 
                          $fornecedor_id, 
                          $ncm, 
                          $valor_compra, 
                          $valor_venda, 
                          $quantidade_comprada, 
                          $estoque_inicial, 
                          $data_compra);
        
        if ($stmt->execute()) {
            $message = "Produto '{$nome_produto}' cadastrado com sucesso! Estoque inicial: {$estoque_inicial}.";
            $status = "success";
        } else {
            $message = "Erro ao cadastrar novo produto: " . $stmt->error;
        }
    }
    
    $stmt_check->close();
    if (isset($stmt)) $stmt->close();

    $destino = "produtos.php"; 

} else {
    $message = "Acesso inválido ao script de processamento.";
}

$conn->close();

header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
exit();
?>