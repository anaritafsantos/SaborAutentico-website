from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth import authenticate, login, logout, get_user_model
from django.contrib.auth.decorators import login_required
from django.views.decorators.http import require_POST, require_GET
from django.core.exceptions import PermissionDenied, ValidationError
from django.core.validators import validate_email
from django.contrib import messages
from django.db import transaction
from django.utils.timezone import now
from app.security.permissions import (
    acesso_total, apenas_admin,
    operar_restaurante, role_required,
    TEAM_ROLES, TOTAL_PERMS
)
from app.models import (
    Cliente, Mesa, Reserva
)
from app.forms import (
    CreatePublicReservation
)

# Create your views here.
User = get_user_model()

@require_GET
def home(request):
    return render(request, "welcome.html", {
        "copyright_year": now().year,
        "already_valid_team_login": ( request.user.is_authenticated and request.user.role in TEAM_ROLES ),
    })

@transaction.atomic
def reserva_public(request):
    # TODO: make use of a django form (template already supports it)
    if request.method == "POST":
        cliente_nome = request.POST.get("cliente_nome")
        cliente_contacto = request.POST.get("cliente_contacto")
        data_reserva = request.POST.get("data_reserva")
        hora = request.POST.get("hora")
        numero_pessoas = request.POST.get("numero_pessoas")
        # Validação básica
        if not all([
            cliente_nome,
            cliente_contacto,
            data_reserva,
            hora,
            numero_pessoas
        ]):
            messages.error(request, "Todos os campos são obrigatórios.")
            return redirect("reserva_publica")
        cliente, created = Cliente.objects.get_or_create(
            contacto=cliente_contacto,
            defaults={
                "nome": cliente_nome
            }
        )
        Reserva.objects.create(
            id_cliente=cliente,
            data_reserva=data_reserva,
            hora=hora,
            numero_pessoas=numero_pessoas,
            estado="pendente"
        )
        messages.success(request, "Reserva solicitada com sucesso!")
        return redirect("home")
    return render(request, "reserva-publica.html")

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
        "has_total_perms": ( request.user.role in TOTAL_PERMS ),
    })

@login_required
@operar_restaurante
@require_POST
def reservas_id(request, id: int):
    if request.POST.get("_method") == "DELETE":
        # Equivalente ao Gate::allows('acesso-total')
        if not request.user.role in TOTAL_PERMS:
            raise PermissionDenied("Acesso Negado! O Staff não pode eliminar registos.")
        reserva = get_object_or_404(Reserva, pk=id)
        reserva.delete()

        messages.success(
            request,
            "Reserva eliminada do sistema pelo administrador."
        )
    else:
        estado = request.POST.get("estado")
        if not estado:
            messages.error(request, "O estado é obrigatório.")
            return redirect("dashboard")
        reserva = get_object_or_404(Reserva, pk=id)
        reserva.estado = estado
        reserva.save()
        messages.success(request,"Estado da reserva/pedido atualizado com sucesso!")
    return redirect("dashboard")

@login_required
@acesso_total
def user_management(request):
    if request.method == "POST":
        name = request.POST.get("name", "").strip()
        email = request.POST.get("email", "").strip()
        password = request.POST.get("password", "")
        role = request.POST.get("role")

        errors = {}

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

        if role not in TEAM_ROLES:
            errors["role"] = "Invalid role."

        if errors:
            return render(request, "admin/users.html", {
                "errors": errors,
                "users": User.objects.order_by("name")
            })

        User.objects.create_user(
            username="N/A",
            name=name,
            email=email,
            password=password,
            role=role,
        )

        messages.success(request, "Utilizador criado com sucesso.")
        return redirect("admin_users")
    users = User.objects.all().order_by("name")
    return render(request, "admin/users.html", {
        "users": users
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
        user = get_object_or_404(User, id=user_id)

        name = request.POST.get("name", "").strip()
        email = request.POST.get("email", "").strip()
        role = request.POST.get("role")

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
                "users": User.objects.order_by("name")
            })

        user.name = name
        user.email = email
        user.role = role
        user.save()
        messages.success(request, "Utilizador atualizado com sucesso.")
    return redirect("admin_users")
