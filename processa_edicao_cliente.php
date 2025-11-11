<?php
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $cliente_id = $_POST['cliente_id']; 
    $nome_cliente = trim($_POST['nome_cliente']);
    $nome_fantasia = trim($_POST['nome_fantasia']);
    $cnpj_cpf = trim($_POST['cnpj_cpf']);
    $inscricao_estadual = trim($_POST['inscricao_estadual'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');


    $contatos_tipos = $_POST['contato_tipo'] ?? [];
    $contatos_valores = $_POST['contato_valor'] ?? [];
    $pix_tipos = $_POST['pix_tipo'] ?? [];
    $pix_chaves = $_POST['pix_chave'] ?? [];

    $conn->begin_transaction(); 

    try {
        

        $sql_update_cliente = "UPDATE clientes SET nome_cliente = ?, nome_fantasia = ?, cnpj_cpf = ?, inscricao_estadual = ?, email = ?, endereco = ? WHERE id = ?";
        $stmt_cliente = $conn->prepare($sql_update_cliente);
        $stmt_cliente->bind_param("ssssssi", $nome_cliente, $nome_fantasia, $cnpj_cpf, $inscricao_estadual, $email, $endereco, $cliente_id);
        
        if (!$stmt_cliente->execute()) {
            throw new Exception("Erro ao atualizar dados principais do cliente: " . $stmt_cliente->error);
        }
        $stmt_cliente->close();


      
        
        $sql_delete_contatos = "DELETE FROM cliente_contatos WHERE cliente_id = ?";
        $stmt_delete_contatos = $conn->prepare($sql_delete_contatos);
        $stmt_delete_contatos->bind_param("i", $cliente_id);
        if (!$stmt_delete_contatos->execute()) {
             throw new Exception("Erro ao excluir contatos antigos: " . $stmt_delete_contatos->error);
        }
        $stmt_delete_contatos->close();
       
        $sql_insert_contato = "INSERT INTO cliente_contatos (cliente_id, tipo, valor_contato) VALUES (?, ?, ?)";
        $stmt_contato = $conn->prepare($sql_insert_contato);
        
        foreach ($contatos_tipos as $index => $tipo) {
            $valor = trim($contatos_valores[$index]);
            $tipo = trim($tipo);
            
            if (!empty($tipo) && !empty($valor)) {
                $stmt_contato->bind_param("iss", $cliente_id, $tipo, $valor);
                if (!$stmt_contato->execute()) {
                    throw new Exception("Erro ao inserir novo contato: " . $stmt_contato->error);
                }
            }
        }
        $stmt_contato->close();


     
   
        $sql_delete_pix = "DELETE FROM cliente_pix WHERE cliente_id = ?";
        $stmt_delete_pix = $conn->prepare($sql_delete_pix);
        $stmt_delete_pix->bind_param("i", $cliente_id);
        if (!$stmt_delete_pix->execute()) {
             throw new Exception("Erro ao excluir PIX antigos: " . $stmt_delete_pix->error);
        }
        $stmt_delete_pix->close();

        $sql_insert_pix = "INSERT INTO cliente_pix (cliente_id, tipo, chave) VALUES (?, ?, ?)";
        $stmt_pix = $conn->prepare($sql_insert_pix);
        
        foreach ($pix_tipos as $index => $tipo) {
            $chave = trim($pix_chaves[$index]);
            $tipo = trim($tipo);

            if (!empty($tipo) && !empty($chave)) {
                $stmt_pix->bind_param("iss", $cliente_id, $tipo, $chave);
                if (!$stmt_pix->execute()) {
                    throw new Exception("Erro ao inserir nova chave PIX: " . $stmt_pix->error);
                }
            }
        }
        $stmt_pix->close();


      
        $conn->commit();
        $message = "Cliente atualizado com sucesso!";
        $status = "success";

    } catch (Exception $e) {
       
        $conn->rollback();
        $message = "Erro na edição do cliente: " . $e->getMessage();
        $status = "error";
    }

    $conn->close();
    
   
    header("Location: clientes.php?status=" . $status . "&message=" . urlencode($message));
    exit();
} else {
    header("Location: clientes.php");
    exit();
}
?>