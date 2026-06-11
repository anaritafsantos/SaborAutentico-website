from django.views.generic import RedirectView
from django.templatetags.static import static
from django.contrib.auth import views as auth_views
from django.urls import path
from . import views
from os import environ as dotenv

urlpatterns = [
    # Like this, it will send a redirect, so all images are in the same place.
    path('favicon.ico', RedirectView.as_view(url=static('assets/favicon.ico'))),
    # --- 1. PÁGINA PRINCIPAL PUBLICITÁRIA ---
    path('', views.home, name='home'),
    # --- 2. SOLICITAÇÃO DE RESERVA PELO CLIENTE (Sem Login) ---
    path('fazer-reserva/', views.reservaPublicCreate, name='reserva_publica'),
    # --- 3. CONTROLO DE AUTENTICAÇÃO DA EQUIPA ---
    path('login/', auth_views.LoginView.as_view(template_name='auth/login.html', next_page='dashboard'), name='login'),
    path('logout/', auth_views.LogoutView.as_view(next_page='home'), name='logout'),
    # --- 4. PAINEL DE GESTÃO DO RESTAURANTE (Protegido por Autenticação) ---
    path('dashboard/', views.dashboardView, name='dashboard'),
    # --- 5. Gestão de Utilizadores (apenas Admin e Patrão) ---
    path('admin/users/', views.user_management, name='admin_users'),
]
"""
# --- 4. PAINEL DE GESTÃO DO RESTAURANTE (Protegido por Autenticação) ---
# POST ONLY
path('reservas/<int:id>/confirmar/', views.atualizarEstadoView, name='reservas.confirmar'),
# DELETE ONLY
path('reservas/<int:id>/', views.reservaDeleteView, name='reservas.destroy'),

# --- 5. Gestão de Utilizadores (apenas Admin e Patrão) ---
path('admin/users/', views.userListView, name='admin.users'),
# POST ONLY path('admin/users/', views.userCreateView, name='admin.users'),
# PUT ONLY
path('admin/users/<int:pk>/', views.userUpdateView, name='admin.users.update'),
# DELETE ONLY path('admin/users/<int:pk>/', views.userDeleteView, name='admin.users.destroy'),
"""