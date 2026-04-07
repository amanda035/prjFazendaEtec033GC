
-- Inserção em racas_suinas
INSERT INTO racas_suinas (nome, origem, finalidade_id, descricao) VALUES
('Large White', 'Inglaterra', 2, 'Raça com boa habilidade materna e prolificidade.'),
('Duroc', 'EUA', 1, 'Raça voltada para produção de carne com bom ganho de peso.'),
('Piau', 'Brasil', 3, 'Raça rústica, adaptada ao clima tropical.');

-- Inserção em matrizes
INSERT INTO matrizes (nome, raca_id, data_nascimento, usuario_criacao_id, usuario_atualizacao_id) VALUES
('Matilda', 1, '2022-03-15', 1, 1),
('Lola', 2, '2021-11-20', 1, 1),
('Bela', 3, '2022-06-10', 1, 1);

-- Inserção em partos
INSERT INTO partos (matriz_id, data_prevista_parto, data_efetiva_parto, data_prevista_desmame, data_efetiva_desmame, data_prevista_maternidade, data_efetiva_maternidade, qtd_crias, usuario_criacao_id, usuario_atualizacao_id) VALUES
(1, '2024-01-10', '2024-01-12', '2024-02-10', '2024-02-12', '2024-01-05', '2024-01-06', 10, 1, 1),
(2, '2024-02-15', '2024-02-16', '2024-03-15', '2024-03-16', '2024-02-10', '2024-02-11', 8, 1, 1),
(3, '2024-03-20', '2024-03-21', '2024-04-20', '2024-04-21', '2024-03-15', '2024-03-16', 12, 1, 1);

-- Inserção em baias
INSERT INTO baias (nome, tipo_baia_id, descricao, capacidade, usuario_criacao_id, usuario_atualizacao_id) VALUES
('Baia A1', 1, 'Baia para gestação', 10, 1, 1),
('Baia B1', 2, 'Baia para maternidade', 8, 1, 1),
('Baia C1', 3, 'Baia para desmame', 12, 1, 1);

-- Inserção em crias
INSERT INTO crias (parto_id, nome, raca_id, tipo_sexo_id, peso_nascimento, data_nascimento, tipo_status_id, baia_id, usuario_criacao_id, usuario_atualizacao_id) VALUES
(1, 'Cria1', 1, 1, 1.20, '2024-01-12', 1, 3, 1, 1),
(2, 'Cria2', 2, 2, 1.10, '2024-02-16', 1, 3, 1, 1),
(3, 'Cria3', 3, 1, 1.30, '2024-03-21', 1, 3, 1, 1);

-- Inserção em vacinas
INSERT INTO vacinas (nome, descricao, usuario_criacao_id, usuario_atualizacao_id) VALUES
('Vacina A', 'Proteção contra doenças respiratórias', 1, 1),
('Vacina B', 'Imunização contra diarreia neonatal', 1, 1),
('Vacina C', 'Vacina polivalente', 1, 1);

-- Inserção em vacinas_matrizes
INSERT INTO vacinas_matrizes (matriz_id, vacina_id, data_aplicacao, usuario_criacao_id, usuario_atualizacao_id) VALUES
(1, 1, '2024-01-01', 1, 1),
(2, 2, '2024-02-01', 1, 1),
(3, 3, '2024-03-01', 1, 1);

-- Inserção em vacinas_crias
INSERT INTO vacinas_crias (cria_id, vacina_id, data_aplicacao, usuario_criacao_id, usuario_atualizacao_id) VALUES
(1, 1, '2024-01-20', 1, 1),
(2, 2, '2024-02-25', 1, 1),
(3, 3, '2024-03-30', 1, 1);
