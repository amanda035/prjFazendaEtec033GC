
-- PROCEDURES E TRIGGERS COMPLETAS PARA O BANCO DE DADOS fazendaEtec

DELIMITER $$

-- TRIGGER: Define status padrão ao inserir nova cria
CREATE TRIGGER trg_cria_status_default
BEFORE INSERT ON crias
FOR EACH ROW
BEGIN
  IF NEW.status_atual IS NULL THEN
    SET NEW.status_atual = 'Leitao';
  END IF;
END$$

-- TRIGGER: Atualiza status atual da cria ao inserir novo status
CREATE TRIGGER trg_update_cria_status
AFTER INSERT ON status_crias
FOR EACH ROW
BEGIN
  UPDATE crias
  SET status_atual = NEW.status
  WHERE id = NEW.cria_id;
END$$

-- TRIGGER: Atualiza baia atual da cria após movimentação
CREATE TRIGGER trg_update_cria_baia
AFTER INSERT ON movimentacao_crias
FOR EACH ROW
BEGIN
  UPDATE crias
  SET baia_id = NEW.baia_destino_id
  WHERE id = NEW.cria_id;
END$$

-- TRIGGER: Atualiza baia atual da cria após entrada em baia_crias
CREATE TRIGGER trg_baia_cria_entrada
AFTER INSERT ON baia_crias
FOR EACH ROW
BEGIN
  UPDATE crias
  SET baia_id = NEW.baia_id
  WHERE id = NEW.cria_id;
END$$

-- TRIGGER: Auditoria de inserções em matrizes
CREATE TRIGGER trg_log_insert_matrizes
AFTER INSERT ON matrizes
FOR EACH ROW
BEGIN
  INSERT INTO logs (usuario_id, tabela, acao, data_acao)
  VALUES (NEW.usuario_id, 'matrizes', 'inclusao', NOW());
END$$

-- TRIGGER: Auditoria de inserções em crias
CREATE TRIGGER trg_log_insert_crias
AFTER INSERT ON crias
FOR EACH ROW
BEGIN
  INSERT INTO logs (usuario_id, tabela, acao, data_acao)
  VALUES (NEW.usuario_id, 'crias', 'inclusao', NOW());
END$$

