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

                <div class="rodape-cartao-aluno" style="display: flex; flex-direction: column; gap: 0.5rem; width: 100%;">
                    <!-- Botões de Saída e Retorno lado a lado -->
                    <div style="display: flex; gap: 0.5rem; width: 100%;">
                        <button type="button" class="botao botao-registrar-saida" 
                                style="flex: 1; display:flex; justify-content:center; align-items:center; padding: 0.5rem; gap: 0.25rem; cursor: pointer;"
                                onclick="abrirModalSaida(<?= $aluno['id'] ?>, '<?= htmlspecialchars(addslashes($aluno['nome_completo'])) ?>')">
                            <i class="ph ph-sign-out"></i> Saída
                        </button>
                        <button type="button" class="botao botao-registrar-retorno" 
                                style="flex: 1; display:flex; justify-content:center; align-items:center; padding: 0.5rem; gap: 0.25rem; cursor: pointer;"
                                onclick="abrirModalRetorno(<?= $aluno['id'] ?>, '<?= htmlspecialchars(addslashes($aluno['nome_completo'])) ?>')">
                            <i class="ph ph-sign-in"></i> Retorno
                        </button>
                    </div>
                    <div style="display: flex; gap: 0.5rem; width: 100%;">
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

<!-- ============================================================
     MODAL DE SAÍDA ANTECIPADA
     Aparece ao clicar em "Registrar Saída" no card do aluno
     ============================================================ -->
<div id="modal-saida" class="modal-overlay">
    <div class="modal-conteudo">
        <!-- Cabeçalho com ícone e título -->
        <div class="modal-cabecalho">
            <div class="modal-icone">
                <i class="ph ph-sign-out"></i>
            </div>
            <div>
                <h2>Registrar Saída</h2>
                <p class="modal-nome-aluno" id="modal-nome-aluno"></p>
            </div>
        </div>

        <!-- Mensagem de erro (escondida por padrão) -->
        <div id="modal-erro" class="modal-erro"></div>

        <!-- Formulário com horário e motivo -->
        <div id="modal-formulario">
            <div class="modal-corpo">
                <div class="modal-campo">
                    <label for="horario-saida">Horário da Saída</label>
                    <input type="time" id="horario-saida" required>
                </div>
                <div class="modal-campo">
                    <label for="motivo-saida">Motivo da Saída</label>
                    <textarea id="motivo-saida" placeholder="Ex: Consulta médica, problema familiar..."></textarea>
                </div>
            </div>

            <!-- Botões de ação -->
            <div class="modal-rodape">
                <button type="button" class="botao botao-secundario-texto" onclick="fecharModalSaida()">
                    Cancelar
                </button>
                <button type="button" class="botao botao-saida" id="btn-confirmar-saida" onclick="confirmarSaida()">
                    <i class="ph ph-check"></i> Confirmar Saída
                </button>
            </div>
        </div>

        <!-- Tela de sucesso (escondida por padrão) -->
        <div id="modal-resultado" style="display: none;">
            <div class="modal-sucesso">
                <i class="ph ph-check-circle"></i>
                <p id="modal-mensagem-sucesso"></p>
            </div>
            <div class="modal-rodape">
                <button type="button" class="botao botao-saida" onclick="fecharModalSaida()" style="width: 100%;">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// JAVASCRIPT DO MODAL DE SAÍDA ANTECIPADA
// Controla abertura, fechamento e envio dos dados pro servidor
// ============================================================

// Variável que guarda o ID do aluno selecionado no modal
let alunoSaidaId = null;

// Abre o modal e preenche o nome do aluno
function abrirModalSaida(alunoId, nomeAluno) {
    alunoSaidaId = alunoId;
    document.getElementById('modal-nome-aluno').textContent = nomeAluno;
    
    // Limpa os campos do formulário
    document.getElementById('horario-saida').value = '';
    document.getElementById('motivo-saida').value = '';
    
    // Esconde erro e resultado, mostra o formulário
    document.getElementById('modal-erro').style.display = 'none';
    document.getElementById('modal-formulario').style.display = 'block';
    document.getElementById('modal-resultado').style.display = 'none';
    
    // Mostra o modal com animação
    document.getElementById('modal-saida').classList.add('modal-visivel');
}

