<?php

include 'includes/header.php'; 



$filtro = '';
$condicao_sql = '';

if (isset($_GET['filtro']) && !empty(trim($_GET['filtro']))) {
    $filtro = $conn->real_escape_string(trim($_GET['filtro']));
    
    
    $condicao_sql = " WHERE 
                        nome_cliente LIKE '%$filtro%' OR 
                        nome_fantasia LIKE '%$filtro%' OR 
                        cnpj_cpf LIKE '%$filtro%'";
}

$sql = "SELECT id, nome_cliente, nome_fantasia, cnpj_cpf, data_cadastro FROM clientes" . $condicao_sql . " ORDER BY nome_cliente ASC";
$result = $conn->query($sql);

?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Gerenciamento de Clientes</h1>

    <?php 
   
    include 'includes/alert_message.php'; 
    ?>

    <div class="card shadow mb-4">
        
        <div class="card-body pb-0">
            <form action="clientes.php" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-9 mb-3">
                        <label for="filtro_busca">Buscar Cliente</label>
                        <input type="text" id="filtro_busca" name="filtro" class="form-control" 
                               placeholder="Razão Social, Nome Fantasia ou CNPJ/CPF..." 
                               value="<?php echo htmlspecialchars($filtro); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-search"></i> Filtrar</button>
                    </div>
                </div>
                <?php if (!empty($filtro)): ?>
                    <div class="row">
                         <div class="col-12">
                            <a href="clientes.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="fas fa-undo"></i> Limpar Filtro</a>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Clientes</h6>
            <a href="cadastro_cliente.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Novo Cliente
            </a>
        </div>
        
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Razão Social</th>
                            <th>Nome Fantasia</th>
                            <th>CNPJ/CPF</th>
                            <th>Desde</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                        
                            while($row = $result->fetch_assoc()) {
                             
                                $data_cadastro = date("d/m/Y", strtotime($row['data_cadastro']));
                                
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['nome_cliente']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nome_fantasia']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['cnpj_cpf']) . "</td>";
                                echo "<td>" . $data_cadastro . "</td>";
                                echo "<td>";
                                
                              
                                echo '<a href="cliente_visualizar.php?id=' . $row['id'] . '" class="btn btn-info btn-sm me-1" title="Visualizar"><i class="fas fa-eye"></i></a> ';
                                echo '<a href="cliente_editar.php?id=' . $row['id'] . '" class="btn btn-warning btn-sm me-1" title="Editar"><i class="fas fa-edit"></i></a> ';
                                
                                echo '<a href="cliente_excluir.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm(\'Tem certeza que deseja EXCLUIR o cliente ' . htmlspecialchars($row['nome_cliente']) . ' e todos os seus dados?\');"><i class="fas fa-trash"></i></a>';
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                           
                            echo "<tr><td colspan='6' class='text-center'>Nenhum cliente encontrado com os critérios de busca.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 

include 'includes/footer.php'; 
?>