<?php
// Interface de Chamada Facial Otimizada (Usa vetores pré-salvos)
require_once 'config/conexao.php';
require_once 'includes/header.php';

$diario_id = isset($_GET['diario_id']) ? (int)$_GET['diario_id'] : die("ID do diário não fornecido.");

try {
    $stmt = $conexao->prepare("SELECT d.*, t.nome as nome_turma, t.id as turma_id 
                               FROM diarios_chamada d 
                               JOIN turmas t ON d.turma_id = t.id 
                               WHERE d.id = :id");
    $stmt->bindParam(':id', $diario_id);
    $stmt->execute();
    $diario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$diario) die("Diário não encontrado.");

    // Busca alunos com os VETORES JSON já prontos
    $stmtAlunos = $conexao->prepare("SELECT id, nome_completo, caminho_foto, vetores_json 
                                     FROM alunos WHERE turma_id = :turma_id");
    $stmtAlunos->bindParam(':turma_id', $diario['turma_id']);
    $stmtAlunos->execute();
    $alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<div class="page-header animate-fade-in">
    <div>
        <h1 class="page-title">Chamada Facial Otimizada</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">
            Turma: <strong style="color: var(--primary-color);"><?= htmlspecialchars($diario['nome_turma']) ?></strong> | 
            <span id="status-ia" style="color: var(--success); font-weight: 600;">
                <i class="ph ph-check-circle"></i> Sistema Ativo
            </span>
        </p>
    </div>
    <a href="chamadas.php" class="btn" style="border-color: var(--border-color); color: var(--text-muted);">
        <i class="ph ph-stop"></i> Finalizar
    </a>
</div>

<div class="animate-fade-in" style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
    
    <div id="video-wrapper" class="card" style="padding: 0; position: relative; background: #000; border-radius: var(--radius-lg); overflow: hidden; aspect-ratio: 16/9;">
        <video id="video-feed" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover;"></video>
        <canvas id="overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></canvas>
        
        <div id="alerta-match" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); background: white; padding: 0.75rem 1.5rem; border-radius: var(--radius-full); display: flex; align-items: center; gap: 1rem; box-shadow: var(--shadow-lg); display: none; z-index: 10; border: 2px solid var(--success); animation: bounceIn 0.5s;">
            <div id="match-foto" style="width: 45px; height: 45px; border-radius: 50%; background: #eee; overflow: hidden; border: 2px solid var(--success);"></div>
            <div>
                <h4 id="match-nome" style="font-size: 0.9rem; margin: 0;">-</h4>
                <p style="font-size: 0.65rem; color: var(--success); margin: 0; font-weight: 700;">PRESENÇA CONFIRMADA!</p>
            </div>
        </div>

        <div id="loader-ia" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; gap: 1rem; z-index: 20;">
            <i class="ph ph-lightning ph-spin" style="font-size: 3rem; color: var(--primary-color);"></i>
            <p id="loader-texto">Sincronizando Banco Biométrico...</p>
        </div>
    </div>

    <div class="card" style="display: flex; flex-direction: column; max-height: 500px;">
        <h3 class="card-title" style="margin-bottom: 1rem; display: flex; justify-content: space-between;">
            Presentes <span id="contador-presencas" style="background: var(--primary-color); color: white; padding: 0.1rem 0.6rem; border-radius: var(--radius-full); font-size: 0.8rem;">0</span>
        </h3>
        <div id="lista-presencas" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 0.75rem;">
            <p id="msg-vazia" style="text-align: center; color: var(--text-muted); padding: 2rem; font-size: 0.8rem;">Posicione-se frente à câmera</p>
        </div>
    </div>
</div>

<script>
    const video = document.getElementById('video-feed');
    const canvas = document.getElementById('overlay');
    const loaderIa = document.getElementById('loader-ia');
    const loaderTexto = document.getElementById('loader-texto');
    const alertaMatch = document.getElementById('alerta-match');
    const matchNome = document.getElementById('match-nome');
    const matchFoto = document.getElementById('match-foto');
    const listaPresencas = document.getElementById('lista-presencas');
    const contador = document.getElementById('contador-presencas');
    const msgVazia = document.getElementById('msg-vazia');
    
    const diarioId = <?= $diario_id ?>;
    const alunosBD = <?= json_encode($alunos) ?>;
    let faceMatcher = null;
    let presentes = new Set();

    async function setupIA() {
        try {
            loaderTexto.innerText = "Carregando Modelos...";
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('assets/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('assets/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('assets/models')
            ]);

            loaderTexto.innerText = "Carregando Biometrias do Banco...";
            const labeledDescriptors = [];
            
            alunosBD.forEach(aluno => {
                if (aluno.vetores_json) {
                    const descritores = JSON.parse(aluno.vetores_json).map(v => new Float32Array(v));
                    if (descritores.length > 0) {
                        labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(aluno.id.toString(), descritores));
                    }
                }
            });

            if (labeledDescriptors.length > 0) {
                // Diminuímos o rigor: de 0.6 para 0.65 (Distância Euclidiana: menor é mais rigoroso)
                faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.65);
                iniciarAcessoCamera();
            } else {
                alert("Nenhum aluno desta turma possui biometria cadastrada. Redirecionando para cadastros.");
                window.location.href = 'cadastrar_aluno.php';
            }

        } catch (err) {
            console.error(err);
            loaderTexto.innerText = "Erro ao iniciar IA.";
        }
    }

    function iniciarAcessoCamera() {
        navigator.mediaDevices.getUserMedia({ video: {} })
            .then(stream => {
                video.srcObject = stream;
                video.onplay = () => {
                    loaderIa.style.display = 'none';
                    const displaySize = { width: video.clientWidth, height: video.clientHeight };
                    faceapi.matchDimensions(canvas, displaySize);

                    setInterval(async () => {
                        const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                                                        .withFaceLandmarks()
                                                        .withFaceDescriptors();
                        
                        const resizedDetections = faceapi.resizeResults(detections, displaySize);
                        const context = canvas.getContext('2d');
                        context.clearRect(0, 0, canvas.width, canvas.height);

                        resizedDetections.forEach(detection => {
                            const result = faceMatcher.findBestMatch(detection.descriptor);
                            const alunoId = result.label;
                            
                            // Visual: Cor verde para conhecido, vermelho para estranho
                            const boxColor = (alunoId !== 'unknown') ? '#10b981' : '#ef4444';
                            const drawBox = new faceapi.draw.DrawBox(detection.detection.box, { 
                                label: alunoId !== 'unknown' ? "Identificado" : "Desconhecido",
                                boxColor: boxColor
                            });
                            drawBox.draw(canvas);

                            if (alunoId !== 'unknown') {
                                let aluno = alunosBD.find(a => a.id == alunoId);
                                if (aluno) registrarNoBanco(aluno.id, aluno.nome_completo, aluno.caminho_foto);
                            }
                        });
                    }, 400); // Intervalo levemente menor para resposta mais rápida
                };
            });
    }

    async function registrarNoBanco(alunoId, nome, foto) {
        // Trava imediata para evitar múltiplas requisições enquanto a primeira ainda está processando
        if (presentes.has(alunoId)) return;
        presentes.add(alunoId); // Marca como "em processamento/presente" antes mesmo do fetch

        try {
            const formData = new FormData();
            formData.append('aluno_id', alunoId);
            formData.append('diario_id', diarioId);

            const response = await fetch('acoes/registrar_presenca.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.sucesso) {
                matchNome.innerText = nome;
                matchFoto.innerHTML = `<img src="${foto}" style="width:100%; height:100%; object-fit:cover;">`;
                alertaMatch.style.display = 'flex';
                setTimeout(() => alertaMatch.style.display = 'none', 3000);

                msgVazia.style.display = 'none';
                const item = document.createElement('div');
                item.className = 'animate-fade-in';
                item.style = 'display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem; background: #f0fdf4; border-radius: var(--radius-md); border: 1px solid #bbf7d0;';
                item.innerHTML = `
                    <div style="width: 35px; height: 35px; border-radius: 50%; overflow: hidden;">
                        <img src="${foto}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="flex: 1;">
                        <p style="font-size: 0.75rem; font-weight: 700; margin: 0;">${nome}</p>
                        <p style="font-size: 0.6rem; color: var(--text-muted); margin: 0;">Às ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
                    </div>
                `;
                listaPresencas.prepend(item);
                contador.innerText = presentes.size;
            } else {
                // Se deu erro no servidor (ex: não conseguiu salvar), removemos do set para tentar novamente depois
                presentes.delete(alunoId);
            }
        } catch (err) { 
            console.error(err); 
            presentes.delete(alunoId); // Libera para tentar de novo caso seja erro de rede
        }
    }

    setupIA();
</script>

<?php require_once 'includes/footer.php'; ?>
