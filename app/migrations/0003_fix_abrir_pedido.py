from django.db import migrations


class Migration(migrations.Migration):

    dependencies = [
        ("app", "0002_add_remaining_sql"),
    ]

    operations = [
        migrations.RunSQL(
            sql="""
CREATE OR REPLACE FUNCTION public.abrir_pedido(
    p_id_mesa integer,
    p_id_empregado integer DEFAULT NULL
)
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

    INSERT INTO pedido (data_pedido, id_mesa, id_empregado)
    VALUES (NOW(), p_id_mesa, p_id_empregado)
    RETURNING id_pedido INTO novo_pedido;

    UPDATE mesa SET estado = 'ocupada'
    WHERE id_mesa = p_id_mesa;

    RETURN novo_pedido;
END;
$$;
""",
            reverse_sql="""
CREATE OR REPLACE FUNCTION public.abrir_pedido(
    p_id_mesa integer,
    p_id_empregado integer DEFAULT NULL
)
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
""",
        )
    ]
