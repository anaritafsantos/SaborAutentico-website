from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth import authenticate, login, logout, get_user_model
from django.contrib.auth.decorators import login_required
from django.views.decorators.http import require_POST, require_GET
from django.core.exceptions import PermissionDenied, ValidationError
from django.core.validators import validate_email
from django.contrib import messages
from django.db import transaction, connection
from django.utils.timezone import now
from app.security.permissions import (
    acesso_total, apenas_admin,
    operar_restaurante, role_required,
    TEAM_ROLES, TOTAL_PERMS
)
from app.models import (
    Cliente, Mesa, Reserva, ReservaMesa,
    Prato, Pedido, ItemPedido, Categoria
)
from app.forms import CreatePublicReservation

User = get_user_model()


# ─────────────────────────────────────────────────────────────────
# PÚBLICO
# ─────────────────────────────────────────────────────────────────

@require_GET
def home(request):
    return render(request, "welcome.html", {
        "copyright_year": now().year,
        "already_valid_team_login": (
            request.user.is_authenticated and request.user.role in TEAM_ROLES
        ),
    })


@transaction.atomic
def reserva_public(request):
    if request.method == "POST":
        form = CreatePublicReservation(request.POST)
        if form.is_valid():
            cliente, _ = Cliente.objects.get_or_create(
                contacto=form.cleaned_data["cliente_contacto"],
                defaults={"nome": form.cleaned_data["cliente_nome"]}
            )
            Reserva.objects.create(
                id_cliente=cliente,
                data_reserva=form.cleaned_data["data_reserva"],
                hora=form.cleaned_data["hora"],
                numero_pessoas=form.cleaned_data["numero_pessoas"],
                estado="pendente"
            )
            messages.success(request, "Reserva solicitada com sucesso!")
            return redirect("home")
    else:
        form = CreatePublicReservation()
    return render(request, "reserva-publica.html", {"form": form})


# ─────────────────────────────────────────────────────────────────
# DASHBOARD
# ─────────────────────────────────────────────────────────────────

@login_required
@operar_restaurante
@require_GET
def dashboard_view(request):
    reservas = (
        Reserva.objects
        .select_related("id_cliente")
        .order_by("-data_reserva", "-hora")
    )
    return render(request, "auth/dashboard.html", {
        "reservas": reservas,
        "has_total_perms": (request.user.role in TOTAL_PERMS),
    })


@login_required
@operar_restaurante
@require_POST
@transaction.atomic
def reservas_id(request, id: int):
    if request.POST.get("_method") == "DELETE":
        if request.user.role not in TOTAL_PERMS:
            raise PermissionDenied("Acesso Negado! O Staff não pode eliminar registos.")
        reserva = get_object_or_404(Reserva, pk=id)
        reserva.delete()
        messages.success(request, "Reserva eliminada do sistema pelo administrador.")
    else:
        estado = request.POST.get("estado")
        if not estado:
            messages.error(request, "O estado é obrigatório.")
            return redirect("dashboard")
        reserva = get_object_or_404(Reserva, pk=id)
        reserva.estado = estado
        reserva.save()
        if estado == "confirmada" and not ReservaMesa.objects.filter(id_reserva=reserva).exists():
            with connection.cursor() as cursor:
                cursor.execute("SELECT selecionar_mesas_para_reserva(%s)", [id])
        messages.success(request, "Estado da reserva/pedido atualizado com sucesso!")
    return redirect("dashboard")


# ─────────────────────────────────────────────────────────────────
# GESTÃO DE UTILIZADORES
# ─────────────────────────────────────────────────────────────────

@login_required
@acesso_total
def user_management(request):
    if request.method == "POST":
        name     = request.POST.get("name", "").strip()
        email    = request.POST.get("email", "").strip()
        password = request.POST.get("password", "")
        password_confirm = request.POST.get("password_confirm", "")
        role     = request.POST.get("role")
        errors   = {}

        if not name:
            errors["name"] = "Name is required."
        try:
            validate_email(email)
        except ValidationError:
            errors["email"] = "Invalid email."
        if User.objects.filter(email=email).exists():
            errors["email"] = "Email already exists."
        if len(password) < 6:
            errors["password"] = "Password must be at least 6 characters."
        if password != password_confirm:
            errors["password_confirm"] = "Passwords do not match."
        if role not in TEAM_ROLES:
            errors["role"] = "Invalid role."

        if errors:
            return render(request, "admin/users.html", {
                "errors": errors,
                "old": {"name": name, "email": email, "role": role},
                "users": User.objects.order_by("name"),
            })

        User.objects.create_user(
            username="N/A", name=name, email=email,
            password=password, role=role,
        )
        messages.success(request, "Utilizador criado com sucesso.")
        return redirect("admin_users")

    return render(request, "admin/users.html", {
        "users": User.objects.all().order_by("name"),
    })


