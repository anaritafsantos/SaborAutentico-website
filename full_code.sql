-- =========================
-- 1. TYPES (MUST COME FIRST)
-- =========================
CREATE TYPE mesa_estado AS ENUM ('livre', 'ocupada');
CREATE TYPE reserva_estado AS ENUM ('pendente', 'confirmada', 'cancelada');

-- =========================
-- 2. BASE TABLES
-- =========================

CREATE TABLE IF NOT EXISTS public.categoria (
id_categoria integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
nome varchar(150) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS public.cliente (
id_cliente integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
nome varchar(200) NOT NULL,
contacto varchar(100)
);

CREATE TABLE IF NOT EXISTS public.empregado (
id_empregado integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
nome varchar(200) NOT NULL,
cargo varchar(100),
contacto varchar(100)
);

CREATE TABLE IF NOT EXISTS public.mesa (
id_mesa integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
numero integer NOT NULL UNIQUE,
lugares integer NOT NULL CHECK (lugares > 0),
estado mesa_estado NOT NULL DEFAULT 'livre'
);

-- =========================
-- 3. DEPENDENT TABLES
-- =========================

CREATE TABLE IF NOT EXISTS public.pedido (
id_pedido integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
data_pedido timestamp NOT NULL DEFAULT now(),
id_mesa integer NOT NULL REFERENCES mesa(id_mesa),
id_empregado integer REFERENCES empregado(id_empregado) ON DELETE SET NULL,
estado varchar(50) DEFAULT 'aberto'
);

CREATE TABLE IF NOT EXISTS public.prato (
id_prato integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
nome varchar(200) NOT NULL UNIQUE,
descricao text,
preco numeric(10,2) NOT NULL CHECK (preco >= 0),
disponibilidade boolean NOT NULL DEFAULT true,
id_categoria integer REFERENCES categoria(id_categoria) ON DELETE SET NULL,
quantidade integer NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS public.item_pedido (
id_item integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
quantidade integer NOT NULL CHECK (quantidade > 0),
id_pedido integer NOT NULL REFERENCES pedido(id_pedido) ON DELETE CASCADE,
id_prato integer NOT NULL REFERENCES prato(id_prato),
preco_unitario numeric(10,2) NOT NULL CHECK (preco_unitario >= 0)
);

-- =========================
-- 4. RESERVAS
-- =========================

CREATE TABLE IF NOT EXISTS public.reserva (
id_reserva integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
data_reserva date NOT NULL,
hora time NOT NULL,
numero_pessoas integer NOT NULL CHECK (numero_pessoas > 0),
estado reserva_estado NOT NULL DEFAULT 'pendente',
id_cliente integer NOT NULL REFERENCES cliente(id_cliente),
id_empregado integer REFERENCES empregado(id_empregado)
);

CREATE TABLE IF NOT EXISTS public.reserva_mesa (
id_reserva_mesa integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
id_reserva integer NOT NULL REFERENCES reserva(id_reserva) ON DELETE CASCADE,
id_mesa integer NOT NULL REFERENCES mesa(id_mesa),
UNIQUE (id_reserva, id_mesa)
);

-- =========================
-- 5. FATURA (AFTER PEDIDO)
-- =========================

CREATE TABLE IF NOT EXISTS public.fatura (
id_fatura integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
data_fatura timestamp NOT NULL DEFAULT now(),
total numeric(10,2) NOT NULL CHECK (total >= 0),
metodo_pagamento varchar(100),
id_pedido integer NOT NULL UNIQUE REFERENCES pedido(id_pedido) ON DELETE CASCADE
);

-- =========================
-- 6. INDEXES
-- =========================

CREATE INDEX IF NOT EXISTS idx_item_pedido_id_pedido ON item_pedido(id_pedido);
CREATE INDEX IF NOT EXISTS idx_item_pedido_id_prato ON item_pedido(id_prato);

CREATE INDEX IF NOT EXISTS idx_reserva_id_cliente ON reserva(id_cliente);
CREATE INDEX IF NOT EXISTS idx_reserva_id_empregado ON reserva(id_empregado);

CREATE INDEX IF NOT EXISTS idx_pedido_id_mesa ON pedido(id_mesa);
CREATE INDEX IF NOT EXISTS idx_pedido_id_empregado ON pedido(id_empregado);

-- =========================
-- 7. FUNCTIONS
-- =========================

-- abrir pedido
CREATE OR REPLACE FUNCTION public.abrir_pedido(
p_id_mesa integer,
p_id_empregado integer DEFAULT NULL)
RETURNS integer
LANGUAGE plpgsql
AS $$
DECLARE
pedido_aberto INT;
novo_pedido INT;
BEGIN
SELECT id_pedido INTO pedido_aberto
FROM pedido
WHERE id_mesa = p_id_mesa AND estado = 'aberto'
LIMIT 1;

IF pedido_aberto IS NOT NULL THEN
RETURN pedido_aberto;
END IF;

INSERT INTO pedido (id_mesa, id_empregado)
VALUES (p_id_mesa, p_id_empregado)
RETURNING id_pedido INTO novo_pedido;

UPDATE mesa SET estado = 'ocupada'
WHERE id_mesa = p_id_mesa;

RETURN novo_pedido;
END;
$$;

-- adicionar item ao pedido
CREATE OR REPLACE FUNCTION public.adicionar_item_ao_pedido(
p_id_mesa integer,
p_id_prato integer,
p_quantidade integer,
p_id_empregado integer)
RETURNS integer
LANGUAGE plpgsql
AS $$
DECLARE
pedido_aberto INT;
BEGIN
SELECT id_pedido INTO pedido_aberto
FROM pedido
WHERE id_mesa = p_id_mesa AND estado = 'aberto'
LIMIT 1;

IF pedido_aberto IS NULL THEN
pedido_aberto := abrir_pedido(p_id_mesa, p_id_empregado);
END IF;

INSERT INTO item_pedido (quantidade, id_pedido, id_prato, preco_unitario)
SELECT p_quantidade, pedido_aberto, p_id_prato, preco
FROM prato
WHERE id_prato = p_id_prato;

RETURN pedido_aberto;
END;
$$;

-- adicionar stock
CREATE OR REPLACE FUNCTION public.adicionar_quantidade_prato(
p_id_prato integer,
p_quantidade integer)
RETURNS void
LANGUAGE plpgsql
AS $$
BEGIN
UPDATE prato
SET quantidade = quantidade + p_quantidade
WHERE id_prato = p_id_prato;
END;
$$;

-- fechar pedido
CREATE OR REPLACE FUNCTION public.fechar_pedido(
p_id_pedido integer)
RETURNS void
LANGUAGE plpgsql
AS $$
DECLARE
mesa_relacionada INT;
BEGIN
SELECT id_mesa INTO mesa_relacionada
FROM pedido
WHERE id_pedido = p_id_pedido;

UPDATE pedido SET estado = 'fechado'
WHERE id_pedido = p_id_pedido;

UPDATE mesa SET estado = 'livre'
WHERE id_mesa = mesa_relacionada;
END;
$$;

-- gerar fatura
CREATE OR REPLACE FUNCTION public.gerar_fatura(
p_id_pedido integer,
p_metodo_pagamento varchar)
RETURNS TABLE(
id_item integer,
prato varchar,
quantidade integer,
preco_unitario numeric,
subtotal numeric,
total_pedido numeric,
fatura_id integer)
LANGUAGE plpgsql
AS $$
DECLARE
v_total numeric(10,2);
v_id_fatura integer;
BEGIN

SELECT COALESCE(SUM(ip.quantidade * ip.preco_unitario), 0)
INTO v_total
FROM item_pedido ip
WHERE ip.id_pedido = p_id_pedido;

SELECT id_fatura INTO v_id_fatura
FROM fatura
WHERE id_pedido = p_id_pedido;

IF v_id_fatura IS NULL THEN
INSERT INTO fatura (total, metodo_pagamento, id_pedido)
VALUES (v_total, p_metodo_pagamento, p_id_pedido)
RETURNING id_fatura INTO v_id_fatura;
END IF;

RETURN QUERY
SELECT
ip.id_item,
p.nome,
ip.quantidade,
ip.preco_unitario,
(ip.quantidade * ip.preco_unitario),
v_total,
v_id_fatura
FROM item_pedido ip
JOIN prato p ON p.id_prato = ip.id_prato
WHERE ip.id_pedido = p_id_pedido;

END;
$$;

-- selecionar mesas (FIXED LOOP)
CREATE OR REPLACE FUNCTION public.selecionar_mesas_para_reserva(
p_id_reserva integer)
RETURNS void
LANGUAGE plpgsql
AS $$
DECLARE
pessoas INT;
capacidade INT := 0;
mesa_row RECORD;
BEGIN

SELECT numero_pessoas INTO pessoas
FROM reserva
WHERE id_reserva = p_id_reserva;

FOR mesa_row IN
SELECT * FROM mesa
LOOP
INSERT INTO reserva_mesa(id_reserva, id_mesa)
VALUES (p_id_reserva, mesa_row.id_mesa);

capacidade := capacidade + mesa_row.lugares;

IF capacidade >= pessoas THEN
EXIT;
END IF;
END LOOP;

END;
$$;

-- =========================
-- 8. VIEWS
-- =========================

CREATE OR REPLACE VIEW public.v_stock_pratos AS
SELECT
p.id_prato,
p.nome,
p.quantidade,
c.nome AS categoria
FROM prato p
LEFT JOIN categoria c ON p.id_categoria = c.id_categoria;

CREATE OR REPLACE VIEW public.v_pedidos_abertos AS
SELECT
pe.id_pedido,
pe.data_pedido,
m.numero,
e.nome AS empregado,
pe.estado
FROM pedido pe
JOIN mesa m ON m.id_mesa = pe.id_mesa
LEFT JOIN empregado e ON e.id_empregado = pe.id_empregado
WHERE pe.estado = 'aberto';

CREATE OR REPLACE VIEW public.v_faturas_completas AS
SELECT * FROM fatura;

CREATE OR REPLACE VIEW public.v_reservas_completas AS
SELECT * FROM reserva;

CREATE OR REPLACE VIEW public.v_vendas_diarias AS
SELECT
date(data_fatura) AS dia,
count(*) AS total_faturas,
sum(total) AS receita_total
FROM fatura
GROUP BY date(data_fatura);