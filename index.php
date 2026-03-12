<?php
// Carrega a conexão com o banco
require_once 'config/conexao.php';

// Puxa o cabeçalho base e o CSS
require_once 'includes/header.php';

// Vamos fazer algumas consultas rápidas para preencher os "Cards" do painel inicial.
// Count das Turmas
$stmtTurmas = $conexao->query("SELECT COUNT(*) AS total FROM turmas");
$totalTurmas = $stmtTurmas->fetch(PDO::FETCH_ASSOC)['total'];

// Count dos Alunos
$stmtAlunos = $conexao->query("SELECT COUNT(*) AS total FROM alunos");
$totalAlunos = $stmtAlunos->fetch(PDO::FETCH_ASSOC)['total'];

// Count de Diários de Chamada hoje
$hoje = date('Y-m-d');
$stmtDiarios = $conexao->query("SELECT COUNT(*) AS total FROM diarios_chamada WHERE data_referencia = '$hoje'");
$chamadasHoje = $stmtDiarios->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="page-header animate-fade-in">
    <div>
        <h1 class="page-title">Painel Geral</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Resumo dos dados do seu sistema hoje.</p>
    </div>
    <!-- Botão de atalho rápido para ir às chamadas ou alunos -->
    <a href="iniciar_chamada.php" class="btn btn-primary">
        <i class="ph ph-plus-circle"></i> Iniciar Chamada
    </a>
</div>

<!-- Grade de Cards com numerais -->
<div class="dashboard-grid animate-fade-in" style="animation-delay: 0.1s;">
    <!-- Card Turmas -->
    <div class="card">
        <h3 class="card-title">Turmas Ativas</h3>
        <div class="card-value"><?= $totalTurmas; ?></div>
        <a href="turmas.php" style="font-size: 0.875rem; display: block; margin-top: 0.5rem;"><i class="ph ph-arrow-right"></i> Ver turmas</a>
    </div>

    <!-- Card Alunos -->
    <div class="card">
        <h3 class="card-title">Alunos Cadastrados</h3>
        <div class="card-value"><?= $totalAlunos; ?></div>
        <a href="alunos.php" style="font-size: 0.875rem; display: block; margin-top: 0.5rem;"><i class="ph ph-arrow-right"></i> Ver carômetro</a>
    </div>

    <!-- Card Chamadas de Hoje -->
    <div class="card">
        <h3 class="card-title">Chamadas Hoje</h3>
        <div class="card-value"><?= $chamadasHoje; ?></div>
        <a href="chamadas.php" style="font-size: 0.875rem; display: block; margin-top: 0.5rem;"><i class="ph ph-arrow-right"></i> Ver chamadas</a>
    </div>
</div>

<div class="card animate-fade-in" style="animation-delay: 0.2s;">
    <h3 class="card-title" style="color: var(--text-main);">Seja bem vindo(a)!</h3>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">
        Utilize o menu no topo para navegar entre as telas de gerenciamento de Turmas, Carômetro de Alunos e Diários de Chamada. O sistema foi desenvolvido focando na agilidade e clareza visual.
    </p>
</div>

<?php 
// Puxa o rodapé e o fechamento do HTML
require_once 'includes/footer.php'; 
?>
