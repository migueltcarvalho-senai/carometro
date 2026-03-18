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

<div class="cabecalho-pagina animacao-surgir">
    <div>
        <h1 class="titulo-pagina">Chamada Facial Otimizada</h1>
        <p class="subtitulo-pagina">
            Turma: <strong class="texto-principal-cor"><?= htmlspecialchars($diario['nome_turma']) ?></strong> | 
            <span id="status-ia" class="texto-sucesso destaque-negrito">
                <i class="ph ph-check-circle"></i> Sistema Ativo
            </span>
        </p>
    </div>
    <a href="chamadas.php" class="botao botao-secundario-texto">
        <i class="ph ph-stop"></i> Finalizar
    </a>
</div>

<div class="animacao-surgir layout-grade-tela-dividida">
    
    <div id="video-wrapper" class="cartao conteiner-video">
        <video id="video-feed" autoplay playsinline muted class="video-fluido"></video>
        <canvas id="overlay" class="lona-sobreposicao"></canvas>
        
        <div id="alerta-match" class="alerta-sucesso-sobreposto">
            <div id="match-foto" class="foto-perfil-pequena-borda"></div>
            <div>
                <h4 id="match-nome" class="titulo-alerta-nome">-</h4>
                <p class="texto-sucesso texto-alerta-pequeno">PRESENÇA CONFIRMADA!</p>
            </div>
        </div>

        <div id="loader-ia" class="tela-carregamento-sobreposta">
            <i class="ph ph-lightning ph-spin icone-carregamento-grande"></i>
            <p id="loader-texto">Sincronizando Banco Biométrico...</p>
        </div>
    </div>

    <div class="cartao cartao-coluna-limitada" style="max-height: 600px;">
        <!-- Abas -->
        <div style="display: flex; gap: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1rem;">
            <button id="aba-presentes" onclick="alternarAba('presentes')" style="background:none; border:none; padding: 0.5rem 1rem; font-weight: 600; font-size: 1rem; cursor: pointer; color: var(--primary-color); border-bottom: 2px solid var(--primary-color);">
                Presentes (<span id="contador-presentes">0</span>)
            </button>
            <button id="aba-ausentes" onclick="alternarAba('ausentes')" style="background:none; border:none; padding: 0.5rem 1rem; font-weight: 600; font-size: 1rem; cursor: pointer; color: var(--text-muted); border-bottom: 2px solid transparent;">
                Ausentes (<span id="contador-ausentes">0</span>)
            </button>
        </div>

        <div id="lista-presentes" class="lista-rolavel-espacada">
            <p class="mensagem-vazia-pequena">Nenhum presente ainda.</p>
        </div>

        <div id="lista-ausentes" class="lista-rolavel-espacada" style="display: none;">
            <p class="mensagem-vazia-pequena">Carregando...</p>
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
    const listaPresentes = document.getElementById('lista-presentes');
    const listaAusentes = document.getElementById('lista-ausentes');
    const contadorPresentes = document.getElementById('contador-presentes');
    const contadorAusentes = document.getElementById('contador-ausentes');
    
    const abaPresentes = document.getElementById('aba-presentes');
    const abaAusentes = document.getElementById('aba-ausentes');
    
    const diarioId = <?= $diario_id ?>;
    const alunosBD = <?= json_encode($alunos) ?>;
    let faceMatcher = null;
    let setPresentesLock = new Set(); // Controle para não inundar o servidor com a mesma face

    // Alternar entre abas
    window.alternarAba = function(aba) {
        if (aba === 'presentes') {
            abaPresentes.style.color = 'var(--primary-color)';
            abaPresentes.style.borderBottomColor = 'var(--primary-color)';
            abaAusentes.style.color = 'var(--text-muted)';
            abaAusentes.style.borderBottomColor = 'transparent';
            listaPresentes.style.display = 'flex';
            listaAusentes.style.display = 'none';
        } else {
            abaAusentes.style.color = 'var(--primary-color)';
            abaAusentes.style.borderBottomColor = 'var(--primary-color)';
            abaPresentes.style.color = 'var(--text-muted)';
            abaPresentes.style.borderBottomColor = 'transparent';
            listaAusentes.style.display = 'flex';
            listaPresentes.style.display = 'none';
        }
    };

    // Função que busca dados reais do servidor a cada 2 segundos
    async function sincronizarDashboard() {
        try {
            const resp = await fetch(`acoes/obter_diario_real.php?diario_id=${diarioId}`);
            if (!resp.ok) return;
            const data = await resp.json();
            
            if (data.sucesso) {
                renderizarListas(data.presentes, data.ausentes);
            }
        } catch (e) {
            console.error("Erro sincronizando dashboard: ", e);
        }
    }

    function renderizarListas(presentes, ausentes) {
        contadorPresentes.innerText = presentes.length;
        contadorAusentes.innerText = ausentes.length;

        // Lista Presentes
        if (presentes.length === 0) {
            listaPresentes.innerHTML = '<p class="mensagem-vazia-pequena">Nenhum presente ainda.</p>';
        } else {
            listaPresentes.innerHTML = presentes.map(a => `
                <div class="animacao-surgir item-lista-presenca" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem; background: #f0fdf4; border-radius: var(--radius-md); border: 1px solid #bbf7d0;">
                    <div style="width: 35px; height: 35px; border-radius: 50%; overflow: hidden;">
                        <img src="${a.caminho_foto}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="flex: 1;">
                        <p style="font-size: 0.75rem; font-weight: 700; margin: 0; color: var(--text-main);">${a.nome_completo}</p>
                        <p style="font-size: 0.6rem; color: var(--success); margin: 0; font-weight: 600;">Às ${a.hora_registro}</p>
                    </div>
                </div>
            `).join('');
        }

        // Lista Ausentes
        if (ausentes.length === 0) {
            listaAusentes.innerHTML = '<p class="mensagem-vazia-pequena">Todos presentes!</p>';
        } else {
            listaAusentes.innerHTML = ausentes.map(a => `
                <div class="item-lista-presenca" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem; background: #fef2f2; border-radius: var(--radius-md); border: 1px solid #fecaca; opacity: 0.8;">
                    <div style="width: 35px; height: 35px; border-radius: 50%; overflow: hidden; opacity: 0.6; grayscale: 100%;">
                        <img src="${a.caminho_foto}" style="width:100%; height:100%; object-fit:cover; filter: grayscale(100%);">
                    </div>
                    <div style="flex: 1;">
                        <p style="font-size: 0.75rem; font-weight: 700; margin: 0; color: var(--text-main);">${a.nome_completo}</p>
                        <p style="font-size: 0.6rem; color: var(--danger); margin: 0; font-weight: 600;">Ausente</p>
                    </div>
                </div>
            `).join('');
        }
    }

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
                // Rigor 0.65
                faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.65);
                iniciarAcessoCamera();
                
                // Inicia loop de sincronização com o banco independente da captura
                sincronizarDashboard();
                setInterval(sincronizarDashboard, 2000); // Atualiza as listas a cada 2 seg
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
                    }, 400); 
                };
            })
            .catch(err => alert("Erro ao acessar a câmera."));
    }

    async function registrarNoBanco(alunoId, nome, foto) {
        if (setPresentesLock.has(alunoId)) return;
        setPresentesLock.add(alunoId); 

        try {
            const formData = new FormData();
            formData.append('aluno_id', alunoId);
            formData.append('diario_id', diarioId);

            const response = await fetch('acoes/registrar_presenca.php', { method: 'POST', body: formData });
            const data = await response.json();

            // Só damos feedback de overlay para o usuário da câmera.
            // As listas serão atualizadas automaticamente pelo sincronizarDashboard().
            if (data.sucesso) {
                matchNome.innerText = nome;
                matchFoto.innerHTML = `<img src="${foto}" style="width:100%; height:100%; object-fit:cover;">`;
                alertaMatch.style.display = 'flex';
                setTimeout(() => alertaMatch.style.display = 'none', 3000);
            } else {
                setPresentesLock.delete(alunoId);
            }
        } catch (err) { 
            console.error(err); 
            setPresentesLock.delete(alunoId); 
        }
    }

    setupIA();
</script>

<?php require_once 'includes/footer.php'; ?>
