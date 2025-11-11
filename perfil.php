<?php
include 'includes/header.php'; 
include 'includes/db_connect.php'; 

$user_id = $_SESSION['usuario_id']; 
$sql_user = "SELECT nome, email, foto_perfil FROM usuarios WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$usuario = $result_user->fetch_assoc();
$stmt_user->close();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Meu Perfil</h1>
    
    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        <?php 
        $status_class = ($_GET['status'] == 'success') ? 'alert-success' : (($_GET['status'] == 'warning') ? 'alert-warning' : 'alert-danger');
        $message = htmlspecialchars($_GET['message']);
        ?>
        <div class="alert <?php echo $status_class; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary-green">Dados Pessoais</h6>
        </div>
        <div class="card-body">
            <form action="processa_perfil.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Foto de Perfil Atual:</label>
                    <div>
                        <?php 
                        $foto_path = $usuario['foto_perfil'] ? $usuario['foto_perfil'] : 'admin/img/perfil_default.png'; 
                        ?>
                        <img src="<?php echo htmlspecialchars($foto_path); ?>" 
                             alt="Foto de Perfil" 
                             class="rounded-circle mb-3" 
                             style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #286428;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="foto_perfil">Alterar Foto de Perfil (Opcional):</label>
                    <input type="file" class="form-control-file" id="foto_perfil" name="foto_perfil" accept="image/*">
                    <small class="form-text text-muted">Apenas arquivos de imagem (JPG, PNG) são permitidos. Máx. 2MB.</small>
                </div>

                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </form>
        </div>
    </div>
</div>

<?php 
// Fecha a conexão
$conn->close();
include 'includes/footer.php'; 
?>