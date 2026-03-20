<?php
// Exibe os erros na tela (em caso de problemas durante o desenvolvimento)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Pega apenas o nome do arquivo atual para saber qual página está ativa no menu
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carômetro Inteligente</title>
    
    <!-- Importação da fonte Inter do Google Fonts para um visual premium e moderno -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Ícones gratuitos do Phosphor Icons (visual mais limpo que fontawesome) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Nosso arquivo de estilos principal -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>

<!-- Transição de Carga de Página -->
<div id="loader-transicao" class="transicao-overlay ativa"></div>

<div class="background-animado">
    <div class="circulo-bg circulo-1"></div>
    <div class="circulo-bg circulo-2"></div>
    <div class="circulo-bg circulo-3"></div>
    <div class="triangulo-bg triangulo-1"></div>
    <div class="triangulo-bg triangulo-2"></div>
    <div class="triangulo-bg triangulo-3"></div>
</div>

<div class="recipiente-aplicativo">
    <!-- Barra de navegação do topo (Header) -->
    <header class="barra-navegacao">
        <a href="index.php" class="marca-barra-navegacao">
            <i class="ph ph-camera"></i>
            Carômetro
        </a>
        
        <nav class="links-navegacao">
            <a href="index.php" class="item-navegacao <?= ($paginaAtual == 'index.php') ? 'ativo' : '' ?>">
                <i class="ph ph-squares-four"></i> Painel
            </a>
            <a href="turmas.php" class="item-navegacao <?= ($paginaAtual == 'turmas.php') ? 'ativo' : '' ?>">
                <i class="ph ph-users-three"></i> Turmas
            </a>
            <a href="alunos.php" class="item-navegacao <?= ($paginaAtual == 'alunos.php' || $paginaAtual == 'carometro.php') ? 'ativo' : '' ?>">
                <i class="ph ph-identification-card"></i> Alunos
            </a>
            <a href="chamadas.php" class="item-navegacao <?= ($paginaAtual == 'chamadas.php') ? 'ativo' : '' ?>">
                <i class="ph ph-calendar-check"></i> Chamadas
            </a>
        </nav>
    </header>

    <!-- Área principal onde o conteúdo das páginas será injetado -->
    <main class="conteudo-principal">
