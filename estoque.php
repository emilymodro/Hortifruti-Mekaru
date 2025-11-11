<?php
include 'includes/header.php'; 


$filtro_nome = '';
$filtro_situacao = '';
$condicoes_sql = [];

if (isset($_GET['filtro_nome']) && !empty(trim($_GET['filtro_nome']))) {
    $filtro_nome = $conn->real_escape_string(trim($_GET['filtro_nome']));
    $condicoes_sql[] = "p.nome_produto LIKE '%$filtro_nome%'";
}

if (isset($_GET['filtro_situacao']) && !empty($_GET['filtro_situacao'])) {
    $filtro_situacao = $conn->real_escape_string($_GET['filtro_situacao']);
    
    switch ($filtro_situacao) {
        case 'baixo':
            $condicoes_sql[] = "p.estoque_atual < p.estoque_minimo AND p.estoque_atual > 0";
            break;
        case 'critico':
            $condicoes_sql[] = "p.estoque_atual <= 0";
            break;
        case 'ok':
            $condicoes_sql[] = "p.estoque_atual >= p.estoque_minimo";
            break;
    }
}

$condicao_where = '';
if (count($condicoes_sql) > 0) {
    $condicao_where = " WHERE " . implode(" AND ", $condicoes_sql);
}

$sql = "SELECT 
            p.id, 
            p.nome_produto, 
            p.estoque_atual, 
            p.estoque_minimo, 
            u.sigla as unidade_sigla 
        FROM produtos p
        JOIN unidades u ON p.unidade_id = u.id"
        . $condicao_where .
        " ORDER BY p.estoque_atual ASC";
        
$result = $conn->query($sql);

?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Visualização do Estoque</h1>

    <p class="mb-4">
        Esta tela mostra o inventário atual. O sistema de alerta considera o **Estoque Mínimo** (padrão 10).
    </p>

    <?php 
    include 'includes/alert_message.php'; 
    ?>

    <div class="card shadow mb-4">
        
        <div class="card-body pb-0">
            <form action="estoque.php" method="GET" class="mb-4">
                <div class="row align-items-end">
                    
                    <div class="col-md-5 mb-3">
                        <label for="filtro_nome">Buscar Produto por Nome</label>
                        <input type="text" id="filtro_nome" name="filtro_nome" class="form-control" 
                               placeholder="Ex: Maçã, Banana, Alface..." 
                               value="<?php echo htmlspecialchars($filtro_nome); ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="filtro_situacao">Filtrar por Situação</label>
                        <select id="filtro_situacao" name="filtro_situacao" class="form-control">
                            <option value="all">Todas as Situações</option>
                            <option value="critico" <?php echo ($filtro_situacao == 'critico') ? 'selected' : ''; ?>>Crítico (<= 0)</option>
                            <option value="baixo" <?php echo ($filtro_situacao == 'baixo') ? 'selected' : ''; ?>>Baixo (Abaixo do Mínimo, mas > 0)</option>
                            <option value="ok" <?php echo ($filtro_situacao == 'ok') ? 'selected' : ''; ?>>OK (Acima ou Igual ao Mínimo)</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-search"></i> Filtrar</button>
                    </div>
                </div>
                
                <?php if (!empty($filtro_nome) || !empty($filtro_situacao) && $filtro_situacao != 'all'): ?>
                    <div class="row">
                         <div class="col-12">
                            <a href="estoque.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="fas fa-undo"></i> Limpar Filtros</a>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Situação Atual do Inventário</h6>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Unidade</th>
                            <th>Estoque Mínimo</th>
                            <th>Estoque Atual</th>
                            <th>Situação</th>
                            <th>Ação Rápida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                
                                $estoque_atual = $row['estoque_atual'];
                                $estoque_minimo = $row['estoque_minimo'];
                                $produto_nome = htmlspecialchars($row['nome_produto']);
                                $sigla = htmlspecialchars($row['unidade_sigla']);
                                
                                $situacao = "OK";
                                $situacao_class = "badge bg-success";
                                $acao = 'Detalhes';
                                
                                if ($estoque_atual < $estoque_minimo && $estoque_atual > 0) {
                                    $situacao = "BAIXO";
                                    $situacao_class = "badge bg-warning text-dark";
                                    $acao = 'Comprar Mais';
                                } elseif ($estoque_atual <= 0) {
                                    $situacao = "CRÍTICO (ZERADO!)";
                                    $situacao_class = "badge bg-danger";
                                    $acao = 'Comprar Imediatamente';
                                }

                                echo "<tr>";
                                echo "<td>" . $produto_nome . "</td>";
                                echo "<td>" . $sigla . "</td>";
                                echo "<td>" . $estoque_minimo . " " . $sigla . "</td>";
                                
                                echo '<td class="font-weight-bold">';
                                echo $estoque_atual . ' ' . $sigla;
                                echo "</td>";
                                
                                echo '<td><span class="' . $situacao_class . '">' . $situacao . '</span></td>';
                                
                                echo '<td>';
                                if ($estoque_atual < $estoque_minimo) {
                                    echo '<a href="cadastro_produto.php" class="btn btn-sm btn-outline-danger"><i class="fas fa-shopping-cart"></i> ' . $acao . '</a>';
                                } else {
                                    echo '<a href="produto_visualizar.php?id=' . $row['id'] . '" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i> Detalhes</a>';
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Nenhum produto encontrado com os critérios de busca.</td></tr>";
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