-- PROCEDURE: Registrar movimentação de cria
CREATE PROCEDURE registrar_movimentacao (
  IN p_cria_id INT,
  IN p_baia_origem_id INT,
  IN p_baia_destino_id INT,
  IN p_motivo VARCHAR(100),
  IN p_data DATE,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO movimentacao_crias (
    cria_id, baia_origem_id, baia_destino_id,
    motivo, data_movimentacao, usuario_id, data_acao
  )
  VALUES (
    p_cria_id, p_baia_origem_id, p_baia_destino_id,
    p_motivo, p_data, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar novo status de cria
CREATE PROCEDURE registrar_status_cria (
  IN p_cria_id INT,
  IN p_status ENUM('Leitao', 'Marra', 'Barrao', 'Matriz', 'Cachaco', 'Descarte', 'Venda'),
  IN p_motivo TEXT,
  IN p_data DATE,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO status_crias (
    cria_id, status, motivo, data_status, usuario_id, data_acao
  )
  VALUES (
    p_cria_id, p_status, p_motivo, p_data, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar entrada em baia_crias
CREATE PROCEDURE registrar_entrada_baia (
  IN p_cria_id INT,
  IN p_baia_id INT,
  IN p_data_entrada DATE,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO baia_crias (
    cria_id, baia_id, data_entrada, usuario_id, data_acao
  )
  VALUES (
    p_cria_id, p_baia_id, p_data_entrada, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar vacina aplicada em matriz
CREATE PROCEDURE registrar_vacina_matriz (
  IN p_matriz_id INT,
  IN p_vacina_id INT,
  IN p_data_aplicacao DATE,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO vacinas_matrizes (
    matriz_id, vacina_id, data_aplicacao, usuario_id, data_acao
  )
  VALUES (
    p_matriz_id, p_vacina_id, p_data_aplicacao, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar vacina aplicada em cria
CREATE PROCEDURE registrar_vacina_cria (
  IN p_cria_id INT,
  IN p_vacina_id INT,
  IN p_data_aplicacao DATE,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO vacinas_crias (
    cria_id, vacina_id, data_aplicacao, usuario_id, data_acao
  )
  VALUES (
    p_cria_id, p_vacina_id, p_data_aplicacao, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar alimentação de matriz
CREATE PROCEDURE registrar_alimentacao_matriz (
  IN p_matriz_id INT,
  IN p_alimento_id INT,
  IN p_quantidade DECIMAL(5,2),
  IN p_data DATE,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO alimentacao_matrizes (
    matriz_id, alimento_id, quantidade, data_alimentacao, usuario_id, data_acao
  )
  VALUES (
    p_matriz_id, p_alimento_id, p_quantidade, p_data, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar alimentação de cria
CREATE PROCEDURE registrar_alimentacao_cria (
  IN p_cria_id INT,
  IN p_alimento_id INT,
  IN p_quantidade DECIMAL(5,2),
  IN p_data DATE,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO alimentacao_crias (
    cria_id, alimento_id, quantidade, data_alimentacao, usuario_id, data_acao
  )
  VALUES (
    p_cria_id, p_alimento_id, p_quantidade, p_data, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar pesagem de matriz
CREATE PROCEDURE registrar_pesagem_matriz (
  IN p_matriz_id INT,
  IN p_peso DECIMAL(6,2),
  IN p_data DATE,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO pesagem_matrizes (
    matriz_id, peso, data_pesagem, usuario_id, data_acao
  )
  VALUES (
    p_matriz_id, p_peso, p_data, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar pesagem de cria
CREATE PROCEDURE registrar_pesagem_cria (
  IN p_cria_id INT,
  IN p_peso DECIMAL(5,2),
  IN p_data DATE,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO pesagem_crias (
    cria_id, peso, data_pesagem, usuario_id, data_acao
  )
  VALUES (
    p_cria_id, p_peso, p_data, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar cobertura de matriz
CREATE PROCEDURE registrar_cobertura (
  IN p_matriz_id INT,
  IN p_data DATE,
  IN p_tipo VARCHAR(25),
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO coberturas (
    matriz_id, data_cobertura, tipo_cobertura, usuario_id, data_acao
  )
  VALUES (
    p_matriz_id, p_data, p_tipo, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar procedimento em matriz
CREATE PROCEDURE registrar_procedimento_matriz (
  IN p_matriz_id INT,
  IN p_procedimento_id INT,
  IN p_data DATE,
  IN p_descricao TEXT,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO procedimentos_matrizes (
    matriz_id, procedimento_id, data_procedimento, descricao, usuario_id, data_acao
  )
  VALUES (
    p_matriz_id, p_procedimento_id, p_data, p_descricao, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar procedimento em cria
CREATE PROCEDURE registrar_procedimento_cria (
  IN p_cria_id INT,
  IN p_procedimento_id INT,
  IN p_data DATE,
  IN p_descricao TEXT,
  IN p_usuario_id INT
)
BEGIN
  INSERT INTO procedimentos_crias (
    cria_id, procedimento_id, data_procedimento, descricao, usuario_id, data_acao
  )
  VALUES (
    p_cria_id, p_procedimento_id, p_data, p_descricao, p_usuario_id, NOW()
  );
END$$

-- PROCEDURE: Registrar log de ação
CREATE PROCEDURE registrar_log (
  IN p_usuario_id INT,
  IN p_tabela VARCHAR(50),
  IN p_acao ENUM('inclusao', 'exclusao', 'alteracao', 'consulta'),
  IN p_data DATETIME
)
BEGIN
  INSERT INTO logs (
    usuario_id, tabela, acao, data_acao
  )
  VALUES (
    p_usuario_id, p_tabela, p_acao, p_data
  );
END$$

DELIMITER ;
