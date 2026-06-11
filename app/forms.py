from django import forms
from datetime import date, time

# put here your forms
class CreatePublicReservation(forms.Form):
    cliente_nome = forms.CharField(
        max_length=100,
        label="O SEU NOME",
        widget=forms.TextInput(attrs={
            'placeholder': 'Ex.: João Silva',
        })
    )
    cliente_contacto = forms.CharField(
        max_length=20,
        label="CONTACTO TELEFÓNICO",
        widget=forms.TextInput(attrs={
            'placeholder': 'Ex.: 912 345 678',
        })
    )
    data_reserva = forms.DateField(
        widget=forms.DateInput(attrs={"type": "date"}),
        label="DATA"
    )
    hora = forms.TimeField(
        widget=forms.TimeInput(attrs={"type": "time"}),
        label="HORA"
    )
    numero_pessoas = forms.IntegerField(
        min_value=1,
        max_value=20,
        initial=1,
        label="NÚMERO DE PESSOAS"
    )

    # ---- extra validation examples ----
    def clean_data_reserva(self):
        data = self.cleaned_data["data_reserva"]
        if data < date.today():
            raise forms.ValidationError("A data não pode ser no passado.")
        return data

    def clean_hora(self):
        hora = self.cleaned_data["hora"]
        # TODO: Definir horario de funcionamento
        if hora < time(10, 0) or hora > time(23, 0):
            raise forms.ValidationError("Horário fora do período de funcionamento.")
        return hora

    def clean_cliente_contacto(self):
        contacto = self.cleaned_data["cliente_contacto"]
        contacto = contacto.replace(" ", "")
        if not (contacto and ((contacto[0] == "+" and not contacto[1:].isdigit()) or (contacto.isdigit()))):
            raise forms.ValidationError("Contacto deve conter apenas números.")
        return contacto
