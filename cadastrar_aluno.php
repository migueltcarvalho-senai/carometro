<?php
// Carrega a conexão com o banco
require_once 'config/conexao.php';
require_once 'includes/header.php';

// Busca as turmas para o select
$stmtTurmas = $conexao->query("SELECT id, nome FROM turmas ORDER BY nome ASC");
$listaTurmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Importação da Face-API.js -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<div class="cabecalho-pagina animacao-surgir">
    <div>
        <h1 class="titulo-pagina">Cadastrar Novo Aluno</h1>
        <p class="subtitulo-pagina">Capture 3 ângulos do rosto para garantir um reconhecimento perfeito.</p>
    </div>
    <a href="alunos.php" class="botao botao-secundario-texto">
        <i class="ph ph-arrow-left"></i> Voltar
    </a>
</div>

<div class="animacao-surgir grade-mista">
    
    <!-- Formulário de Dados -->
    <div class="cartao">
        <form action="acoes/salvar_aluno.php" method="POST" id="formAluno" class="formulario-coluna">
            
            <!-- Campos ocultos para a imagem principal e para o JSON de vetores -->
            <input type="hidden" name="foto_base64" id="foto_base64">
            <!-- Campo oculto que armazena o JSON dos vetores faciais extraídos pela IA -->
            <input type="hidden" name="vetor_facial" id="vetor_facial">

            <div>
                <label for="nome_completo" class="rotulo-formulario">Nome Completo</label>
                <input type="text" name="nome_completo" id="nome_completo" required placeholder="Nome do aluno" class="controle-formulario">
            </div>

            <div class="grade-duas-colunas">
                <div>
                    <label for="registro_matricula" class="rotulo-formulario">RM (Matrícula)</label>
                    <input type="text" name="registro_matricula" id="registro_matricula" required placeholder="Ex: 123456" class="controle-formulario">
                </div>
                <div>
                    <label for="turma_id" class="rotulo-formulario">Turma</label>
                    <select name="turma_id" id="turma_id" required class="controle-formulario bg-branco">
                        <option value="">Selecione...</option>
                        <?php foreach ($listaTurmas as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="rodape-formulario">
                <button type="submit" id="btnSalvar" class="botao botao-principal botao-largo" disabled>
                    <i class="ph ph-user-plus"></i> Finalizar Cadastro
                </button>
                <p id="msgAviso" class="texto-cuidado">
                    <i class="ph ph-info"></i> Capture as 3 fotos antes de salvar.
                </p>
            </div>
        </form>
    </div>

    <!-- Área da Câmera Otimizada -->
    <div class="cartao texto-centralizado">
        <div id="status-ia" class="status-tela-ia">
            <i class="ph ph-spinner ph-spin"></i> Carregando Motor de Reconhecimento...
        </div>
        
        <div id="container-camera" class="conteiner-camera-cadastro">
            <video id="video" autoplay playsinline muted class="video-fluido"></video>
            <canvas id="canvas-preview" class="lona-preview"></canvas>
            
            <!-- Overlay visual para guiar o enquadramento -->
            <div id="enquadramento" class="marcador-rosto"></div>
        </div>

        <div class="flex-coluna-espacada">
            <div id="instrucao-foto" class="texto-principal-cor destaque-negrito">Foto 1: Olhe fixo para a câmera</div>
            
            <button type="button" id="btnCapturar" class="botao botao-principal botao-largo" disabled>
                <i class="ph ph-camera"></i> Capturar Foto <span id="num-foto">1</span>/3
            </button>
            
            <div id="miniaturas" class="conteiner-miniaturas">
                <div class="slot-miniatura"><i class="ph ph-image"></i></div>
                <div class="slot-miniatura"><i class="ph ph-image"></i></div>
                <div class="slot-miniatura"><i class="ph ph-image"></i></div>
            </div>

            <button type="button" id="btnReset" class="botao botao-secundario-texto">
                <i class="ph ph-arrows-counter-clockwise"></i> Recomeçar Capturas
            </button>
        </div>
    </div>
</div>

<script>
    const video = document.getElementById('video');
    const btnCapturar = document.getElementById('btnCapturar');
    const btnSalvar = document.getElementById('btnSalvar');
    const btnReset = document.getElementById('btnReset');
    const numFotoSpan = document.getElementById('num-foto');
    const instrucao = document.getElementById('instrucao-foto');
    const msgAviso = document.getElementById('msgAviso');
    const slots = document.querySelectorAll('.slot-miniatura');
    const statusIa = document.getElementById('status-ia');

    let fotosCapturadas = [];
    let vetoresExtraidos = [];
    const instrucoes = [
        "Foto 1: Olhe fixo para a câmera",
        "Foto 2: Vire um pouco a cabeça para a ESQUERDA",
        "Foto 3: Vire um pouco a cabeça para a DIREITA"
    ];

    // Carregar Modelos da IA
    async function initIA() {
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('assets/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('assets/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('assets/models')
            ]);
            
            statusIa.innerHTML = '<i class="ph ph-check-circle texto-sucesso"></i> Motor IA Pronto';
            statusIa.style.color = 'var(--success)';
            btnCapturar.disabled = false;
            
            startWebcam();
        } catch (err) {
            statusIa.innerHTML = '<i class="ph ph-x-circle texto-perigo"></i> Erro ao carregar IA';
            console.error(err);
        }
    }

    function startWebcam() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => video.srcObject = stream)
            .catch(err => alert('Webcam não encontrada!'));
    }

    btnCapturar.addEventListener('click', async () => {
        if (fotosCapturadas.length >= 3) return;

        btnCapturar.disabled = true;
        btnCapturar.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Processando...';

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);

        try {
            // Extrair o vetor facial na hora para garantir que a foto presta
            // Aumentamos o scoreThreshold para 0.7 para garantir biometrias de alta qualidade no banco
            const detection = await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.7 }))
                                          .withFaceLandmarks()
                                          .withFaceDescriptor();

            if (!detection) {
                alert("Não consegui ver seu rosto claramente nesta foto. Tente novamente!");
                resetBtnCapturar();
                return;
            }

            // Guardar o vetor e a foto
            const base64 = canvas.toDataURL('image/jpeg');
            fotosCapturadas.push(base64);
            vetoresExtraidos.push(Array.from(detection.descriptor)); // Converte Float32Array para array comum pro JSON

            // Atualizar UI
            const index = fotosCapturadas.length - 1;
            slots[index].innerHTML = `<img src="${base64}" style="width:100%; height:100%; object-fit:cover;">`;
            slots[index].style.borderColor = 'var(--success)';

            if (fotosCapturadas.length < 3) {
                numFotoSpan.innerText = fotosCapturadas.length + 1;
                instrucao.innerText = instrucoes[fotosCapturadas.length];
                resetBtnCapturar();
            } else {
                instrucao.innerText = "✅ Tudo pronto! Pode salvar.";
                instrucao.style.color = 'var(--success)';
                btnCapturar.style.display = 'none';
                
                // Preencher campos ocultos
                document.getElementById('foto_base64').value = fotosCapturadas[0]; // Foto principal (frente)
                // Preenche o campo oculto com o JSON de todos os vetores extraídos
                document.getElementById('vetor_facial').value = JSON.stringify(vetoresExtraidos);
                
                btnSalvar.disabled = false;
                msgAviso.style.display = 'none';
            }

        } catch (e) {
            console.error(e);
            alert("Erro ao processar rosto.");
            resetBtnCapturar();
        }
    });

    function resetBtnCapturar() {
        btnCapturar.disabled = false;
        btnCapturar.innerHTML = `<i class="ph ph-camera"></i> Capturar Foto ${fotosCapturadas.length + 1}/3`;
    }

    btnReset.addEventListener('click', () => {
        fotosCapturadas = [];
        vetoresExtraidos = [];
        slots.forEach(s => {
            s.innerHTML = '<i class="ph ph-image"></i>';
            s.style.borderColor = 'var(--border-color)';
        });
        btnCapturar.style.display = 'inline-flex';
        btnSalvar.disabled = true;
        msgAviso.style.display = 'block';
        instrucao.innerText = instrucoes[0];
        instrucao.style.color = 'var(--primary-color)';
        resetBtnCapturar();
    });

    initIA();
</script>

<?php 
require_once 'includes/footer.php'; 
?>
