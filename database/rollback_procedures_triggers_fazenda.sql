
-- ROLLBACK: Remoção de procedures e triggers criadas

DROP TRIGGER IF EXISTS trg_cria_status_default;
DROP TRIGGER IF EXISTS trg_update_cria_status;
DROP TRIGGER IF EXISTS trg_update_cria_baia;
DROP TRIGGER IF EXISTS trg_baia_cria_entrada;
DROP TRIGGER IF EXISTS trg_log_insert_matrizes;
DROP TRIGGER IF EXISTS trg_log_insert_crias;

DROP PROCEDURE IF EXISTS registrar_movimentacao;
DROP PROCEDURE IF EXISTS registrar_status_cria;
DROP PROCEDURE IF EXISTS registrar_entrada_baia;