@login_required
@acesso_total
@require_POST
def user_management_id(request, user_id: int):
    if request.POST.get("_method") == "DELETE":
        user = get_object_or_404(User, id=user_id)
        if user.id == request.user.id:
            messages.error(request, "Não pode eliminar a sua própria conta.")
            return redirect("admin_users")
        user.delete()
        messages.success(request, "Utilizador eliminado com sucesso.")
    else:
        user  = get_object_or_404(User, id=user_id)
        name  = request.POST.get("name", "").strip()
        email = request.POST.get("email", "").strip()
        role  = request.POST.get("role")
        errors = {}

        if not name:
            errors["name"] = "Name is required."
        try:
            validate_email(email)
        except ValidationError:
            errors["email"] = "Invalid email."
        if User.objects.exclude(id=user.id).filter(email=email).exists():
            errors["email"] = "Email already exists."
        if role not in TEAM_ROLES:
            errors["role"] = "Invalid role."

        if errors:
            return render(request, "admin/users.html", {
                "errors": errors,
                "users": User.objects.order_by("name"),
            })

        user.name  = name
        user.email = email
        user.role  = role
        user.save()
        messages.success(request, "Utilizador atualizado com sucesso.")
    return redirect("admin_users")


# ─────────────────────────────────────────────────────────────────
# GESTÃO DE MESAS  (criar / apagar — só admin/patrão)
# ─────────────────────────────────────────────────────────────────

@login_required
@acesso_total
def gestao_mesas(request):
    if request.method == "POST":
        action = request.POST.get("action")

        if action == "criar":
            numero = request.POST.get("numero", "").strip()
            lugares = request.POST.get("lugares", "").strip()
            try:
                numero  = int(numero)
                lugares = int(lugares)
                if lugares < 1:
                    raise ValueError
            except (ValueError, TypeError):
                messages.error(request, "Número e lugares têm de ser inteiros positivos.")
                return redirect("gestao_mesas")

            if Mesa.objects.filter(numero=numero).exists():
                messages.error(request, f"A mesa {numero} já existe.")
                return redirect("gestao_mesas")

            Mesa.objects.create(numero=numero, lugares=lugares, estado="livre")
            messages.success(request, f"Mesa {numero} criada com sucesso.")

        elif action == "apagar":
            mesa_id = request.POST.get("mesa_id")
            mesa = get_object_or_404(Mesa, pk=mesa_id)
            if mesa.estado == "ocupada":
                messages.error(request, f"A mesa {mesa.numero} está ocupada — não pode ser apagada.")
                return redirect("gestao_mesas")
            mesa.delete()
            messages.success(request, f"Mesa apagada com sucesso.")

        return redirect("gestao_mesas")

    mesas = Mesa.objects.order_by("numero")
    return render(request, "admin/gestao_mesas.html", {"mesas": mesas})


# ─────────────────────────────────────────────────────────────────
# GESTÃO DE PEDIDOS
# ─────────────────────────────────────────────────────────────────

@login_required
@operar_restaurante
def gestao_pedido(request):
    mesas = Mesa.objects.order_by("numero")
    mesa_id = request.POST.get("mesa_id") or request.GET.get("mesa")
    mesa_selecionada = None
    itens_pedido     = []
    pedido_aberto    = None
    total_pedido     = 0

    if mesa_id:
        mesa_selecionada = get_object_or_404(Mesa, pk=mesa_id)
        pedido_aberto = Pedido.objects.filter(
            id_mesa=mesa_selecionada, estado="aberto"
        ).first()

        if pedido_aberto:
            itens_pedido = list(
                ItemPedido.objects
                .select_related("id_prato")
                .filter(id_pedido=pedido_aberto)
            )
            total_pedido = sum(
                item.quantidade * item.preco_unitario for item in itens_pedido
            )

    if request.method == "POST":
        action = request.POST.get("action")

        if action == "abrir":
            with connection.cursor() as cursor:
                cursor.execute("SELECT abrir_pedido(%s, %s)", [mesa_id, None])
            messages.success(request, "Pedido aberto com sucesso.")
            return redirect(f"{request.path}?mesa={mesa_id}")

        elif action == "adicionar_item":
            prato_id   = request.POST.get("prato_id")
            quantidade = request.POST.get("quantidade", 1)
            try:
                quantidade = int(quantidade)
                if quantidade < 1:
                    raise ValueError
            except (ValueError, TypeError):
                messages.error(request, "Quantidade inválida.")
                return redirect(f"{request.path}?mesa={mesa_id}")

            with connection.cursor() as cursor:
                cursor.execute(
                    "SELECT adicionar_item_ao_pedido(%s, %s, %s, %s)",
                    [mesa_id, prato_id, quantidade, None]
                )
            messages.success(request, "Item adicionado ao pedido.")
            return redirect(f"{request.path}?mesa={mesa_id}")

        elif action == "fechar":
            pedido_id = request.POST.get("pedido_id")
            if not pedido_id:
                messages.error(request, "Pedido inválido.")
                return redirect(f"{request.path}?mesa={mesa_id}")
            with connection.cursor() as cursor:
                cursor.execute("SELECT fechar_pedido(%s)", [pedido_id])
            messages.success(request, "Pedido fechado. Mesa libertada.")
            return redirect(request.path)

        elif action == "remover_item":
            item_id = request.POST.get("item_id")
            item = get_object_or_404(ItemPedido, pk=item_id)
            item.delete()
            messages.success(request, "Item removido.")
            return redirect(f"{request.path}?mesa={mesa_id}")

    pratos = Prato.objects.filter(disponibilidade=True).order_by("nome")

    return render(request, "staff/gestao_pedido.html", {
        "mesas":            mesas,
        "mesa_selecionada": mesa_selecionada,
        "pedido_aberto":    pedido_aberto,
        "itens_pedido":     itens_pedido,
        "pratos":           pratos,
        "total_pedido":     total_pedido,
    })


