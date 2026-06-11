from functools import wraps
from django.http import HttpResponseForbidden
from django.shortcuts import redirect

def role_required(allowed_roles):
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
            if getattr(user, "role", None) not in allowed_roles:
                return HttpResponseForbidden("Permission denied")

            return view_func(request, *args, **kwargs)
        return _wrapped_view
    return decorator


# 1. ACESSO TOTAL (admin + patrao)
def acesso_total(view_func):
    return role_required(["admin", "patrao"])(view_func)


# 2. PERMISSÃO OPERACIONAL (admin + patrao + staff)
def operar_restaurante(view_func):
    return role_required(["admin", "patrao", "staff"])(view_func)


# 3. APENAS ADMIN
def apenas_admin(view_func):
    return role_required(["admin"])(view_func)


def cliente_required(login_url="login"):
    def decorator(view_func):
        @wraps(view_func)
        def _wrapped_view(request, *args, **kwargs):
            if not request.session.get("cliente_id"):
                return redirect(login_url)
            return view_func(request, *args, **kwargs)
        return _wrapped_view
    return decorator