<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($msg_texto) && isset($_SESSION['flash_msg'])) {
	$msg_tipo = $_SESSION['flash_msg']['tipo'] ?? 'erro';
	$msg_texto = $_SESSION['flash_msg']['texto'] ?? null;
	$msg_detalhes = $_SESSION['flash_msg']['detalhes'] ?? null;
	unset($_SESSION['flash_msg']);
}
?>
<style>
.toast-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
    font-family: Arial, Helvetica, sans-serif;
}
.toast-message {
    min-width: 280px;
    max-width: 420px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.16);
    padding: 16px 18px;
    color: #202124;
    border-left: 6px solid transparent;
    animation: toast-slide 0.35s ease-out;
    display: flex;
    align-items: center;
    gap: 12px;
}
.toast-message p {
    margin: 0;
    line-height: 1.5;
    flex: 1;
}
.toast-message small {
    display: block;
    margin-top: 4px;
    color: #555;
    font-size: 0.95em;
}
.toast-message.acerto {
    border-color: #2e7d32;
    background: #e8f5e9;
    color: #1b5e20;
}
.toast-message.erro {
    border-color: #c62828;
    background: #dddada;
    color: #b71c1c;
}
.toast-message.atencao {
    border-color: #f9a825;
    background: #dddada;
    color: #f57f17;
}
.toast-close {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    color: inherit;
    text-decoration: none;
    background: rgba(0,0,0,0.05);
    border: none;
    border-radius: 999px;
    font-size: 1.2em;
    cursor: pointer;
    transition: background 0.2s ease;
    padding: 0;
}
.toast-close:hover {
    background: rgba(0,0,0,0.12);
}
@keyframes toast-slide {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<div id="modalMsg" class="toast-container" style="display:<?php echo isset($msg_texto) ? 'flex' : 'none'; ?>;">
    <?php
    if (isset($msg_tipo) && isset($msg_texto)) {
        $tipoClasse = ($msg_tipo == 'acerto') ? 'acerto' : (($msg_tipo == 'erro') ? 'erro' : 'atencao');
        echo "<div class='toast-message {$tipoClasse}'>";
        echo "<button type='button' class='toast-close' onclick='this.closest(\".toast-message\").style.display=\"none\";'>×</button>";
        echo "<p>" . htmlspecialchars($msg_texto) . "</p>";
        if (isset($msg_detalhes) && $msg_detalhes) {
            echo "<small>" . htmlspecialchars($msg_detalhes) . "</small>";
        }
        echo "</div>";
    }
    ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toast = document.querySelector('.toast-message');
        if (toast) {
            setTimeout(function() {
                toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(function() {
                    if (toast.parentNode) {
                        toast.parentNode.style.display = 'none';
                    }
                }, 300);
            }, 4000);
        }
    });
</script>