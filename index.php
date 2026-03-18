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

<div class="cabecalho-pagina animacao-surgir">
    <div>
        <h1 class="titulo-pagina">Painel Geral</h1>
        <p class="subtitulo-pagina">Resumo dos dados do seu sistema hoje.</p>
    </div>
    <!-- Botão de atalho rápido para ir às chamadas ou alunos -->
    <a href="iniciar_chamada.php" class="botao botao-principal">
        <i class="ph ph-plus-circle"></i> Iniciar Chamada
    </a>
</div>

<!-- Grade de Cards com numerais -->
<div class="grade-painel animacao-surgir atraso-animacao-1">
    <!-- Card Turmas -->
    <div class="cartao">
        <h3 class="titulo-cartao">Turmas Ativas</h3>
        <div class="valor-cartao"><?= $totalTurmas; ?></div>
        <a href="turmas.php" class="link-bloco-pequeno"><i class="ph ph-arrow-right"></i> Ver turmas</a>
    </div>

    <!-- Card Alunos -->
    <div class="cartao">
        <h3 class="titulo-cartao">Alunos Cadastrados</h3>
        <div class="valor-cartao"><?= $totalAlunos; ?></div>
        <a href="alunos.php" class="link-bloco-pequeno"><i class="ph ph-arrow-right"></i> Ver carômetro</a>
    </div>

    <!-- Card Chamadas de Hoje -->
    <div class="cartao">
        <h3 class="titulo-cartao">Chamadas Hoje</h3>
        <div class="valor-cartao"><?= $chamadasHoje; ?></div>
        <a href="chamadas.php" class="link-bloco-pequeno"><i class="ph ph-arrow-right"></i> Ver chamadas</a>
    </div>
</div>

<div class="cartao animacao-surgir atraso-animacao-2">
    <h3 class="titulo-cartao texto-principal">Seja bem vindo(a)!</h3>
    <p class="texto-secundario margem-topo-media">
        Utilize o menu no topo para navegar entre as telas de gerenciamento de Turmas, Carômetro de Alunos e Diários de Chamada. O sistema foi desenvolvido focando na agilidade e clareza visual.
    </p>
</div>

<?php 
// Puxa o rodapé e o fechamento do HTML
require_once 'includes/footer.php'; 
?>
