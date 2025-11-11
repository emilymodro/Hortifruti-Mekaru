<?php
include 'includes/header.php'; 




$total_clientes = 0;
$sql_clientes = "SELECT COUNT(id) as total FROM clientes";
$result_clientes = $conn->query($sql_clientes);
if ($result_clientes && $row = $result_clientes->fetch_assoc()) {
    $total_clientes = $row['total'];
}


$total_fornecedores = 0;
$sql_fornecedores = "SELECT COUNT(id) as total FROM fornecedores";
$result_fornecedores = $conn->query($sql_fornecedores);
if ($result_fornecedores && $row = $result_fornecedores->fetch_assoc()) {
    $total_fornecedores = $row['total'];
}

$estoque_critico = 0;
$sql_critico = "SELECT COUNT(id) as total FROM produtos WHERE estoque_atual <= 0";
$result_critico = $conn->query($sql_critico);
if ($result_critico && $row = $result_critico->fetch_assoc()) {
    $estoque_critico = $row['total'];
}


$estoque_baixo = 0;
$sql_baixo = "SELECT COUNT(id) as total FROM produtos WHERE estoque_atual < estoque_minimo AND estoque_atual > 0";
$result_baixo = $conn->query($sql_baixo);
if ($result_baixo && $row = $result_baixo->fetch_assoc()) {
    $estoque_baixo = $row['total'];
}


$produtos_criticos = [];
$sql_top_criticos = "SELECT nome_produto, estoque_atual, estoque_minimo 
                     FROM produtos 
                     WHERE estoque_atual <= estoque_minimo AND estoque_atual <= 10 
                     ORDER BY estoque_atual ASC LIMIT 5";
$result_top_criticos = $conn->query($sql_top_criticos);
if ($result_top_criticos->num_rows > 0) {
    while($row = $result_top_criticos->fetch_assoc()) {
        $produtos_criticos[] = $row;
    }
}


?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Painel de Controle (Dashboard)</h1>
    </div>

    <div class="row">

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total de Clientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_clientes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total de Fornecedores
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_fornecedores; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck-moving fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Produtos com Estoque Baixo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estoque_baixo; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Produtos com Estoque Crítico (Zerado)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estoque_critico; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">📈 Vendas Mensais (Últimos 12 Meses)</h6>
                </div>
                <div class="card-body">
                    <canvas id="graficoVendasMensais"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Top 5 Produtos Mais Vendidos (em Valor)</h6>
                </div>
                <div class="card-body">
                    <canvas id="graficoProdutosVendidos"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">

        <div class="col-lg-12 mb-4"> 
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Atenção: Produtos para Reposição Imediata</h6>
                </div>
                <div class="card-body">
                    <?php if (count($produtos_criticos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Estoque Atual</th>
                                    <th>Estoque Mínimo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos_criticos as $prod): ?>
                                <tr class="<?php echo ($prod['estoque_atual'] <= 0) ? 'table-danger' : 'table-warning'; ?>">
                                    <td><?php echo htmlspecialchars($prod['nome_produto']); ?></td>
                                    <td><?php echo $prod['estoque_atual']; ?></td>
                                    <td><?php echo $prod['estoque_minimo']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="estoque.php" class="btn btn-danger btn-sm mt-3">Ver Todos os Itens Críticos</a>
                    <?php else: ?>
                    <p class="text-success">Nenhum produto em nível crítico de estoque. Bom trabalho!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    </div> </div> <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cores da Paleta Hortifruti
        const GREEN_PRIMARY = '#286428';
        const GREEN_LIGHT = '#4CAF50';
        const GREEN_DARK = '#1A421A';
        const YELLOW_LIME = '#7CB342';
        const GREEN_PASTEL = '#A5D6A7';

    
        fetch('dados_vendas_mensais.php') 
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao carregar dados de vendas: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.labels && data.data) {
                    const ctx = document.getElementById('graficoVendasMensais').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Total de Vendas (R$)',
                                data: data.data,
                                backgroundColor: 'rgba(40, 100, 40, 0.6)', 
                                borderColor: GREEN_PRIMARY,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao buscar ou desenhar o gráfico de vendas:', error);
            });

        fetch('dados_top_produtos.php') 
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao carregar dados do Top Produtos: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.labels && data.data && data.data.length > 0) {
                    const ctxProdutos = document.getElementById('graficoProdutosVendidos').getContext('2d');
                    new Chart(ctxProdutos, {
                        type: 'doughnut', 
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: [
                                    GREEN_PRIMARY, 
                                    GREEN_LIGHT, 
                                    GREEN_DARK, 
                                    YELLOW_LIME, 
                                    GREEN_PASTEL 
                                ],
                                hoverBackgroundColor: [
                                    GREEN_DARK, 
                                    '#388E3C', 
                                    '#0D270D',
                                    '#689F38',
                                    '#81C784'
                                ],
                                hoverBorderColor: "rgba(234, 236, 244, 1)",
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                }
                            }
                        }
                    });
                } else {

                    document.getElementById('graficoProdutosVendidos').parentNode.innerHTML = 
                        '<p class="text-info text-center mt-4">Ainda não há dados suficientes para o Top 5.</p>';
                }
            })
            .catch(error => {
                console.error('Erro ao buscar ou desenhar o gráfico de Top Produtos:', error);
            });
    });
</script>

<?php 
include 'includes/footer.php'; 
?>