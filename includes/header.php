<?php
session_start(); // Garante que a sessão seja iniciada

// Se o usuário não estiver logado, redireciona para a página de login
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    header("Location: login.php");
    exit();
}

// O restante do seu código PHP, incluindo a conexão com o banco de dados
include 'db_connect.php'; 

// -------------------------------------------------------------------------
// LÓGICA DE FOTO DE PERFIL (Para uso na Topbar)
// Define o caminho da foto de perfil. O valor padrão será usado se não houver foto na sessão.
$foto_perfil_url = 'admin/img/perfil_default.png'; 

// Verifica se a variável de sessão 'foto_perfil' existe e não está vazia.
// NOTA: Esta variável deve ser populada no seu script de login.php!
if (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])) {
    $foto_perfil_url = htmlspecialchars($_SESSION['foto_perfil']);
}
// -------------------------------------------------------------------------

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>HH Mekaru</title>

    <link href="admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link href="admin/css/sb-admin-2.min.css" rel="stylesheet">
<style>
    /* Cor Principal da Barra Lateral (Verde Escuro MODERADO: #286428) */
    .bg-gradient-primary {
        background-color: #286428 !important;
        background-image: linear-gradient(180deg, #286428 10%, #1A421A 100%) !important;
        background-size: cover;
    }

    /* Cor do botão primário */
    .btn-primary {
        background-color: #286428 !important;
        border-color: #286428 !important;
    }

    .btn-primary:hover {
        background-color: #1A421A !important;
        border-color: #1A421A !important;
    }

    /* Cores de Destaque / Sucesso (Verde Claro: #5CB85C) */
    .btn-success {
        background-color: #5CB85C !important;
        border-color: #5CB85C !important;
    }

    .btn-success:hover {
        background-color: #4CAF50 !important; 
        border-color: #4CAF50 !important;
    }

    /* --- REGRAS PARA REMOVER O FUNDO CINZA DA LOGO --- */
    .sidebar-brand {
        background-color: transparent !important;
        border: none !important;
        /* Aumenta a altura da barra da logo para acomodar o texto maior (opcional) */
        /* height: 4.5rem !important; */
    }

    .sidebar-brand .sidebar-brand-icon,
    .sidebar-brand .sidebar-brand-text {
        color: #FFFFFF !important;
        background-color: transparent !important;
    }

    .sidebar-brand-icon.rotate-n-15 {
        background-color: transparent !important;
    }

    /* --- NOVAS REGRAS PARA TAMANHO DA LOGO --- */
    .sidebar-brand-icon {
        /* Aumenta o tamanho do ícone de folha */
        font-size: 1.8rem !important; /* Tamanho original era 1.5rem ou 1.6rem */
    }
    
    .sidebar-brand-text {
        /* Aumenta o tamanho do texto "Hortifruti SYS" */
        font-size: 1.3rem !important; /* Tamanho original era 1rem ou 1.25rem */
    }
    .text-primary-green {
    color: #286428 !important; /* Seu verde principal */
    }
    
    /* Adiciona estilo para a foto de perfil na topbar */
    .img-profile {
        object-fit: cover;
        border: 2px solid #286428; /* Adiciona borda para destacar a foto */
    }

</style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
</head>

<body id="page-top">

    <div id="wrapper">

        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
             <div class="sidebar-brand-icon">
             <img src="admin/img/logo.png" alt="HH Mekaru Logo" style="height: 45px;"> 
             </div>
        </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Gestão
            </div>

            <li class="nav-item">
                <a class="nav-link" href="clientes.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Clientes</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="fornecedores.php">
                    <i class="fas fa-fw fa-truck"></i>
                    <span>Fornecedores</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="produtos.php">
                    <i class="fas fa-fw fa-apple-alt"></i>
                    <span>Produtos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="estoque.php">
                    <i class="fas fa-fw fa-warehouse"></i>
                    <span>Estoque</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="venda_listagem.php">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    <span>Vendas</span>
                </a>
            </li>
            <li class="nav-item">
                 <a class="nav-link" href="categorias.php">
                     <i class="fas fa-fw fa-tags"></i>
                     <span>Categorias</span>
                </a>
            </li>

            <li class="nav-item">
             <a class="nav-link" href="tabela_precos.php">
              <i class="fas fa-fw fa-money-bill-wave"></i> <span>Tabela de Preços</span>
              </a>
            </li>

            <li class="nav-item">
             <a class="nav-link" href="agenda_listagem.php">
              <i class="fas fa-fw fa-address-book"></i>
              <span>Agenda de Serviços</span>
             </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                   <ul class="navbar-nav ml-auto">

                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?php echo isset($_SESSION['nome_usuario']) ? $_SESSION['nome_usuario'] : 'Visitante'; ?>
                            </span>
                            
                            <?php if ($foto_perfil_url !== 'admin/img/perfil_default.png'): ?>
                                <img class="img-profile rounded-circle" 
                                     src="<?php echo $foto_perfil_url; ?>" 
                                     alt="Foto de Perfil"
                                     style="width: 2rem; height: 2rem; object-fit: cover;"> 
                            <?php else: ?>
                                <i class="fas fa-user-circle fa-2x text-primary-green"></i>
                            <?php endif; ?>
                            
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="perfil.php">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Perfil
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                Configurações
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Sair
                            </a>
                        </div>
                    </li>

                    </ul>

                </nav>
                <div class="container-fluid">