# INSTALL
```bash
$ git clone GIT_REPO
$ python3 -m venv .venv
$ .\\.venv\\bin\\activate # Windows
$ source ./.venv/bin/activate # Linux/Mac
$ pip install -r requirements.txt
$ cp env.txt .env
$ # Editar os valores em .env para os necessarios
$ python manage.py migrate
```

# RUNNING
```bash
$ python manage.py runserver 8000
```
