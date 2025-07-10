# Guide d'intégration de l'API Kopo Forum

Ce document décrit l'ensemble des routes réellement disponibles dans l'application FastAPI du projet Kopo Forum. Les exemples utilisent `curl` avec l'API démarrée en local sur `http://localhost:8000`.

Certaines opérations nécessitent un jeton JWT obtenu via l'endpoint `/login` et transmis dans l'en‑tête `Authorization`.

## Authentification

### POST /login
Obtenir un jeton d'accès.

```bash
curl -X POST -F "username=toto" -F "password=secret" http://localhost:8000/login
```

### POST /logout
Révoque le jeton courant.

```bash
curl -H "Authorization: Bearer <TOKEN>" -X POST http://localhost:8000/logout
```

## Utilisateurs

### POST /users
Créer un compte utilisateur.

Corps JSON :
- `username`
- `email`
- `password`
- `bio` *(optionnel)*
- `avatar_url` *(optionnel)*

```bash
curl -X POST -H "Content-Type: application/json" \
     -d '{"username":"toto","email":"toto@example.com","password":"secret"}' \
     http://localhost:8000/users
```

### GET /users/{id}
Récupérer un utilisateur par identifiant.

```bash
curl http://localhost:8000/users/1
```

### GET /users
Lister les utilisateurs.
Paramètres :
- `skip` (défaut 0)
- `limit` (défaut 10)
- `sort` : `newest` ou `oldest`

```bash
curl "http://localhost:8000/users?skip=0&limit=10&sort=newest"
```

### GET /users/count
Nombre total d'utilisateurs.

```bash
curl http://localhost:8000/users/count
```

### PUT/PATCH /users/{id}
Modifier son compte *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X PATCH -H "Content-Type: application/json" \
     -d '{"username":"newname"}' \
     http://localhost:8000/users/1
```

### PUT/PATCH /users/{id}/bio
Mettre à jour uniquement la bio *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X PATCH -H "Content-Type: application/json" \
     -d '{"bio":"Nouvelle bio"}' \
     http://localhost:8000/users/1/bio
```

### DELETE /users/{id}
Supprimer son compte *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" -X DELETE http://localhost:8000/users/1
```

### GET /users/{id}/profile
Afficher le profil d'un utilisateur.

```bash
curl http://localhost:8000/users/1/profile
```

### PUT /users/{id}/profile
Mettre à jour un profil utilisateur.

```bash
curl -X PUT -H "Content-Type: application/json" \
     -d '{"display_name":"John"}' \
     http://localhost:8000/users/1/profile
```

### GET /users/{id}/threads
Lister les fils créés par un utilisateur.

```bash
curl http://localhost:8000/users/1/threads
```

### GET /users/{id}/articles
Lister les articles créés par un utilisateur.

```bash
curl http://localhost:8000/users/1/articles
```

## Articles

### POST /articles
Créer un article *(token requis)*.

Corps JSON :
- `title`
- `content`
- `image_url` *(optionnel)*
- `user_id` *(optionnel)*
- `is_pub` *(bool)*
- `tag_ids` ou `tag_names` *(listes optionnelles)*

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X POST -H "Content-Type: application/json" \
     -d '{"title":"Hello","content":"First post"}' \
     http://localhost:8000/articles
```

### GET /articles
Lister les articles.
Paramètres :
- `skip` (défaut 0)
- `limit` (défaut 10)
- `tag` (filtrer par identifiant de tag)

```bash
curl "http://localhost:8000/articles?skip=0&limit=10"
```

### GET /articles/count
Nombre total d'articles.

```bash
curl http://localhost:8000/articles/count
```

### GET /articles/{id}
Lire un article.

```bash
curl http://localhost:8000/articles/1
```

### PUT/PATCH /articles/{id}
Mettre à jour un article *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X PATCH -H "Content-Type: application/json" \
     -d '{"title":"Nouveau titre"}' \
     http://localhost:8000/articles/1
```

### DELETE /articles/{id}
Supprimer un article *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" -X DELETE http://localhost:8000/articles/1
```

### POST /articles/{id}/comments
Ajouter un commentaire *(token requis)*.

Corps JSON :
- `post_id`
- `content`
- `user_id` *(optionnel)*
- `parent_id` *(optionnel)*

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X POST -H "Content-Type: application/json" \
     -d '{"post_id":1,"content":"Super"}' \
     http://localhost:8000/articles/1/comments
```

### GET /articles/{id}/comments
Lister les commentaires d'un article.

```bash
curl http://localhost:8000/articles/1/comments
```

### PUT/PATCH /comments/{id}
Modifier un commentaire *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X PATCH -H "Content-Type: application/json" \
     -d '{"content":"Édité"}' \
     http://localhost:8000/comments/5
```

### DELETE /comments/{id}
Supprimer un commentaire *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" -X DELETE http://localhost:8000/comments/5
```

### POST /tags
Créer un tag.

```bash
curl -X POST -H "Content-Type: application/json" -d '{"name":"news"}' http://localhost:8000/tags
```

### GET /tags
Lister les tags.

```bash
curl http://localhost:8000/tags
```

### POST /articles/{id}/tags
Associer un tag à un article *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X POST -H "Content-Type: application/json" \
     -d '{"tag_id":1}' \
     http://localhost:8000/articles/1/tags
```

### GET /articles?tag={id}
Filtrer les articles par tag.

```bash
curl "http://localhost:8000/articles?tag=1"
```

## Forum

### POST /forum/categories
Créer une catégorie *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X POST -H "Content-Type: application/json" \
     -d '{"name":"Général"}' \
     http://localhost:8000/forum/categories
```

### GET /forum/categories
Lister les catégories.

```bash
curl http://localhost:8000/forum/categories
```

### POST /forum/threads
Créer un fil de discussion *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X POST -H "Content-Type: application/json" \
     -d '{"title":"Sujet","content":"Contenu"}' \
     http://localhost:8000/forum/threads
```

### GET /forum/threads
Lister les fils de discussion.

```bash
curl http://localhost:8000/forum/threads
```

### GET /forum/threads/{id}
Afficher un fil.

```bash
curl http://localhost:8000/forum/threads/1
```

### PUT/PATCH /forum/threads/{id}
Modifier un fil *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X PATCH -H "Content-Type: application/json" \
     -d '{"is_locked":true}' \
     http://localhost:8000/forum/threads/1
```

### DELETE /forum/threads/{id}
Supprimer un fil *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" -X DELETE http://localhost:8000/forum/threads/1
```

### POST /forum/threads/{thread_id}/replies
Répondre à un fil *(token requis)*.

Corps JSON :
- `thread_id`
- `content`
- `user_id` *(optionnel)*
- `parent_id` *(optionnel)*

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X POST -H "Content-Type: application/json" \
     -d '{"thread_id":1,"content":"Salut"}' \
     http://localhost:8000/forum/threads/1/replies
```

### GET /forum/threads/{thread_id}/replies
Lister les réponses d'un fil.

```bash
curl http://localhost:8000/forum/threads/1/replies
```

### PUT/PATCH /forum/replies/{id}
Éditer une réponse *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X PATCH -H "Content-Type: application/json" \
     -d '{"content":"Modifié"}' \
     http://localhost:8000/forum/replies/3
```

### DELETE /forum/replies/{id}
Supprimer une réponse *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" -X DELETE http://localhost:8000/forum/replies/3
```

---
Ce guide reprend tous les points d'entrée actuellement implémentés dans l'API. Les schémas Pydantic sont disponibles dans `backend/schemas.py` pour plus de détails sur les champs acceptés.
