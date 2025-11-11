<?php
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nome_cliente = trim($_POST['nome_cliente']);
    $nome_fantasia = trim($_POST['nome_fantasia']);
    $cnpj_cpf = trim($_POST['cnpj_cpf']);
    $inscricao_estadual = trim($_POST['inscricao_estadual'] ?? ''); 
    $endereco = trim($_POST['endereco'] ?? '');
    
    $contatos_tipos = $_POST['contato_tipo'] ?? [];
    $contatos_valores = $_POST['contato_valor'] ?? [];
    $pix_tipos = $_POST['pix_tipo'] ?? [];
    $pix_chaves = $_POST['pix_chave'] ?? [];

    $conn->begin_transaction(); 

    try {
        
   
        $sql_cliente = "INSERT INTO clientes (nome_cliente, nome_fantasia, cnpj_cpf, inscricao_estadual, email, endereco) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_cliente = $conn->prepare($sql_cliente);
        
        $stmt_cliente->bind_param("ssssss", $nome_cliente, $nome_fantasia, $cnpj_cpf, $inscricao_estadual, $email, $endereco);
        
        if (!$stmt_cliente->execute()) {
            throw new Exception("Erro ao cadastrar o cliente principal: " . $stmt_cliente->error);
        }
        $cliente_id = $conn->insert_id; 
        $stmt_cliente->close();

        $sql_contato = "INSERT INTO cliente_contatos (cliente_id, tipo, valor_contato) VALUES (?, ?, ?)";
        $stmt_contato = $conn->prepare($sql_contato);
        
        foreach ($contatos_tipos as $index => $tipo) {
            $valor = trim($contatos_valores[$index]);
            $tipo = trim($tipo);
            
            
            if (!empty($tipo) && !empty($valor)) {
                $stmt_contato->bind_param("iss", $cliente_id, $tipo, $valor);
                if (!$stmt_contato->execute()) {
                    throw new Exception("Erro ao inserir contato: " . $stmt_contato->error);
                }
            }
        }
        $stmt_contato->close();

      
        $sql_pix = "INSERT INTO cliente_pix (cliente_id, tipo, chave) VALUES (?, ?, ?)";
        $stmt_pix = $conn->prepare($sql_pix);
        
        foreach ($pix_tipos as $index => $tipo) {
            $chave = trim($pix_chaves[$index]);
            $tipo = trim($tipo);

         
            if (!empty($tipo) && !empty($chave)) {
                $stmt_pix->bind_param("iss", $cliente_id, $tipo, $chave);
                if (!$stmt_pix->execute()) {
                    throw new Exception("Erro ao inserir chave PIX: " . $stmt_pix->error);
                }
            }
        }
        $stmt_pix->close();

        $conn->commit();
        $message = "Cliente cadastrado com sucesso!";
        $status = "success";

    } catch (Exception $e) {
       
        $conn->rollback();
        $message = "Erro no cadastro do cliente: " . $e->getMessage();
        $status = "error";
    }

    $conn->close();

    header("Location: clientes.php?status=" . $status . "&message=" . urlencode($message));
    exit();
} else {
    
    header("Location: cadastro_cliente.php");
    exit();
}
?>