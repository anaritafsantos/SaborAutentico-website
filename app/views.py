from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth import authenticate, login, logout, get_user_model
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied, ValidationError
from django.core.validators import validate_email
from django.contrib import messages
from django.db import transaction
from app.security.permissions import (
    acesso_total, apenas_admin,
    operar_restaurante, role_required,
    TEAM_ROLES
)
from app.models import (
    Cliente, Mesa, Reserva
)

# Create your views here.
ROLES = ["admin", "patrao", "staff"]
User = get_user_model()

#############################  CLIENT AUTH CONTROLLER #############################
def show_login(request):
    if request.user.is_authenticated:
        return redirect('cliente_dashboard')

    return render(request, 'cliente/login.html')


def login_view(request):
    if request.method == 'POST':
        contacto = request.POST.get('contacto')
        password = request.POST.get('password')

        user = authenticate(request, username=contacto, password=password)

        if user is not None:
            login(request, user)
            return redirect('cliente_dashboard')

        messages.error(request, 'Dados inválidos.')

    return render(request, 'cliente/login.html')


def show_register(request):
    return render(request, 'cliente/register.html')


def register(request):
    if request.method == 'POST':
        nome = request.POST.get('nome')
        contacto = request.POST.get('contacto')
        password = request.POST.get('password')
        password_confirm = request.POST.get('password_confirm')

        if Cliente.objects.filter(contacto=contacto).exists():
            messages.error(request, 'Contacto já existe.')
            return render(request, 'cliente/register.html')

        if password != password_confirm:
            messages.error(request, 'Passwords não coincidem.')
            return render(request, 'cliente/register.html')

        cliente = Cliente.objects.create_user(
            username=contacto,  # if using Django auth
            contacto=contacto,
            nome=nome,
            password=password
        )

        login(request, cliente)
        return redirect('cliente_dashboard')

    return render(request, 'cliente/register.html')


def logout_view(request):
    logout(request)
    return redirect('home')


@login_required
def dashboard(request):
    cliente = request.user

    reservas = Reserva.objects.filter(
        id_cliente=cliente.id_cliente
    )

    return render(
        request,
        'cliente/dashboard.html',
        {
            'cliente': cliente,
            'reservas': reservas
        }
    )


@login_required
def create_reserva(request):
    mesas = Mesa.objects.filter(estado='livre')

    return render(
        request,
        'cliente/reserva_create.html',
        {'mesas': mesas}
    )


@login_required
def store_reserva(request):
    if request.method == 'POST':
        data_reserva = request.POST.get('data_reserva')
        hora = request.POST.get('hora')
        numero_pessoas = request.POST.get('numero_pessoas')

        Reserva.objects.create(
            id_cliente=request.user,
            data_reserva=data_reserva,
            hora=hora,
            numero_pessoas=numero_pessoas,
            estado='pendente'
        )

        messages.success(request, 'Reserva criada!')
        return redirect('cliente_dashboard')

    return redirect('create_reserva')
##########################################################

#############################  RESERVA CONTROLLER #############################
@login_required
def index(request):
    reservas = (
        Reserva.objects
        .select_related("cliente")
        .order_by("-data_reserva", "-hora")
    )

    return render(
        request,
        "auth/dashboard.html",
        {"reservas": reservas}
    )

# Atualizar Estado da Reserva
@login_required
def atualizar_estado(request, reserva_id):

    # Equivalente ao Gate::allows('operar-restaurante')
    if not request.user.has_perm("app.operar_restaurante"):
        raise PermissionDenied(
            "Não tem permissão para operar o restaurante."
        )

    if request.method == "POST":
        estado = request.POST.get("estado")

        if not estado:
            messages.error(request, "O estado é obrigatório.")
            return redirect("dashboard")

        reserva = get_object_or_404(Reserva, pk=reserva_id)
        reserva.estado = estado
        reserva.save()

        messages.success(
            request,
            "Estado da reserva/pedido atualizado com sucesso!"
        )

    return redirect("dashboard")

# Eliminar Reserva
@login_required
def destroy(request, reserva_id):

    # Equivalente ao Gate::allows('acesso-total')
    if not request.user.has_perm("app.acesso_total"):
        raise PermissionDenied(
            "Acesso Negado! O Staff não pode eliminar registos."
        )

    reserva = get_object_or_404(Reserva, pk=reserva_id)
    reserva.delete()

    messages.success(
        request,
        "Reserva eliminada do sistema pelo administrador."
    )

    return redirect("dashboard")
##########################################################


#############################  USER CONTROLLER #############################
@login_required
def index(request):
    users = User.objects.all().order_by("name")

    return render(request, "admin/users.html", {
        "users": users
    })

@login_required
def store(request):
    if request.method != "POST":
        return redirect("users")

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

    if role not in ROLES:
        errors["role"] = "Invalid role."

    if errors:
        return render(request, "admin/users.html", {
            "errors": errors,
            "users": User.objects.order_by("name")
        })

    User.objects.create_user(
        username=email,  # remove if using custom user model
        name=name,
        email=email,
        password=password,
        role=role,
    )

    messages.success(request, "Utilizador criado com sucesso.")
    return redirect("users")

@login_required
def update(request, user_id):
    if request.method != "POST":
        return redirect("users")

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

    if role not in ROLES:
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
    return redirect("users")

@login_required
def destroy(request, user_id):
    user = get_object_or_404(User, id=user_id)

    if user.id == request.user.id:
        messages.error(request, "Não pode eliminar a sua própria conta.")
        return redirect("users")

    user.delete()

    messages.success(request, "Utilizador eliminado com sucesso.")
    return redirect("users")
##########################################################


############################# VISTOS ###################################
def home(request):
    print((request.user.is_authenticated and request.user.role in TEAM_ROLES))
    return render(request, "welcome.html", {
        "already_valid_team_login": ( request.user.is_authenticated and request.user.role in TEAM_ROLES ),
    })

@transaction.atomic
def reservaPublicCreate(request):
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
def dashboardView(request):
    return render(request, "auth/dashboard.html")

@login_required
@acesso_total
def user_management(request):
    return render(request, "admin/users.html")
