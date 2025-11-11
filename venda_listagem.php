<?php
include 'includes/header.php'; 

$opcoes_limite = [10, 20, 50, 100, 200, 300, 500]; 
$limite_padrao = 10;

$limite_por_pagina = $limite_padrao;
if (isset($_GET['limite']) && is_numeric($_GET['limite']) && in_array((int)$_GET['limite'], $opcoes_limite)) {
    $limite_por_pagina = (int)$_GET['limite'];
}

$pagina_atual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $limite_por_pagina;


$filtro_data_inicio = '';
$filtro_data_fim = '';
$filtro_pagamento = '';
$filtro_cliente = '';
$condicoes_sql = [];
$get_params = ['limite' => $limite_por_pagina]; 


if (isset($_GET['data_inicio']) && !empty(trim($_GET['data_inicio']))) {
    $filtro_data_inicio = $conn->real_escape_string(trim($_GET['data_inicio']));
    $condicoes_sql[] = "v.data_venda >= '{$filtro_data_inicio}'";
    $get_params['data_inicio'] = $filtro_data_inicio;
}


if (isset($_GET['data_fim']) && !empty(trim($_GET['data_fim']))) {
    $filtro_data_fim = $conn->real_escape_string(trim($_GET['data_fim']));
    $condicoes_sql[] = "v.data_venda <= '{$filtro_data_fim} 23:59:59'"; 
    $get_params['data_fim'] = $filtro_data_fim;
}


if (isset($_GET['pagamento']) && !empty($_GET['pagamento']) && $_GET['pagamento'] != 'all') {
    $filtro_pagamento = $conn->real_escape_string($_GET['pagamento']);
    $condicoes_sql[] = "v.forma_pagamento = '{$filtro_pagamento}'";
    $get_params['pagamento'] = $filtro_pagamento;
}

if (isset($_GET['cliente']) && !empty(trim($_GET['cliente']))) {
    $filtro_cliente = $conn->real_escape_string(trim($_GET['cliente']));
    $condicoes_sql[] = "(c.nome_cliente LIKE '%$filtro_cliente%' OR v.nome_cliente_avulso LIKE '%$filtro_cliente%')";
    $get_params['cliente'] = $filtro_cliente;
}


$condicao_where = '';
if (count($condicoes_sql) > 0) {
    $condicao_where = " WHERE " . implode(" AND ", $condicoes_sql);
}


$get_params_url = http_build_query($get_params);
if (!empty($get_params_url)) {
    $get_params_url .= '&';
}


$sql_count = "SELECT COUNT(*) AS total FROM vendas v LEFT JOIN clientes c ON v.cliente_id = c.id" . $condicao_where;
$result_count = $conn->query($sql_count);
$row_count = $result_count->fetch_assoc();
$total_registros = $row_count['total'];


$total_paginas = ceil($total_registros / $limite_por_pagina);


if ($pagina_atual > $total_paginas && $total_registros > 0) {
    header("Location: venda_listagem.php?" . http_build_query(array_merge($get_params, ['pagina' => $total_paginas])));
    exit();
}


$sql = "SELECT v.id AS venda_id, v.data_venda, v.valor_total, v.forma_pagamento, v.nome_cliente_avulso, c.nome_cliente
        FROM vendas v
        LEFT JOIN clientes c ON v.cliente_id = c.id" 
    . $condicao_where 
    . " ORDER BY v.data_venda DESC, v.id DESC"
    . " LIMIT $limite_por_pagina OFFSET $offset"; 
            
$result = $conn->query($sql);

$formas_pagamento = [
    'Dinheiro', 
    'Pix', 
    'Debito', 
    'Credito', 
    'Boleto/Faturado'
];

