<?php
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cadastro_fornecedor.php");
    exit();
}

$nome_fornecedor = trim($_POST['nome_fornecedor']);
$nome_fantasia = trim($_POST['nome_fantasia']);

if (empty($nome_fornecedor)) {
    die("Erro: O Nome do Fornecedor é obrigatório."); 
}

$sql_fornecedor = "INSERT INTO fornecedores (nome_fornecedor, nome_fantasia) VALUES (?, ?)";

if ($stmt_fornecedor = $conn->prepare($sql_fornecedor)) {
    $stmt_fornecedor->bind_param("ss", $nome_fornecedor, $nome_fantasia);
    
    if ($stmt_fornecedor->execute()) {
        $fornecedor_id = $conn->insert_id;
        
        $sucesso_total = true;

        if (isset($_POST['contato_tipo']) && isset($_POST['contato_valor'])) {
            $contato_tipos = $_POST['contato_tipo'];
            $contato_valores = $_POST['contato_valor'];

            
            $sql_contato = "INSERT INTO fornecedor_contatos (fornecedor_id, tipo, valor_contato) VALUES (?, ?, ?)";
            $stmt_contato = $conn->prepare($sql_contato);

            for ($i = 0; $i < count($contato_tipos); $i++) {
                $tipo = trim($contato_tipos[$i]);
                $valor = trim($contato_valores[$i]);
                
                if (!empty($tipo) && !empty($valor)) {
                    $stmt_contato->bind_param("iss", $fornecedor_id, $tipo, $valor);
                    if (!$stmt_contato->execute()) {
                        echo "Erro ao inserir contato: " . $stmt_contato->error . "<br>";
                        $sucesso_total = false;
                    }
                }
            }
            $stmt_contato->close();
        }

        if (isset($_POST['pix_tipo']) && isset($_POST['pix_chave'])) {
            $pix_tipos = $_POST['pix_tipo'];
            $pix_chaves = $_POST['pix_chave'];
            
            $sql_pix = "INSERT INTO fornecedor_pix (fornecedor_id, tipo, chave) VALUES (?, ?, ?)";
            $stmt_pix = $conn->prepare($sql_pix);
            
            for ($i = 0; $i < count($pix_tipos); $i++) {
                $tipo = trim($pix_tipos[$i]);
                $chave = trim($pix_chaves[$i]);
                
                if (!empty($tipo) && !empty($chave)) {
                    $stmt_pix->bind_param("iss", $fornecedor_id, $tipo, $chave);
                    if (!$stmt_pix->execute()) {
                        echo "Erro ao inserir chave Pix: " . $stmt_pix->error . "<br>";
                        $sucesso_total = false;
                    }
                }
            }
            $stmt_pix->close();
        }


        if ($sucesso_total) {
            header("Location: fornecedores.php?status=" . $status . "&message=" . urlencode($message));
            exit();
        } else {
            echo "Fornecedor principal salvo, mas houve erros em alguns contatos/Pix. (Verifique o log)";
        }

    } else {
        echo "Erro ao cadastrar fornecedor: " . $stmt_fornecedor->error;
    }

    $stmt_fornecedor->close();

} else {
    echo "Erro na preparação da query do fornecedor: " . $conn->error;
}

$conn->close();
?>