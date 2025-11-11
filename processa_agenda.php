<?php
include 'includes/db_connect.php'; 


$action = $_REQUEST['action'] ?? null;
$id = $_REQUEST['id'] ?? null;


function redirect($status, $message) {
    header("Location: agenda_listagem.php?status=" . urlencode($status) . "&message=" . urlencode($message));
    exit;
}

if ($conn->connect_error) {
    redirect('error', "Falha na conexão com o banco de dados: " . $conn->connect_error);
}


if ($action == 'delete' && $id) {
    $id = (int) $id;
    
    $sql = "DELETE FROM agenda_servicos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        redirect('error', 'Erro na preparação da exclusão: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        redirect('success', 'Contato excluído com sucesso!');
    } else {
        redirect('error', 'Erro ao excluir o contato: ' . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$action) {
    redirect('error', 'Ação inválida ou acesso direto negado.');
}

$nome_contato = trim($_POST['nome_contato'] ?? '');
$servico_prestado = trim($_POST['servico_prestado'] ?? '');
$telefone_contato = trim($_POST['telefone_contato'] ?? '');
$email_contato = trim($_POST['email_contato'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

if (empty($nome_contato) || empty($servico_prestado)) {
    redirect('error', 'Nome do Contato e Serviço Principal são campos obrigatórios.');
}


if ($action == 'insert') {
    $sql = "INSERT INTO agenda_servicos 
            (nome_contato, servico_prestado, telefone_contato, email_contato, observacoes) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        redirect('error', 'Erro na preparação do cadastro: ' . $conn->error);
    }

    $stmt->bind_param("sssss", 
        $nome_contato, 
        $servico_prestado, 
        $telefone_contato, 
        $email_contato, 
        $observacoes
    );
    
    if ($stmt->execute()) {
        redirect('success', 'Contato "' . htmlspecialchars($nome_contato) . '" cadastrado com sucesso!');
    } else {
        redirect('error', 'Erro ao cadastrar o contato: ' . $stmt->error);
    }
    
    $stmt->close();
}


elseif ($action == 'update' && $id) {
    $id = (int) $id;

    $sql = "UPDATE agenda_servicos SET 
            nome_contato = ?, 
            servico_prestado = ?, 
            telefone_contato = ?, 
            email_contato = ?, 
            observacoes = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        redirect('error', 'Erro na preparação da edição: ' . $conn->error);
    }

    $stmt->bind_param("sssssi", 
        $nome_contato, 
        $servico_prestado, 
        $telefone_contato, 
        $email_contato, 
        $observacoes,
        $id
    );

    if ($stmt->execute()) {
        redirect('success', 'Contato "' . htmlspecialchars($nome_contato) . '" atualizado com sucesso!');
    } else {
        redirect('error', 'Erro ao atualizar o contato: ' . $stmt->error);
    }
    
    $stmt->close();
}

$conn->close();
exit;
?>