// Fecha o modal
function fecharModalSaida() {
    document.getElementById('modal-saida').classList.remove('modal-visivel');
    alunoSaidaId = null;
}

// Fecha o modal se clicar fora do card (no overlay)
document.getElementById('modal-saida').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalSaida();
    }
});

// Envia os dados pro servidor quando o professor confirma
function confirmarSaida() {
    const horario = document.getElementById('horario-saida').value;
    const motivo = document.getElementById('motivo-saida').value;
    const erroDiv = document.getElementById('modal-erro');
    
    // Valida se o horário foi preenchido
    if (!horario) {
        erroDiv.textContent = 'Por favor, informe o horário da saída.';
        erroDiv.style.display = 'block';
        return;
    }
    
    // Esconde erro anterior
    erroDiv.style.display = 'none';
    
    // Desabilita o botão pra evitar clique duplo
    const btnConfirmar = document.getElementById('btn-confirmar-saida');
    btnConfirmar.disabled = true;
    btnConfirmar.innerHTML = '<i class="ph ph-spinner"></i> Processando...';
    
    // Monta os dados pra enviar via POST
    const dados = new FormData();
    dados.append('aluno_id', alunoSaidaId);
    dados.append('horario_saida', horario);
    dados.append('motivo', motivo);
    
    // Envia pro servidor
    fetch('acoes/registrar_saida.php', {
        method: 'POST',
        body: dados
    })
    .then(resposta => resposta.json())
    .then(resultado => {
        if (resultado.sucesso) {
            // Deu certo! Mostra a mensagem de sucesso
            document.getElementById('modal-formulario').style.display = 'none';
            document.getElementById('modal-resultado').style.display = 'block';
            document.getElementById('modal-mensagem-sucesso').textContent = resultado.mensagem;
        } else {
            // Deu ruim, mostra o erro
            erroDiv.textContent = resultado.erro || 'Erro ao registrar saída.';
            erroDiv.style.display = 'block';
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = '<i class="ph ph-check"></i> Confirmar Saída';
        }
    })
    .catch(erro => {
        // Erro de rede ou servidor fora
        erroDiv.textContent = 'Erro de conexão. Tente novamente.';
        erroDiv.style.display = 'block';
        btnConfirmar.disabled = false;
        btnConfirmar.innerHTML = '<i class="ph ph-check"></i> Confirmar Saída';
    });
}
</script>

<!-- ============================================================
     MODAL DE RETORNO
     Aparece ao clicar em "Registrar Retorno" no card do aluno
     ============================================================ -->
<div id="modal-retorno" class="modal-overlay">
    <div class="modal-conteudo">
        <!-- Cabeçalho com ícone e título -->
        <div class="modal-cabecalho">
            <div class="modal-icone" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="ph ph-sign-in"></i>
            </div>
            <div>
                <h2>Registrar Retorno</h2>
                <p class="modal-nome-aluno" id="modal-retorno-nome-aluno"></p>
            </div>
        </div>

        <!-- Mensagem de erro (escondida por padrão) -->
        <div id="modal-retorno-erro" class="modal-erro"></div>

        <!-- Formulário com horário e motivo -->
        <div id="modal-retorno-formulario">
            <div class="modal-corpo">
                <div class="modal-campo">
                    <label for="horario-retorno">Horário do Retorno</label>
                    <input type="time" id="horario-retorno" required>
                </div>
                <div class="modal-campo">
                    <label for="motivo-retorno">Motivo do Retorno (opcional)</label>
                    <textarea id="motivo-retorno" placeholder="Ex: Voltou da consulta médica..."></textarea>
                </div>
            </div>

            <!-- Botões de ação -->
            <div class="modal-rodape">
                <button type="button" class="botao botao-secundario-texto" onclick="fecharModalRetorno()">
                    Cancelar
                </button>
                <button type="button" class="botao botao-retorno" id="btn-confirmar-retorno" onclick="confirmarRetorno()">
                    <i class="ph ph-check"></i> Confirmar Retorno
                </button>
            </div>
        </div>

        <!-- Tela de sucesso (escondida por padrão) -->
        <div id="modal-retorno-resultado" style="display: none;">
            <div class="modal-sucesso">
                <i class="ph ph-check-circle"></i>
                <p id="modal-retorno-mensagem-sucesso"></p>
            </div>
            <div class="modal-rodape">
                <button type="button" class="botao botao-retorno" onclick="fecharModalRetorno()" style="width: 100%;">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// JAVASCRIPT DO MODAL DE RETORNO
