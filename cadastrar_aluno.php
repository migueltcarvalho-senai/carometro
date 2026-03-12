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

<div class="page-header animate-fade-in">
    <div>
        <h1 class="page-title">Cadastrar Novo Aluno</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Capture 3 ângulos do rosto para garantir um reconhecimento perfeito.</p>
    </div>
    <a href="alunos.php" class="btn" style="border-color: var(--border-color); color: var(--text-muted);">
        <i class="ph ph-arrow-left"></i> Voltar
    </a>
</div>

<div class="animate-fade-in" style="display: grid; grid-template-columns: 1fr 450px; gap: 2rem; align-items: start;">
    
    <!-- Formulário de Dados -->
    <div class="card">
        <form action="acoes/salvar_aluno.php" method="POST" id="formAluno" style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Campos ocultos para a imagem principal e para o JSON de vetores -->
            <input type="hidden" name="foto_base64" id="foto_base64">
            <input type="hidden" name="vetores_json" id="vetores_json">

            <div>
                <label for="nome_completo" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem;">Nome Completo</label>
                <input type="text" name="nome_completo" id="nome_completo" required placeholder="Nome do aluno"
                       style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label for="registro_matricula" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem;">RM (Matrícula)</label>
                    <input type="text" name="registro_matricula" id="registro_matricula" required placeholder="Ex: 123456"
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit;">
                </div>
                <div>
                    <label for="turma_id" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem;">Turma</label>
                    <select name="turma_id" id="turma_id" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit; background-color: white;">
                        <option value="">Selecione...</option>
                        <?php foreach ($listaTurmas as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <button type="submit" id="btnSalvar" class="btn btn-primary" style="width: 100%; padding: 0.875rem;" disabled>
                    <i class="ph ph-user-plus"></i> Finalizar Cadastro
                </button>
                <p id="msgAviso" style="color: var(--warning); font-size: 0.75rem; margin-top: 0.5rem; text-align: center; font-weight: 600;">
                    <i class="ph ph-info"></i> Capture as 3 fotos antes de salvar.
                </p>
            </div>
        </form>
    </div>

    <!-- Área da Câmera Otimizada -->
    <div class="card" style="text-align: center;">
        <div id="status-ia" style="font-size: 0.75rem; color: var(--warning); margin-bottom: 1rem; font-weight: 600;">
            <i class="ph ph-spinner ph-spin"></i> Carregando Motor de Reconhecimento...
        </div>
        
        <div id="container-camera" style="width: 100%; aspect-ratio: 4/3; background: #000; border-radius: var(--radius-md); overflow: hidden; position: relative; margin-bottom: 1rem;">
            <video id="video" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover;"></video>
            <canvas id="canvas-preview" style="display: none; width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;"></canvas>
            
            <!-- Overlay visual para guiar o enquadramento -->
            <div id="enquadramento" style="position: absolute; top: 15%; left: 20%; width: 60%; height: 70%; border: 2px dashed rgba(255,255,255,0.4); border-radius: 40px; pointer-events: none;"></div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div id="instrucao-foto" style="font-weight: 600; color: var(--primary-color);">Foto 1: Olhe fixo para a câmera</div>
            
            <button type="button" id="btnCapturar" class="btn" style="background: var(--text-main); color: white; padding: 1rem;" disabled>
                <i class="ph ph-camera"></i> Capturar Foto <span id="num-foto">1</span>/3
            </button>
            
            <div id="miniaturas" style="display: flex; gap: 0.5rem; justify-content: center; margin-top: 0.5rem;">
                <div class="slot-foto" style="width: 60px; height: 60px; background: #f1f5f9; border: 2px solid var(--border-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;"><i class="ph ph-image"></i></div>
                <div class="slot-foto" style="width: 60px; height: 60px; background: #f1f5f9; border: 2px solid var(--border-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;"><i class="ph ph-image"></i></div>
                <div class="slot-foto" style="width: 60px; height: 60px; background: #f1f5f9; border: 2px solid var(--border-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;"><i class="ph ph-image"></i></div>
            </div>

            <button type="button" id="btnReset" class="btn" style="border-color: var(--border-color); color: var(--text-muted); font-size: 0.75rem;">
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
    const slots = document.querySelectorAll('.slot-foto');
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
            
            statusIa.innerHTML = '<i class="ph ph-check-circle text-success"></i> Motor IA Pronto';
            statusIa.style.color = 'var(--success)';
            btnCapturar.disabled = false;
            
            startWebcam();
        } catch (err) {
            statusIa.innerHTML = '<i class="ph ph-x-circle text-danger"></i> Erro ao carregar IA';
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
            const detection = await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions())
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
                document.getElementById('vetores_json').value = JSON.stringify(vetoresExtraidos);
                
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
