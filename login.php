<?php
session_start();
include 'includes/db_connect.php'; 

$mensagem_erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $conn->real_escape_string($_POST['email']);
    $senha_digitada = $_POST['senha'];

   
    $sql = "SELECT id, nome, senha, cargo, foto_perfil FROM usuarios WHERE email = '$email'";
    $resultado = $conn->query($sql);

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        $senha_hash_db = $usuario['senha'];

        if (password_verify($senha_digitada, $senha_hash_db)) {
            
            $_SESSION['usuario_logado'] = true;
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nome_usuario'] = $usuario['nome']; 
            $_SESSION['cargo'] = $usuario['cargo'];
            
            $_SESSION['foto_perfil'] = $usuario['foto_perfil']; 

            header("Location: index.php");
            exit();
        } else {
            $mensagem_erro = "E-mail ou senha incorretos.";
        }
    } else {
        $mensagem_erro = "E-mail ou senha incorretos.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Hortifruti - Login</title>
    <link href="admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="admin/css/sb-admin-2.min.css" rel="stylesheet">

<style>
        .bg-gradient-primary { 
            background-color: #286428 !important; 
            background-image: linear-gradient(180deg, #286428 10%, #1A421A 100%) !important;
            background-size: cover;
        }

        .btn-primary {
            background-color: #286428 !important;
            border-color: #286428 !important;
        }
        
        .btn-primary:hover {
            background-color: #1A421A !important;
            border-color: #1A421A !important;
        }

        .text-primary-green { 
            color: #286428 !important; 
        }
        
        .h4 {
            color: #4e73df; 
            font-weight: 700;
        }
        
        .bg-hortifruti-image {
            background: url('admin/img/img8.jpg') no-repeat center center fixed; 
            background-size: cover;
        }

        .card {
            box-shadow: none !important;
        }

    </style>
</head>

<body class="bg-hortifruti-image">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8"> 
            <div class="card border-0 my-5">
                <div class="card-body p-0">
                    
                    <div class="p-4"> 
                        <div class="text-center">
                            <img src="admin/img/logo.png" alt="Logo" style="width: 150px; margin-bottom: 20px;">
                            <h1 class="h4 text-gray-900 mb-4">Bem-vindo(a) ao <span class="text-primary-green">Hortifruti HH</span>!</h1>
                        </div>

                        <?php if ($mensagem_erro): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $mensagem_erro; ?>
                        </div>
                        <?php endif; ?>

                        <form class="user" method="POST" action="login.php">
                            <div class="form-group">
                                <input type="email" class="form-control form-control-user"
                                    id="exampleInputEmail" aria-describedby="emailHelp"
                                    placeholder="Digite seu Endereço de E-mail..." name="email" required>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-user"
                                    id="exampleInputPassword" placeholder="Senha" name="senha" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block" 
                                style="background-color: #286428 !important; border-color: #286428 !important;">
                                Login
                            </button>
                        </form>
                        
                        <hr>
                        
                        <div class="text-center">
                            <a class="small" href="#">Esqueceu a Senha?</a>
                        </div>
                        
                    </div> </div> </div> </div> </div> </div> <script src="admin/vendor/jquery/jquery.min.js"></script>
    <script src="admin/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>