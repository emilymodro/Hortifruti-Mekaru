<?php
include 'includes/header.php'; 

$message = '';
$status = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nova_categoria'])) {
    $nome = trim($_POST['nome_categoria']);
    if (!empty($nome)) {
        try {
            $sql = "INSERT INTO categorias (nome_categoria) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $nome);
            if ($stmt->execute()) {
                $message = "Categoria '{$nome}' cadastrada com sucesso!";
                $status = "success";
            } else {
                $message = "Erro ao cadastrar categoria. Nome já existe ou falha no DB.";
                $status = "error";
            }
            $stmt->close();
        } catch (Exception $e) {
            $message = "Erro de DB: " . $e->getMessage();
            $status = "error";
        }
    } else {
        $message = "O nome da categoria não pode ser vazio.";
        $status = "error";
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_del = (int)$_GET['id'];
    try {
        $sql = "DELETE FROM categorias WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_del);
        
        if ($stmt->execute()) {
            $message = "Categoria excluída com sucesso!";
            $status = "success";
        } else {
            $message = "Erro: Categoria não pode ser excluída. Existem produtos vinculados a ela.";
            $status = "error";
        }
        $stmt->close();
        header("Location: categorias.php?status=" . $status . "&message=" . urlencode($message));
        exit();
    } catch (Exception $e) {
        $message = "Erro ao excluir: " . $e->getMessage();
        $status = "error";
    }
}

$sql_select = "SELECT id, nome_categoria FROM categorias ORDER BY nome_categoria ASC";
$result = $conn->query($sql_select);
?>

<h1 class="h3 mb-4 text-gray-800">Gerenciamento de Categorias</h1>

<?php 
if (!empty($message)) {
    echo '<div class="alert alert-' . ($status == 'success' ? 'success' : 'danger') . ' alert-dismissible fade show" role="alert">' . htmlspecialchars($message) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
include 'includes/alert_message.php'; 
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Cadastrar Nova Categoria</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="categorias.php">
            <div class="row">
                <div class="col-md-6">
                    <label for="nome_categoria" class="form-label">Nome da Categoria (Ex: Frutas, Verduras)</label>
                    <input type="text" class="form-control" id="nome_categoria" name="nome_categoria" required>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" name="nova_categoria" class="btn btn-primary">Salvar Categoria</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Categorias Cadastradas</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome da Categoria</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['nome_categoria']) . "</td>";
                            echo "<td>";
                            echo '<a href="categorias.php?action=delete&id=' . $row['id'] . '" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm(\'ATENÇÃO: A categoria deve ser excluída? Se houver produtos vinculados, a exclusão falhará. \n\nTem certeza que deseja EXCLUIR ' . htmlspecialchars($row['nome_categoria']) . '?\');"><i class="fas fa-trash"></i> Excluir</a>';
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Nenhuma categoria cadastrada.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>