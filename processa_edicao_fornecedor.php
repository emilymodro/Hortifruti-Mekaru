<?php

include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    
    $fornecedor_id = $_POST['fornecedor_id']; 
    $nome_fornecedor = trim($_POST['nome_fornecedor']);
    $nome_fantasia = trim($_POST['nome_fantasia']);

    $contatos_tipos = $_POST['contato_tipo'] ?? [];
    $contatos_valores = $_POST['contato_valor'] ?? [];
    $pix_tipos = $_POST['pix_tipo'] ?? [];
    $pix_chaves = $_POST['pix_chave'] ?? [];

    $conn->begin_transaction(); 

    try {
        $sql_update_fornecedor = "UPDATE fornecedores SET nome_fornecedor = ?, nome_fantasia = ? WHERE id = ?";
        $stmt_fornecedor = $conn->prepare($sql_update_fornecedor);
        $stmt_fornecedor->bind_param("ssi", $nome_fornecedor, $nome_fantasia, $fornecedor_id);
        
        if (!$stmt_fornecedor->execute()) {
            throw new Exception("Erro ao atualizar dados principais do fornecedor: " . $stmt_fornecedor->error);
        }
        $stmt_fornecedor->close();

        $sql_delete_contatos = "DELETE FROM fornecedor_contatos WHERE fornecedor_id = ?";
        $stmt_delete_contatos = $conn->prepare($sql_delete_contatos);
        $stmt_delete_contatos->bind_param("i", $fornecedor_id);
        if (!$stmt_delete_contatos->execute()) {
             throw new Exception("Erro ao excluir contatos antigos: " . $stmt_delete_contatos->error);
        }
        $stmt_delete_contatos->close();
        
        $sql_insert_contato = "INSERT INTO fornecedor_contatos (fornecedor_id, tipo, valor_contato) VALUES (?, ?, ?)";
        $stmt_contato = $conn->prepare($sql_insert_contato);
        
        foreach ($contatos_tipos as $index => $tipo) {
            $valor = trim($contatos_valores[$index]);
            $tipo = trim($tipo);
            
            if (!empty($tipo) && !empty($valor)) {
                $stmt_contato->bind_param("iss", $fornecedor_id, $tipo, $valor);
                if (!$stmt_contato->execute()) {
                    throw new Exception("Erro ao inserir novo contato: " . $stmt_contato->error);
                }
            }
        }
        $stmt_contato->close();


       
        
        $sql_delete_pix = "DELETE FROM fornecedor_pix WHERE fornecedor_id = ?";
        $stmt_delete_pix = $conn->prepare($sql_delete_pix);
        $stmt_delete_pix->bind_param("i", $fornecedor_id);
        if (!$stmt_delete_pix->execute()) {
             throw new Exception("Erro ao excluir PIX antigos: " . $stmt_delete_pix->error);
        }
        $stmt_delete_pix->close();

        $sql_insert_pix = "INSERT INTO fornecedor_pix (fornecedor_id, tipo, chave) VALUES (?, ?, ?)";
        $stmt_pix = $conn->prepare($sql_insert_pix);
        
        foreach ($pix_tipos as $index => $tipo) {
            $chave = trim($pix_chaves[$index]);
            $tipo = trim($tipo);

            if (!empty($tipo) && !empty($chave)) {
                $stmt_pix->bind_param("iss", $fornecedor_id, $tipo, $chave);
                if (!$stmt_pix->execute()) {
                    throw new Exception("Erro ao inserir nova chave PIX: " . $stmt_pix->error);
                }
            }
        }
        $stmt_pix->close();


        $conn->commit();
        $message = "Fornecedor atualizado com sucesso!";
        $status = "success";

    } catch (Exception $e) {
        $conn->rollback();
        $message = "Erro na edição do fornecedor: " . $e->getMessage();
        $status = "error";
    }

    $conn->close();
    
    header("Location: fornecedores.php?status=" . $status . "&message=" . urlencode($message));
    exit();
} else {
    header("Location: fornecedores.php");
    exit();
}
?>