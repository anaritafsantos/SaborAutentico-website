from functools import wraps
from django.http import HttpResponseForbidden
from django.shortcuts import redirect

ADMIN_ROLE = "admin"
PATRAO_ROLE = "patrao"
STAFF_ROLE = "staff"
DEFAULT_ROLE = "none"

TEAM_ROLES = (
    ADMIN_ROLE,
    PATRAO_ROLE,
    STAFF_ROLE
)

def role_required(allowed_roles: tuple):
    """
    Generic role-based decorator (base for all permissions)
    """
    def decorator(view_func):
        @wraps(view_func)
        def _wrapped_view(request, *args, **kwargs):
            user = request.user

            # Ensure user is authenticated
            if not user or not user.is_authenticated:
                return HttpResponseForbidden("Authentication required")

            # Check role attribute
            if (getattr(user, "role", None) not in allowed_roles) and not getattr(user, "is_superuser", False):
                return HttpResponseForbidden("Permission denied")

            return view_func(request, *args, **kwargs)
        return _wrapped_view
    return decorator

# 1. APENAS ADMIN
def apenas_admin(view_func):
    return role_required((ADMIN_ROLE,))(view_func)

# 2. ACESSO TOTAL (admin + patrao)
def acesso_total(view_func):
    return role_required((ADMIN_ROLE, PATRAO_ROLE))(view_func)


# 3. PERMISSÃO OPERACIONAL (admin + patrao + staff)
def operar_restaurante(view_func):
    return role_required(TEAM_ROLES)(view_func)


def cliente_required(login_url="/auth/login"):
    def decorator(view_func):
        @wraps(view_func)
        def _wrapped_view(request, *args, **kwargs):
            if not request.session.get("cliente_id"):
                return redirect(login_url)
            return view_func(request, *args, **kwargs)
        return _wrapped_view
    return decorator
