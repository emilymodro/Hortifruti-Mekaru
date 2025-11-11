<?php
include 'includes/db_connect.php'; 

$nome = "Administrador Principal";
$email = "admin@hortifruti.com.br";
$senha_pura = "senhasegura123"; 
$cargo = "Admin"; 

$senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nome, email, senha, cargo) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Erro na preparação do SQL: " . $conn->error);
}


$stmt->bind_param("ssss", $nome, $email, $senha_hash, $cargo);

if ($stmt->execute()) {
    echo "<h2>✅ Sucesso!</h2>";
    echo "O usuário <b>" . htmlspecialchars($nome) . "</b> foi criado com sucesso!<br>";
    echo "Seu e-mail de login é: <b>" . htmlspecialchars($email) . "</b><br>";
    echo "O cargo atribuído é: <b>" . htmlspecialchars($cargo) . "</b><br>";
    echo "<br><b>AÇÃO CRÍTICA DE SEGURANÇA: DELETA ESTE ARQUIVO DO SERVIDOR AGORA!</b>";
} else {
    echo "<h2>❌ ERRO:</h2> Houve um erro na execução do SQL: " . $stmt->error . "<br>";
}

$stmt->close();
$conn->close();

?>