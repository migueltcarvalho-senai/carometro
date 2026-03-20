<?php
// Carrega a conexão com o banco
require_once 'config/conexao.php';

// Puxa o cabeçalho base e o CSS
require_once 'includes/header.php';

// Verifica se veio um filtro de turma por ID na URL
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : null;

// Busca os dados das turmas para o filtro
$stmtTurmas = $conexao->query("SELECT id, nome FROM turmas ORDER BY nome ASC");
$listaTurmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

// Monta a consulta de alunos
try {
    $sql = "SELECT a.*, t.nome as nome_turma 
            FROM alunos a 
            JOIN turmas t ON a.turma_id = t.id";
    
    // Se houver filtro de turma, adiciona o WHERE
    if ($turmaId) {
        $sql .= " WHERE a.turma_id = :turma_id";
    }
    
    $sql .= " ORDER BY a.nome_completo ASC";
    
    $stmt = $conexao->prepare($sql);
    if ($turmaId) {
        $stmt->bindParam(':turma_id', $turmaId, PDO::PARAM_INT);
    }
    $stmt->execute();
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $erro = "Erro ao buscar alunos: " . $e->getMessage();
}
?>

<div class="cabecalho-pagina animacao-surgir">
    <div>
        <h1 class="titulo-pagina">Carômetro de Alunos</h1>
        <p class="subtitulo-pagina">Identificação visual de todos os alunos cadastrados.</p>
    </div>
    <a href="cadastrar_aluno.php" class="botao botao-principal">
        <i class="ph ph-user-plus"></i> Novo Aluno
    </a>
</div>

<!-- Barra de Filtros -->
<div class="cartao animacao-surgir margem-base-extra-grande conteudo-acolchoado">
    <form action="alunos.php" method="GET" class="formulario-filtros">
        <div class="campo-filtro">
            <label for="turma_id" class="rotulo-filtro">Filtrar por Turma</label>
            <select name="turma_id" id="turma_id" class="selecao-filtro">
                <option value="">Todas as Turmas</option>
                <?php foreach ($listaTurmas as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($turmaId == $t['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="botao botao-principal">
            <i class="ph ph-funnel"></i> Aplicar Filtro
        </button>
        <?php if ($turmaId): ?>
            <a href="alunos.php" class="botao botao-secundario-texto">
                Limpar
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if (isset($erro)): ?>
    <div class="cartao alerta-erro">
        <p class="texto-perigo"><?= $erro; ?></p>
    </div>
<?php endif; ?>

<!-- Grid do Carômetro -->
<div class="grade-painel grade-painel-pequena animacao-surgir atraso-animacao-1">
    <?php if (count($alunos) > 0): ?>
        <?php foreach ($alunos as $index => $aluno): ?>
            <div class="cartao cartao-aluno animacao-surgir cartao-aluno-atraso-<?= min($index + 1, 10) ?>">
                <!-- Foto do Aluno com fallback caso não exista -->
                <div class="foto-perfil-recipiente">
                    <?php if (!empty($aluno['caminho_foto']) && file_exists($aluno['caminho_foto'])): ?>
                        <img src="<?= htmlspecialchars($aluno['caminho_foto']) ?>" alt="<?= htmlspecialchars($aluno['nome_completo']) ?>" class="foto-perfil-imagem">
                    <?php else: ?>
                        <i class="ph ph-user icone-grande"></i>
                    <?php endif; ?>
                </div>

                <h3 class="titulo-cartao-menor texto-truncado" title="<?= htmlspecialchars($aluno['nome_completo']) ?>">
                    <?= htmlspecialchars($aluno['nome_completo']) ?>
                </h3>
                
                <p class="texto-detalhe-secundario margem-base-media">
                    RM: <?= htmlspecialchars($aluno['registro_matricula'] ?? 'N/D') ?>
                </p>

                <span class="etiqueta-pequena">
                    <?= htmlspecialchars($aluno['nome_turma']) ?>
                </span>

                <div class="rodape-cartao-aluno" style="display: flex; gap: 0.5rem; width: 100%;">
                    <a href="historico_aluno.php?id=<?= $aluno['id'] ?>" class="botao botao-secundario-texto" style="flex: 1; text-decoration:none; display:flex; justify-content:center; align-items:center; padding: 0.5rem; gap: 0.25rem;">
                        <i class="ph ph-clock-counter-clockwise"></i> Histórico
                    </a>
                    <a href="acoes/excluir_aluno.php?id=<?= $aluno['id'] ?>&turma_id=<?= $turmaId ?>" 
                       class="botao" style="flex: 1; text-decoration:none; display:flex; justify-content:center; align-items:center; background-color: var(--cor-erro, #ef4444); color: white; padding: 0.5rem; gap: 0.25rem;"
                       onclick="return confirm('Tem certeza que deseja excluir este aluno? Esta ação apagará todo o histórico de presenças dele e não pode ser desfeita.');">
                        <i class="ph ph-trash"></i> Excluir
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="cartao cartao-vazio">
            <i class="ph ph-users icone-extra-grande"></i>
            <h3 class="texto-secundario margem-base-pequena">Nenhum aluno encontrado</h3>
            <p class="texto-secundario">Tente mudar o filtro ou cadastre novos alunos no banco de dados.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
// Puxa o rodapé e o fechamento do HTML
require_once 'includes/footer.php'; 
?>
