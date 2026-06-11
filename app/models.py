# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#   * Rearrange models' order
#   * Make sure each model has one field with primary_key=True
#   * Make sure each ForeignKey and OneToOneField has `on_delete` set to the desired behavior
#   * Remove `managed = True` lines if you wish to allow Django to create, modify, and delete the table
# Feel free to rename the models, but don't rename db_table values or field names.
from django.db import models


class Categoria(models.Model):
    id_categoria = models.AutoField(primary_key=True)
    nome = models.CharField(unique=True, max_length=150)

    class Meta:
        managed = True
        db_table = 'categoria'


class Cliente(models.Model):
    id_cliente = models.AutoField(primary_key=True)
    nome = models.CharField(max_length=200)
    contacto = models.CharField(max_length=100, blank=True, null=True)

    class Meta:
        managed = True
        db_table = 'cliente'


class Empregado(models.Model):
    id_empregado = models.AutoField(primary_key=True)
    nome = models.CharField(max_length=200)
    cargo = models.CharField(max_length=100, blank=True, null=True)
    contacto = models.CharField(max_length=100, blank=True, null=True)

    class Meta:
        managed = True
        db_table = 'empregado'


class Fatura(models.Model):
    id_fatura = models.AutoField(primary_key=True)
    data_fatura = models.DateTimeField()
    total = models.DecimalField(max_digits=10, decimal_places=2)
    metodo_pagamento = models.CharField(max_length=100, blank=True, null=True)
    id_pedido = models.OneToOneField('Pedido', models.DO_NOTHING, db_column='id_pedido')

    class Meta:
        managed = True
        db_table = 'fatura'


class ItemPedido(models.Model):
    id_item = models.AutoField(primary_key=True)
    quantidade = models.IntegerField()
    id_pedido = models.ForeignKey('Pedido', models.DO_NOTHING, db_column='id_pedido')
    id_prato = models.ForeignKey('Prato', models.DO_NOTHING, db_column='id_prato')
    preco_unitario = models.DecimalField(max_digits=10, decimal_places=2)

    class Meta:
        managed = True
        db_table = 'item_pedido'


class Mesa(models.Model):
    id_mesa = models.AutoField(primary_key=True)
    numero = models.IntegerField(unique=True)
    lugares = models.IntegerField()
    estado = models.TextField()  # This field type is a guess.

    class Meta:
        managed = True
        db_table = 'mesa'


class Pedido(models.Model):
    id_pedido = models.AutoField(primary_key=True)
    data_pedido = models.DateTimeField()
    id_mesa = models.ForeignKey(Mesa, models.DO_NOTHING, db_column='id_mesa')
    id_empregado = models.ForeignKey(Empregado, models.DO_NOTHING, db_column='id_empregado', blank=True, null=True)
    estado = models.CharField(max_length=50, blank=True, null=True)

    class Meta:
        managed = True
        db_table = 'pedido'


class Prato(models.Model):
    id_prato = models.AutoField(primary_key=True)
    nome = models.CharField(unique=True, max_length=200)
    descricao = models.TextField(blank=True, null=True)
    preco = models.DecimalField(max_digits=10, decimal_places=2)
    disponibilidade = models.BooleanField()
    id_categoria = models.ForeignKey(Categoria, models.DO_NOTHING, db_column='id_categoria', blank=True, null=True)
    quantidade = models.IntegerField()

    class Meta:
        managed = True
        db_table = 'prato'


class Reserva(models.Model):
    id_reserva = models.AutoField(primary_key=True)
    data_reserva = models.DateField()
    hora = models.TimeField()
    numero_pessoas = models.IntegerField()
    estado = models.TextField()  # This field type is a guess.
    id_cliente = models.ForeignKey(Cliente, models.DO_NOTHING, db_column='id_cliente')
    id_empregado = models.ForeignKey(Empregado, models.DO_NOTHING, db_column='id_empregado', blank=True, null=True)

    class Meta:
        managed = True
        db_table = 'reserva'


class ReservaMesa(models.Model):
    id_reserva_mesa = models.AutoField(primary_key=True)
    id_reserva = models.ForeignKey(Reserva, models.DO_NOTHING, db_column='id_reserva')
    id_mesa = models.ForeignKey(Mesa, models.DO_NOTHING, db_column='id_mesa')

    class Meta:
        managed = True
        db_table = 'reserva_mesa'
        unique_together = (('id_reserva', 'id_mesa'),)
