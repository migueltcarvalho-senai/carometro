    <!-- Fechamento da área de conteúdo injetada nas outras páginas -->
    </main>

    <!-- Rodapé -->
    <footer class="rodape">
        <p>&copy; <?= date('Y'); ?> Carômetro Inteligente - Feito com muito zelo para escolas.</p>
    </footer>
</div>

<script>
document.querySelectorAll('.botao, .botao-principal').forEach(btn => {
    btn.addEventListener('click', function(e) {
        let ripple = document.createElement('span');
        let rect = btn.getBoundingClientRect();
        let size = Math.max(rect.width, rect.height);
        let x = e.clientX - rect.left - size/2;
        let y = e.clientY - rect.top - size/2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        
        this.appendChild(ripple);
        setTimeout(() => { if(ripple.parentNode) ripple.remove(); }, 600);
    });
});

// Transições de página interativas
window.addEventListener('pageshow', function(event) {
    document.getElementById('loader-transicao').classList.remove('ativa');
});

document.querySelectorAll('a:not([target="_blank"])').forEach(link => {
    link.addEventListener('click', function(e) {
        if(this.href && !this.href.includes('#') && !this.href.startsWith('javascript')) {
            e.preventDefault();
            const destino = this.href;
            document.getElementById('loader-transicao').classList.add('ativa');
            setTimeout(() => {
                window.location.href = destino;
            }, 300);
        }
    });
});
</script>

</body>
</html>
