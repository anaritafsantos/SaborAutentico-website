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

TOTAL_PERMS = (
    ADMIN_ROLE,
    PATRAO_ROLE,
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

def apenas_admin(view_func):
    return role_required((ADMIN_ROLE,))(view_func)

def acesso_total(view_func):
    return role_required(TOTAL_PERMS)(view_func)

def operar_restaurante(view_func):
    return role_required(TEAM_ROLES)(view_func)
