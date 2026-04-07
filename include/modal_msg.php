<style>
.modal-msg-box {
    max-width: 400px;
    margin: 80px auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.18);
    padding: 32px 24px 24px 24px;
    text-align: center;
    position: relative;
    font-family: Arial, Helvetica, sans-serif;
}
.modal-msg-box h1 {
    font-size: 1.6em;
    margin-bottom: 16px;
}
.modal-msg-box p {
    font-size: 1.1em;
    margin-bottom: 12px;
}
.modal-msg-box .msg-detalhes {
    background: #f7f7f7;
    color: #555;
    font-size: 0.95em;
    margin: 10px 0 18px 0;
    padding: 8px 12px;
    border-radius: 5px;
}
.modal-msg-box .btn-voltar {
    display: inline-block;
    background: #007700;
    color: #fff;
    padding: 8px 24px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    margin-top: 10px;
    transition: background 0.2s;
}
.modal-msg-box .btn-voltar:hover {
    background: #005500;
}
.modal-msg-box.acerto { border-left: 8px solid #007700; }
.modal-msg-box.erro   { border-left: 8px solid #c00; }
.modal-msg-box.atencao{ border-left: 8px solid #e6b800; }
.modal-msg-box .close {
    position: absolute;
    top: 10px;
    right: 16px;
    font-size: 1.5em;
    color: #888;
}
</style>

<div id="modalMsg" class="modal" style="display:<?php echo isset($msg_texto) ? 'block' : 'none'; ?>;">
    <?php
    if (isset($msg_tipo) && isset($msg_texto)) {
        $cor = ($msg_tipo == 'acerto') ? 'green' : (($msg_tipo == 'erro') ? 'red' : 'orange');
        echo "<div class='modal-msg' style='border:2px solid $cor; color:$cor; padding:16px; margin:16px 0; font-weight:bold;'>";
        echo htmlspecialchars($msg_texto);
        if (isset($msg_detalhes) && $msg_detalhes) {
            echo "<br><small>" . htmlspecialchars($msg_detalhes) . "</small>";
        }
        echo "</div>";
    }
    ?>
</div>