?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Gerenciamento de Vendas</h1>

    <?php 
    include 'includes/alert_message.php'; 
    ?>

    <div class="card shadow mb-4">
        
        <div class="card-body pb-0">
            <form action="venda_listagem.php" method="GET" class="mb-4">
                <div class="row">
                    
                    <div class="col-md-2 mb-3">
                        <label for="limite">Itens por Pág.</label>
                        <select id="limite" name="limite" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($opcoes_limite as $limite_opt): ?>
                                <option value="<?php echo $limite_opt; ?>" 
                                    <?php echo ($limite_opt == $limite_por_pagina) ? 'selected' : ''; ?>>
                                    <?php echo $limite_opt; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="data_inicio">Data Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" 
                               value="<?php echo htmlspecialchars($filtro_data_inicio); ?>">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="data_fim">Data Fim</label>
                        <input type="date" id="data_fim" name="data_fim" class="form-control" 
                               value="<?php echo htmlspecialchars($filtro_data_fim); ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="pagamento">Pagamento</label>
                        <select id="pagamento" name="pagamento" class="form-control">
                            <option value="all">Todos</option>
                            <?php foreach ($formas_pagamento as $fp): ?>
                                <option value="<?php echo htmlspecialchars($fp); ?>" 
                                    <?php echo ($filtro_pagamento == $fp) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($fp); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="cliente">Buscar Cliente</label>
                        <div class="input-group">
                            <input type="text" id="cliente" name="cliente" class="form-control" 
                                       placeholder="Nome do Cliente..."
                                       value="<?php echo htmlspecialchars($filtro_cliente); ?>">
                            <button type="submit" class="btn btn-success"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>

                <?php if (!empty($filtro_data_inicio) || !empty($filtro_data_fim) || ($filtro_pagamento != '' && $filtro_pagamento != 'all') || !empty($filtro_cliente) || $limite_por_pagina != $limite_padrao): ?>
                    <div class="row">
                        <div class="col-12">
                             <a href="venda_listagem.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="fas fa-undo"></i> Limpar Filtros</a>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Histórico de Vendas</h6>
            <a href="venda_cadastro.php" class="btn btn-success btn-sm">
                <i class="fas fa-cash-register"></i> Nova Venda
            </a>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID Venda</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Valor Total</th>
                            <th>Pagamento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        
                        if ($result->num_rows > 0) {
                            $total_filtrado = 0;
                            while($row = $result->fetch_assoc()) {
                                
                                $total_filtrado += $row['valor_total'];
                                
                           
                                $nome_cliente_exibido = 'N/D'; 

                                if (!empty($row['nome_cliente_avulso'])) {
                                    $nome_cliente_exibido = htmlspecialchars($row['nome_cliente_avulso']) . ' (Avulso)';
                                } elseif (!empty($row['nome_cliente'])) {
                                    $nome_cliente_exibido = htmlspecialchars($row['nome_cliente']);
                                }

                             
                                $data_formatada = date("d/m/Y", strtotime($row['data_venda']));
                                $valor_formatado = "R$ " . number_format($row['valor_total'], 2, ',', '.');
                                
                              
                                $pagamento_class = ($row['forma_pagamento'] == 'Boleto/Faturado') ? 'badge bg-warning text-dark' : 'badge bg-success';
                                $pagamento_exibido = ($row['forma_pagamento'] == 'Boleto/Faturado') ? 'Pendente' : htmlspecialchars($row['forma_pagamento']);
                                
                                echo "<tr>";
                                echo "<td>" . $row['venda_id'] . "</td>";
                                echo "<td>" . $data_formatada . "</td>";
                                echo "<td>" . $nome_cliente_exibido . "</td>";
                                echo '<td class="font-weight-bold">' . $valor_formatado . "</td>";
                                echo '<td><span class="' . $pagamento_class . '">' . $pagamento_exibido . '</span></td>';
                                
                                echo "<td>";
                                
                             
                                echo '<a href="venda_recibo.php?id=' . $row['venda_id'] . '" class="btn btn-dark btn-sm me-1" title="Gerar Recibo/Cupom" target="_blank"><i class="fas fa-receipt"></i></a> ';
                                echo '<a href="venda_visualizar.php?id=' . $row['venda_id'] . '" class="btn btn-info btn-sm me-1" title="Visualizar Detalhes"><i class="fas fa-eye"></i></a> ';
                                echo '<a href="venda_editar.php?id=' . $row['venda_id'] . '" class="btn btn-warning btn-sm me-1" title="Editar Venda"><i class="fas fa-edit"></i></a> ';
                                echo '<a href="venda_excluir.php?id=' . $row['venda_id'] . '" class="btn btn-danger btn-sm" title="Excluir Venda e Reverter Estoque" onclick="return confirm(\'ATENÇÃO: Tem certeza que deseja EXCLUIR a Venda #' . $row['venda_id'] . ' ? Isso REVERTERÁ o estoque.\');"><i class="fas fa-trash"></i></a>';
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Nenhuma venda encontrada com os critérios de busca.</td></tr>";
                        }
                       
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total das Vendas Filtradas:</th>
                            <th class="text-success font-weight-bold">
                                <?php echo isset($total_filtrado) ? "R$ " . number_format($total_filtrado, 2, ',', '.') : "R$ 0,00"; ?>
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if ($total_paginas > 1): ?>
                <div class="row">
                    <div class="col-12 d-flex justify-content-center mt-3">
                        <nav aria-label="Navegação de Vendas">
                            <ul class="pagination">
                                
                                <li class="page-item <?php echo ($pagina_atual <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="venda_listagem.php?<?php echo $get_params_url; ?>pagina=<?php echo $pagina_atual - 1; ?>" aria-label="Anterior">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <li class="page-item <?php echo ($i == $pagina_atual) ? 'active' : ''; ?>">
                                        <a class="page-link" href="venda_listagem.php?<?php echo $get_params_url; ?>pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?php echo ($pagina_atual >= $total_paginas) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="venda_listagem.php?<?php echo $get_params_url; ?>pagina=<?php echo $pagina_atual + 1; ?>" aria-label="Próximo">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div class="col-12 text-center text-muted small">
                        Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?> (Total de <?php echo $total_registros; ?> registros)
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>