# ─────────────────────────────────────────────────────────────────
# STOCK  (listar, adicionar quantidade, criar prato, remover prato)
# ─────────────────────────────────────────────────────────────────

@login_required
@operar_restaurante
def stock_view(request):
    STOCK_MINIMO = 5

    if request.method == "POST":
        action = request.POST.get("action")

        # ── Repor stock existente ─────────────────────────────────
        if action == "repor":
            prato_id   = request.POST.get("prato_id")
            quantidade = request.POST.get("quantidade", 0)
            try:
                quantidade = int(quantidade)
                if quantidade < 1:
                    raise ValueError
            except (ValueError, TypeError):
                messages.error(request, "Quantidade de reposição inválida.")
                return redirect("stock")

            with connection.cursor() as cursor:
                cursor.execute(
                    "SELECT adicionar_quantidade_prato(%s, %s)",
                    [prato_id, quantidade]
                )
            messages.success(request, f"Stock reposto (+{quantidade} unidades).")
            return redirect("stock")

        # ── Adicionar novo prato ──────────────────────────────────
        elif action == "criar_prato":
            nome        = request.POST.get("nome", "").strip()
            preco       = request.POST.get("preco", "").strip()
            quantidade  = request.POST.get("quantidade", "0").strip()
            categoria_id = request.POST.get("categoria_id") or None
            descricao   = request.POST.get("descricao", "").strip()

            if not nome:
                messages.error(request, "O nome do prato é obrigatório.")
                return redirect("stock")
            try:
                preco      = float(preco.replace(",", "."))
                quantidade = int(quantidade)
                if preco < 0 or quantidade < 0:
                    raise ValueError
            except (ValueError, TypeError):
                messages.error(request, "Preço e quantidade têm de ser valores válidos.")
                return redirect("stock")

            if Prato.objects.filter(nome=nome).exists():
                messages.error(request, f'Já existe um prato com o nome "{nome}".')
                return redirect("stock")

            Prato.objects.create(
                nome=nome,
                preco=preco,
                quantidade=quantidade,
                disponibilidade=True,
                descricao=descricao if descricao else None,
                id_categoria_id=categoria_id if categoria_id else None,
            )
            messages.success(request, f'Prato "{nome}" adicionado com sucesso.')
            return redirect("stock")

        # ── Remover prato ─────────────────────────────────────────
        elif action == "remover_prato":
            prato_id = request.POST.get("prato_id")
            prato = get_object_or_404(Prato, pk=prato_id)
            nome  = prato.nome
            prato.delete()
            messages.success(request, f'Prato "{nome}" removido do sistema.')
            return redirect("stock")

        # ── Alternar disponibilidade ──────────────────────────────
        elif action == "toggle_disponibilidade":
            prato_id = request.POST.get("prato_id")
            prato = get_object_or_404(Prato, pk=prato_id)
            prato.disponibilidade = not prato.disponibilidade
            prato.save()
            estado = "disponível" if prato.disponibilidade else "indisponível"
            messages.success(request, f'"{prato.nome}" marcado como {estado}.')
            return redirect("stock")

    # ── GET — lê v_stock_pratos via SQL ──────────────────────────
    with connection.cursor() as cursor:
        cursor.execute(
            "SELECT id_prato, nome, quantidade, categoria "
            "FROM v_stock_pratos ORDER BY nome"
        )
        cols = [c[0] for c in cursor.description]
        pratos_stock = [dict(zip(cols, row)) for row in cursor.fetchall()]

    # Disponibilidade vem do ORM (a view SQL não a tem)
    disp_map = {
        p.pk: p.disponibilidade
        for p in Prato.objects.only("id_prato", "disponibilidade")
    }
    for p in pratos_stock:
        p["disponivel"] = disp_map.get(p["id_prato"], True)

    alertas    = [p for p in pratos_stock if p["quantidade"] <= STOCK_MINIMO]
    categorias = Categoria.objects.order_by("nome")

    return render(request, "staff/stock.html", {
        "pratos_stock": pratos_stock,
        "alertas":      alertas,
        "stock_minimo": STOCK_MINIMO,
        "categorias":   categorias,
    })