// Controla abertura, fechamento e envio dos dados pro servidor
// ============================================================

// Variável que guarda o ID do aluno selecionado no modal de retorno
let alunoRetornoId = null;

// Abre o modal de retorno e preenche o nome do aluno
function abrirModalRetorno(alunoId, nomeAluno) {
    alunoRetornoId = alunoId;
    document.getElementById('modal-retorno-nome-aluno').textContent = nomeAluno;
    
    // Limpa os campos do formulário
    document.getElementById('horario-retorno').value = '';
    document.getElementById('motivo-retorno').value = '';
    
    // Esconde erro e resultado, mostra o formulário
    document.getElementById('modal-retorno-erro').style.display = 'none';
    document.getElementById('modal-retorno-formulario').style.display = 'block';
    document.getElementById('modal-retorno-resultado').style.display = 'none';
    
    // Mostra o modal com animação
    document.getElementById('modal-retorno').classList.add('modal-visivel');
}

// Fecha o modal de retorno
function fecharModalRetorno() {
    document.getElementById('modal-retorno').classList.remove('modal-visivel');
    alunoRetornoId = null;
}

// Fecha o modal se clicar fora do card (no overlay)
document.getElementById('modal-retorno').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalRetorno();
    }
});

// Envia os dados pro servidor quando o professor confirma o retorno
function confirmarRetorno() {
    const horario = document.getElementById('horario-retorno').value;
    const motivo = document.getElementById('motivo-retorno').value;
    const erroDiv = document.getElementById('modal-retorno-erro');
    
    // Valida se o horário foi preenchido
    if (!horario) {
        erroDiv.textContent = 'Por favor, informe o horário do retorno.';
        erroDiv.style.display = 'block';
        return;
    }
    
    // Esconde erro anterior
    erroDiv.style.display = 'none';
    
    // Desabilita o botão pra evitar clique duplo
    const btnConfirmar = document.getElementById('btn-confirmar-retorno');
    btnConfirmar.disabled = true;
    btnConfirmar.innerHTML = '<i class="ph ph-spinner"></i> Processando...';
    
    // Monta os dados pra enviar via POST
    const dados = new FormData();
    dados.append('aluno_id', alunoRetornoId);
    dados.append('horario_retorno', horario);
    dados.append('motivo', motivo);
    
    // Envia pro servidor
    fetch('acoes/registrar_retorno.php', {
        method: 'POST',
        body: dados
    })
    .then(resposta => resposta.json())
    .then(resultado => {
        if (resultado.sucesso) {
            // Deu certo! Mostra a mensagem de sucesso
            document.getElementById('modal-retorno-formulario').style.display = 'none';
            document.getElementById('modal-retorno-resultado').style.display = 'block';
            document.getElementById('modal-retorno-mensagem-sucesso').textContent = resultado.mensagem;
        } else {
            // Deu ruim, mostra o erro
            erroDiv.textContent = resultado.erro || 'Erro ao registrar retorno.';
            erroDiv.style.display = 'block';
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = '<i class="ph ph-check"></i> Confirmar Retorno';
        }
    })
    .catch(erro => {
        // Erro de rede ou servidor fora
        erroDiv.textContent = 'Erro de conexão. Tente novamente.';
        erroDiv.style.display = 'block';
        btnConfirmar.disabled = false;
        btnConfirmar.innerHTML = '<i class="ph ph-check"></i> Confirmar Retorno';
    });
}
</script>

<?php 
// Puxa o rodapé e o fechamento do HTML
require_once 'includes/footer.php'; 
?>
