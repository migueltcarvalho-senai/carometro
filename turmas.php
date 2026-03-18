<?php
// Carrega a conexão com o banco
require_once 'config/conexao.php';

// Puxa o cabeçalho base e o CSS
require_once 'includes/header.php';

// Consulta todas as turmas cadastradas
try {
    $stmt = $conexao->query("SELECT * FROM turmas ORDER BY nome ASC");
    $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro = "Erro ao buscar turmas: " . $e->getMessage();
}
?>

<div class="cabecalho-pagina animacao-surgir">
    <div>
        <h1 class="titulo-pagina">Gerenciamento de Turmas</h1>
        <p class="subtitulo-pagina">Visualize e gerencie as turmas cadastradas no sistema.</p>
    </div>
    <!-- Botão para cadastrar uma nova turma -->
    <a href="cadastrar_turma.php" class="botao botao-principal">
        <i class="ph ph-plus"></i> Nova Turma
    </a>
</div>

<?php if (isset($erro)): ?>
    <div class="cartao alerta-erro">
        <p class="texto-perigo"><?= $erro; ?></p>
    </div>
<?php endif; ?>

<div class="grade-painel animacao-surgir atraso-animacao-1">
    <?php if (count($turmas) > 0): ?>
        <?php foreach ($turmas as $index => $turma): ?>
            <div class="cartao animacao-surgir cartao-aluno-atraso-<?= min($index + 1, 10) ?>">
                <div class="cabecalho-cartao-flex">
                    <div class="icone-fundo-azul">
                        <i class="ph ph-users-three icone-medio"></i>
                    </div>
                    <?php if ($turma['chamada_automatica']): ?>
                        <span class="etiqueta-sucesso">
                            Automática
                        </span>
                    <?php endif; ?>
                </div>

                <h3 class="titulo-cartao-medio margem-base-pequena"><?= htmlspecialchars($turma['nome']) ?></h3>
                
                <div class="lista-detalhes-cartao">
                    <div class="item-detalhe-cartao">
                        <i class="ph ph-clock"></i>
                        Início: <?= date('H:i', strtotime($turma['horario_inicio_chamada'])) ?>
                    </div>
                    <div class="item-detalhe-cartao">
                        <i class="ph ph-book-open"></i>
                        <?= $turma['qtd_aulas_dia'] ?> aulas de <?= $turma['duracao_aula'] ?> min
                    </div>
                </div>

                <div class="botoes-cartao-flex">
                    <a href="alunos.php?turma_id=<?= $turma['id'] ?>" class="botao botao-principal botao-expandido">
                        <i class="ph ph-identification-card"></i> Alunos
                    </a>
                    <a href="chamadas.php?turma_id=<?= $turma['id'] ?>" class="botao botao-secundario botao-secundario-pequeno">
                        <i class="ph ph-calendar-check"></i> Chamada
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="cartao cartao-vazio-centro">
            <i class="ph ph-folder-open icone-grande-vazio"></i>
            <h3 class="texto-secundario">Nenhuma turma encontrada</h3>
            <p class="texto-secundario">As turmas cadastradas no banco aparecerão aqui.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
// Puxa o rodapé e o fechamento do HTML
require_once 'includes/footer.php'; 
?>
