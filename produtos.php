<?php
include 'includes/header.php'; 


$filtro_nome = '';
$filtro_categoria = '';
$condicoes_sql = [];


$sql_categorias = "SELECT id, nome_categoria FROM categorias ORDER BY nome_categoria ASC";
$result_categorias = $conn->query($sql_categorias);
$categorias = [];
if ($result_categorias && $result_categorias->num_rows > 0) {
    while($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}


if (isset($_GET['filtro_nome']) && !empty(trim($_GET['filtro_nome']))) {
    $filtro_nome = $conn->real_escape_string(trim($_GET['filtro_nome']));
    $condicoes_sql[] = "p.nome_produto LIKE '%$filtro_nome%'";
}


if (isset($_GET['filtro_categoria']) && !empty($_GET['filtro_categoria'])) {
    $filtro_categoria = $conn->real_escape_string($_GET['filtro_categoria']);
    
   
    if ($filtro_categoria != 'all') {
        $condicoes_sql[] = "p.categoria_id = '$filtro_categoria'";
    }
}


$condicao_where = '';
if (count($condicoes_sql) > 0) {
    $condicao_where = " WHERE " . implode(" AND ", $condicoes_sql);
}


$sql = "SELECT 
            p.id, 
            p.nome_produto, 
            p.valor_venda, 
            p.estoque_atual, 
            c.nome_categoria, 
            u.sigla as unidade_sigla 
        FROM produtos p
        JOIN categorias c ON p.categoria_id = c.id
        JOIN unidades u ON p.unidade_id = u.id"
        . $condicao_where .
        " ORDER BY p.nome_produto ASC";
        
$result = $conn->query($sql);

?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Gerenciamento de Produtos</h1>

    <?php 
   
    include 'includes/alert_message.php'; 
    ?>

    <div class="card shadow mb-4">

        <div class="card-body pb-0">
            <form action="produtos.php" method="GET" class="mb-4">
                <div class="row align-items-end">
                    
                    <div class="col-md-5 mb-3">
                        <label for="filtro_nome">Buscar Produto por Nome</label>
                        <input type="text" id="filtro_nome" name="filtro_nome" class="form-control" 
                               placeholder="Ex: Maçã, Banana, Alface..." 
                               value="<?php echo htmlspecialchars($filtro_nome); ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="filtro_categoria">Filtrar por Categoria</label>
                        <select id="filtro_categoria" name="filtro_categoria" class="form-control">
                            <option value="all">Todas as Categorias</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($filtro_categoria == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nome_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-search"></i> Filtrar</button>
                    </div>
                </div>
                
                <?php if (!empty($filtro_nome) || !empty($filtro_categoria) && $filtro_categoria != 'all'): ?>
                    <div class="row">
                         <div class="col-12">
                            <a href="produtos.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="fas fa-undo"></i> Limpar Filtros</a>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Produtos</h6>
            <a href="cadastro_produto.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Novo Produto (Nova Compra)
            </a>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço Venda</th>
                            <th>Unidade</th>
                            <th>Estoque Atual</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                              
                                $valor_venda_formatado = "R$ " . number_format($row['valor_venda'], 2, ',', '.');
                                
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['nome_produto']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nome_categoria']) . "</td>";
                                echo "<td>" . $valor_venda_formatado . "</td>";
                                echo "<td>" . htmlspecialchars($row['unidade_sigla']) . "</td>";
                                
                               
                                $estoque_atual = $row['estoque_atual'];
                                $estoque_class = 'text-success'; 
                                if ($estoque_atual < 50) $estoque_class = 'text-warning';
                                if ($estoque_atual <= 10) $estoque_class = 'text-danger'; 

                                echo '<td class="font-weight-bold ' . $estoque_class . '">' . $estoque_atual . ' ' . htmlspecialchars($row['unidade_sigla']) . "</td>";
                                
                                echo "<td>";
                                
                               
                                echo '<a href="produto_visualizar.php?id=' . $row['id'] . '" class="btn btn-info btn-sm me-1" title="Visualizar"><i class="fas fa-eye"></i></a> ';
                                echo '<a href="produto_editar.php?id=' . $row['id'] . '" class="btn btn-warning btn-sm me-1" title="Editar"><i class="fas fa-edit"></i></a> ';
                                
                                echo '<a href="produto_excluir.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm(\'Tem certeza que deseja EXCLUIR o produto ' . htmlspecialchars($row['nome_produto']) . ' permanentemente?\');"><i class="fas fa-trash"></i></a>';
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>Nenhum produto encontrado com os critérios de busca.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>