# INSTALL
```bash
$ git clone GIT_REPO
$ cd GIT_REPO_NAME
$ python3 -m venv .venv
$ .\\.venv\\Scripts\\activate.bat # Windows
$ source ./.venv/bin/activate # Linux/Mac
$ pip install -r requirements.txt
$ copy env.txt .env # Windows
$ cp env.txt .env # Linux/Mac
$ # Editar os valores em .env para os necessarios
$ python manage.py migrate
```

# Creating the SuperUser
```bash
$ python manage.py createsuperuser
```
Fill as needed, but for `Role` **ALWAYS PUT** `admin`

# RUNNING
If on windows CMD:
```bash
.\\run_server.bat
```
If on Linux/Mac and already in the Virtual Environment
```bash
(.venv) $ python manage.py runserver 8000
```

## Acrescentar por ordem de prioridade:
1. pedir p meter as letras em branco no dashboard e gestao de pedidos nao esta a funcionar

