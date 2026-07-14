# Gestion de cotisations

Projet PHP procédural utilisant des sessions pour stocker les données.

## Démarrage local

Depuis le dossier `public` :

```bash
php -S localhost:8000 router.php
```

Puis ouvrez :

- `http://127.0.0.1:8000/login`

## Routes disponibles

- `GET /login`
- `POST /login`
- `GET /inscription`
- `POST /inscription`
- `GET /gerant/dashboard`
- `GET /gerant/paiements/create`
- `POST /gerant/paiements/create`
- `GET /gerant/campagnes/create`
- `POST /gerant/campagnes/create`
- `GET /gerant/apprenants`
- `POST /gerant/apprenants`
