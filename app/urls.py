from django.views.generic import RedirectView
from django.templatetags.static import static
from django.contrib.auth import views as auth_views
from django.urls import path
from . import views

urlpatterns = [
    # Like this, it will send a redirect, so all images are in the same place.
    path('favicon.ico', RedirectView.as_view(url=static('assets/favicon.ico'))),
    # --- 1. PÁGINA PRINCIPAL PUBLICITÁRIA ---
    path('', views.home, name='home'),
    # --- 2. SOLICITAÇÃO DE RESERVA PELO CLIENTE (Sem Login) ---
    path('fazer-reserva/', views.reserva_public, name='reserva_publica'),
    # --- 3. CONTROLO DE AUTENTICAÇÃO DA EQUIPA ---
    path('login/', auth_views.LoginView.as_view(template_name='auth/login.html', next_page='dashboard', redirect_authenticated_user=True), name='login'),
    path('logout/', auth_views.LogoutView.as_view(next_page='home'), name='logout'),
    # --- 4. PAINEL DE GESTÃO DO RESTAURANTE (Protegido por Autenticação) ---
    path('dashboard/', views.dashboard_view, name='dashboard'),
    path('reservas/<int:id>/', views.reservas_id, name='reservas_id'),
    # --- 5. Gestão de Utilizadores (apenas Admin e Patrão) ---
    path('admin/users/', views.user_management, name='admin_users'),
    path('admin/users/<int:user_id>/', views.user_management_id, name='admin_users_id'),
]
