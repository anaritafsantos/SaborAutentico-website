from django.views.generic import RedirectView
from django.templatetags.static import static
from django.contrib.auth import views as auth_views
from django.urls import path
from . import views

urlpatterns = [
    path('favicon.ico', RedirectView.as_view(url=static('assets/favicon.ico'))),
    # 1. Público
    path('', views.home, name='home'),
    path('fazer-reserva/', views.reserva_public, name='reserva_publica'),
    # 2. Auth
    path('login/', auth_views.LoginView.as_view(
        template_name='auth/login.html',
        next_page='dashboard',
        redirect_authenticated_user=True,
    ), name='login'),
    path('logout/', auth_views.LogoutView.as_view(next_page='home'), name='logout'),
    # 3. Dashboard + Reservas
    path('dashboard/', views.dashboard_view, name='dashboard'),
    path('reservas/<int:id>/', views.reservas_id, name='reservas_id'),
    # 4. Utilizadores
    path('admin/users/', views.user_management, name='admin_users'),
    path('admin/users/<int:user_id>/', views.user_management_id, name='admin_users_id'),
    # 5. Mesas (criar/apagar — admin/patrão)
    path('admin/mesas/', views.gestao_mesas, name='gestao_mesas'),
    # 6. Pedidos (staff/patrão/admin)
    path('pedidos/', views.gestao_pedido, name='gestao_pedido'),
    # 7. Stock (staff/patrão/admin)
    path('stock/', views.stock_view, name='stock'),
]
