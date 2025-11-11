<?php
session_start();
include 'includes/db_connect.php';

$destino = "perfil.php";
$status = "error";
$message = "Erro desconhecido ao atualizar o perfil.";

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true || !isset($_SESSION['usuario_id'])) { // CORREÇÃO da chave para 'usuario_id'
    $message = "Sessão inválida. Faça login novamente.";
    header("Location: login.php?status=" . $status . "&message=" . urlencode($message));
    exit();
}

$user_id = (int)$_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $foto_caminho = NULL; 
    
    if (empty($nome) || empty($email)) {
        $message = "Nome e E-mail são obrigatórios.";
        header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
        exit();
    }

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        
        $file = $_FILES['foto_perfil'];
        $upload_dir = "uploads/usuarios/";
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $novo_nome = "user_" . $user_id . "_" . time() . "." . $extensao;
        $destino_arquivo = $upload_dir . $novo_nome;

        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extensao, $tipos_permitidos)) {
            $message = "Tipo de arquivo não permitido. Use JPG, JPEG, PNG ou GIF.";
            header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
            exit();
        }
        
        if (move_uploaded_file($file['tmp_name'], $destino_arquivo)) {
            $foto_caminho = $destino_arquivo;
            
            $sql_old = "SELECT foto_perfil FROM usuarios WHERE id = ?";
            $stmt_old = $conn->prepare($sql_old);
            $stmt_old->bind_param("i", $user_id);
            $stmt_old->execute();
            $result_old = $stmt_old->get_result();
            $usuario_old = $result_old->fetch_assoc();
            $stmt_old->close();

            if ($usuario_old && $usuario_old['foto_perfil'] && file_exists($usuario_old['foto_perfil'])) {
                @unlink($usuario_old['foto_perfil']); 
            }
            
        } else {
            $message = "Erro ao fazer upload da foto. Atualizando apenas outros campos.";
            $status = "warning";
        }
    }
    
    $sql_update = "UPDATE usuarios SET nome = ?, email = ?";
    $bind_types = "ss";
    $bind_params = [$nome, $email];
    
    if ($foto_caminho !== NULL) {
        $sql_update .= ", foto_perfil = ?";
        $bind_types .= "s";
        $bind_params[] = $foto_caminho;
    }
    
    $sql_update .= " WHERE id = ?";
    $bind_types .= "i";
    $bind_params[] = $user_id;
    
    $stmt_update = $conn->prepare($sql_update);
    
    if (!call_user_func_array([$stmt_update, 'bind_param'], array_merge([$bind_types], refValues($bind_params)))) {
        $message = "Erro interno ao preparar a atualização do banco de dados.";
        $status = "error";
    } else {
        if ($stmt_update->execute()) {
            $_SESSION['nome_usuario'] = $nome; 
            if ($foto_caminho !== NULL) {
                $_SESSION['foto_perfil'] = $foto_caminho; 
            }
            
            if ($status !== "warning") {
                $status = "success";
                $message = "Perfil atualizado com sucesso!";
            }
        } else {
            $message = "Erro ao atualizar dados no banco de dados: " . $stmt_update->error;
            $status = "error";
        }
    }
    
    $stmt_update->close();
    
} else {
    $message = "Método de requisição inválido.";
}

$conn->close();

function refValues($arr){
    if (strnatcmp(phpversion(),'5.6') >= 0)
    {
        return $arr;
    }
    $refs = array();
    foreach($arr as $key => $value)
        $refs[$key] = &$arr[$key];
    return $refs;
}

header("Location: " . $destino . "?status=" . $status . "&message=" . urlencode($message));
exit();

?>