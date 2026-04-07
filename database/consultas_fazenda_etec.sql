
-- ZOOTÉCNICOS

-- 1. Taxa de natalidade por mês
SELECT 
    DATE_FORMAT(data_efetiva_parto, '%Y-%m') AS mes,
    COUNT(id) AS total_partos,
    SUM(qtd_crias) AS total_crias,
    ROUND(SUM(qtd_crias) / COUNT(id), 2) AS media_crias_por_parto
FROM partos
GROUP BY mes
ORDER BY mes;

-- 2. Média de peso ao nascimento por raça
SELECT 
    raca,
    ROUND(AVG(peso_nascimento), 2) AS media_peso_nascimento
FROM crias
GROUP BY raca;

-- 3. Intervalo entre partos por matriz
SELECT 
    matriz_id,
    DATEDIFF(MAX(data_efetiva_parto), MIN(data_efetiva_parto)) / (COUNT(*) - 1) AS media_dias_entre_partos
FROM partos
WHERE data_efetiva_parto IS NOT NULL
GROUP BY matriz_id
HAVING COUNT(*) > 1;

-- SANITÁRIOS

-- 4. Vacinas aplicadas por mês
SELECT 
    DATE_FORMAT(data_aplicacao, '%Y-%m') AS mes,
    COUNT(*) AS total_aplicacoes
FROM (
    SELECT data_aplicacao FROM vacinas_crias
    UNION ALL
    SELECT data_aplicacao FROM vacinas_matrizes
) AS vacinas
GROUP BY mes
ORDER BY mes;

-- 5. Procedimentos realizados por tipo
SELECT 
    p.nome AS procedimento,
    COUNT(pm.id) AS total_matrizes,
    COUNT(pc.id) AS total_crias
FROM procedimentos p
LEFT JOIN procedimentos_matrizes pm ON p.id = pm.procedimento_id
LEFT JOIN procedimentos_crias pc ON p.id = pc.procedimento_id
GROUP BY p.nome;

-- 6. Eventos sanitários por tipo
SELECT 
    tipo_evento,
    COUNT(*) AS total_eventos
FROM eventos_sanitarios
GROUP BY tipo_evento;

-- FINANCEIROS

-- 7. Receita por mês com vendas
SELECT 
    DATE_FORMAT(data_venda, '%Y-%m') AS mes,
    SUM(valor_total) AS receita_total
FROM vendas
GROUP BY mes
ORDER BY mes;

-- 8. Despesas por categoria
SELECT 
    categoria,
    SUM(valor) AS total_despesas
FROM financeiro
WHERE tipo = 'Despesa'
GROUP BY categoria;

-- 9. Lucro líquido por mês
SELECT 
    receita.mes,
    receita.total_receita,
    COALESCE(despesa.total_despesa, 0) AS total_despesa,
    (receita.total_receita - COALESCE(despesa.total_despesa, 0)) AS lucro_liquido
FROM (
    SELECT DATE_FORMAT(data_venda, '%Y-%m') AS mes, SUM(valor_total) AS total_receita
    FROM vendas
    GROUP BY mes
) AS receita
LEFT JOIN (
    SELECT DATE_FORMAT(data_lancamento, '%Y-%m') AS mes, SUM(valor) AS total_despesa
    FROM financeiro
    WHERE tipo = 'Despesa'
    GROUP BY mes
) AS despesa ON receita.mes = despesa.mes
ORDER BY receita.mes;
