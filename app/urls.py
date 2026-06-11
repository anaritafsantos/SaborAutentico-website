from django.views.generic import RedirectView
from django.templatetags.static import static
from django.urls import path
from . import views
from os import environ as dotenv

urlpatterns = [
    # Like this, it will send a redirect, so all images are in the same place.
    path('favicon.ico', RedirectView.as_view(url=static('assets/favicon.ico'))),
]
