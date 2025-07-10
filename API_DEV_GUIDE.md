# Guide de d'integration de l'API Kopo Forum

Ce document decrit les differents endpoints disponibles dans l'API REST du projet Kopo Forum. Chaque section explique l'utilite de l'endpoint, les parametres attendus et propose un exemple d'appel `curl`.

L'API est accessible par defaut sur `http://localhost:8000` lorsque le serveur est lance via `uvicorn backend.main:app`. Certains appels necessitent un token JWT obtenu avec l'endpoint `/login`.

## Authentification

### POST /login
Obtenir un jeton d'acces JWT.

Parametres formulaire :
- `username` : nom de l'utilisateur
- `password` : mot de passe

```bash
curl -X POST -F "username=toto" -F "password=secret" http://localhost:8000/login
```

### POST /logout
Revoque le token present dans l'en-tete `Authorization`.

```bash
curl -H "Authorization: Bearer <TOKEN>" -X POST http://localhost:8000/logout
```

## Utilisateurs

### POST /users
Creer un utilisateur.

Corps JSON :
- `username`
- `email`
- `password`
- `bio` (optionnel)
- `avatar_url` (optionnel)

```bash
curl -X POST -H "Content-Type: application/json" \
     -d '{"username":"toto","email":"toto@example.com","password":"secret"}' \
     http://localhost:8000/users
```

### GET /users/{id}
Recuperer les informations d'un utilisateur.

```bash
curl http://localhost:8000/users/1
```

### GET /users
Lister les utilisateurs.

Parametres query :
- `skip` (defaut 0)
- `limit` (defaut 10)
- `sort` : `newest` ou `oldest`

```bash
curl "http://localhost:8000/users?skip=0&limit=24&sort=newest"
```

### GET /users/count
Nombre total d'utilisateurs inscrits.

```bash
curl http://localhost:8000/users/count
```




### PUT /users/{id}
Mettre a jour son compte (username, email, mot de passe, bio, avatar). Token requis.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X PUT -H "Content-Type: application/json" \
     -d '{"username":"newname","avatar_url":"https://example.com/avatar.png"}' \
     http://localhost:8000/users/1
```

### PUT /users/{id}/bio
Mettre a jour **uniquement** sa bio *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X PUT -H "Content-Type: application/json" \
     -d '{"bio":"Ma nouvelle bio"}' \
     http://localhost:8000/users/1/bio
```

### GET /users/{id}/profile
Afficher le profil detaille d'un utilisateur.

```bash
curl http://localhost:8000/users/1/profile
```

### PUT /users/{id}/profile
Mettre a jour un profil utilisateur (`display_name`, `website`, `location`, `birth_date`, `gender`).

```bash
curl -X PUT -H "Content-Type: application/json" \
    -d '{"display_name":"John"}' \
     http://localhost:8000/users/1/profile
```

## Articles
Revoque le token present dans l'en-tete `Authorization`.

```bash
curl -H "Authorization: Bearer <TOKEN>" -X POST http://localhost:8000/logout
```

## Utilisateurs

### POST /users
Creer un utilisateur.

Corps JSON :
- `username`
- `email`
- `password`
- `bio` (optionnel)
- `avatar_url` (optionnel)

```bash
curl -X POST -H "Content-Type: application/json" \
     -d '{"username":"toto","email":"toto@example.com","password":"secret"}' \
     http://localhost:8000/users
```

### GET /users/{id}
Recuperer les informations d'un utilisateur.

```bash
curl http://localhost:8000/users/1
```

### PUT /users/{id}/bio
Mettre a jour **uniquement** sa bio *(token requis)*.

```bash
curl -H "Authorization: Bearer <TOKEN>" \
     -X PUT -H "Content-Type: application/json" \
     -d '{"bio":"Ma nouvelle bio"}' \
     http://localhost:8000/users/1/bio
```

### GET /users/{id}/profile
Afficher le profil detaille d'un utilisateur.

```bash
curl http://localhost:8000/users/1/profile
```

### PUT /users/{id}/profile
Mettre a jour un profil utilisateur (`display_name`, `website`, `location`, `birth_date`, `gender`).

```bash
curl -X PUT -H "Content-Type: application/json" \
    -d '{"display_name":"John"}' \
     http://localhost:8000/users/1/profile
```

## Articles

### POST /articles
Creer un nouvel article.

Corps JSON :
- `title`
- `content`
- `user_id` (optionnel)
- `is_pub` (bool, defaut `false`)

```bash
curl -X POST -H "Content-Type: application/json" \
     -d '{"title":"Hello","content":"First post","user_id":1}' \
     http://localhost:8000/articles
```

### GET /articles
Lister les articles.

Parametres query :
- `skip` (defaut 0)
- `limit` (defaut 10)
- `tag` (optionnel)

```bash
curl "http://localhost:8000/articles?skip=0&limit=10"
```

### GET /articles/{id}
Obtenir un article precis.

```bash
curl http://localhost:8000/articles/1
```

### PATCH /articles/{id}
Modifier un article.

```bash
curl -X PATCH -H "Content-Type: application/json" \
     -d '{"title":"Nouveau titre"}' \
     http://localhost:8000/articles/1
```

### DELETE /articles/{id}
Supprimer un article.

```bash
curl -X DELETE http://localhost:8000/articles/1
```

### POST /articles/{id}/comments
Ajouter un commentaire.

Corps JSON :
- `post_id`
- `content`
- `user_id` (optionnel)
- `parent_id` (optionnel)

```bash
curl -X POST -H "Content-Type: application/json" \
     -d '{"post_id":1,"content":"Super","user_id":2}' \
     http://localhost:8000/articles/1/comments
```

### GET /articles/{id}/comments
Lister les commentaires d'un article.

```bash
curl http://localhost:8000/articles/1/comments
```

### PATCH /comments/{id}
Mettre a jour un commentaire.

```bash
curl -X PATCH -H "Content-Type: application/json" \
     -d '{"content":"Edite"}' \
     http://localhost:8000/comments/5
```

### DELETE /comments/{id}
Supprimer un commentaire.

```bash
curl -X DELETE http://localhost:8000/comments/5
```

## Forum

### POST /forum/categories
Creer une categorie de forum.

Corps JSON :
- `name`
- `description` (optionnel)
- `order_index` (optionnel)

```bash
curl -X POST -H "Content-Type: application/json" -d '{"name":"General"}' http://localhost:8000/forum/categories
```

### GET /forum/categories
Lister les categories.

```bash
curl http://localhost:8000/forum/categories
```

### POST /forum/threads
Creer un fil de discussion.

Corps JSON :
- `title`
- `content`
- `category_id` (optionnel)
- `user_id` (optionnel)

```bash
curl -X POST -H "Content-Type: application/json" \
     -d '{"title":"Bienvenue","content":"Presentez vous","category_id":1,"user_id":1}' \
     http://localhost:8000/forum/threads
```

### GET /forum/threads
Lister les fils de discussion.

Parametres query :
- `skip` (defaut 0)
- `limit` (defaut 10)

```bash
curl "http://localhost:8000/forum/threads?skip=0&limit=10"
```

### GET /forum/threads/{id}
Afficher un fil specifique.

```bash
curl http://localhost:8000/forum/threads/1
```

### PATCH /forum/threads/{id}
Mettre a jour un fil (`title`, `content`, `category_id`, `user_id`, `is_locked`, `is_pinned`).

```bash
curl -X PATCH -H "Content-Type: application/json" -d '{"is_locked":true}' http://localhost:8000/forum/threads/1
```

### DELETE /forum/threads/{id}
Supprimer un fil.

```bash
curl -X DELETE http://localhost:8000/forum/threads/1
```

### POST /forum/threads/{id}/replies
Ajouter une reponse dans un fil.

Corps JSON :
- `thread_id`
- `content`
- `user_id` (optionnel)
- `parent_id` (optionnel)

```bash
curl -X POST -H "Content-Type: application/json" \
     -d '{"thread_id":1,"content":"Salut","user_id":2}' \
     http://localhost:8000/forum/threads/1/replies
```

### GET /forum/threads/{id}/replies
Lister les reponses d'un fil.

```bash
curl http://localhost:8000/forum/threads/1/replies
```

### PATCH /forum/replies/{id}
Modifier une reponse.

```bash
curl -X PATCH -H "Content-Type: application/json" -d '{"content":"Modifie"}' http://localhost:8000/forum/replies/3
```

### DELETE /forum/replies/{id}
Supprimer une reponse.

```bash
curl -X DELETE http://localhost:8000/forum/replies/3
```

## Likes

### POST /likes
Ajouter un "j'aime" sur un contenu.

Corps JSON :
- `user_id`
- `target_type`
- `target_id`

```bash
curl -X POST -H "Content-Type: application/json" \
     -d '{"user_id":1,"target_type":"article","target_id":1}' \
     http://localhost:8000/likes
```

## Signalements

### POST /reports
Signaler un contenu.

Corps JSON :
- `reporter_id` (optionnel)
- `target_type`
- `target_id`
- `reason`

```bash
curl -X POST -H "Content-Type: application/json" \
     -d '{"target_type":"article","target_id":1,"reason":"spam"}' \
     http://localhost:8000/reports
```

### GET /reports
Lister les signalements.

```bash
curl http://localhost:8000/reports
```

### PATCH /reports/{id}/resolve
Marquer un signalement comme resolu.

```bash
curl -X PATCH http://localhost:8000/reports/1/resolve
```

## Bannissements

### POST /bans
Bannir un utilisateur.

Corps JSON :
- `user_id`
- `reason`

```bash
curl -X POST -H "Content-Type: application/json" -d '{"user_id":1,"reason":"abus"}' http://localhost:8000/bans
```

### GET /bans
Lister les bannissements actifs.

```bash
curl http://localhost:8000/bans
```

### DELETE /bans/{id}
Lever un bannissement.

```bash
curl -X DELETE http://localhost:8000/bans/1
```

## Tags

### POST /tags
Creer un tag.

Corps JSON :
- `name`

```bash
curl -X POST -H "Content-Type: application/json" -d '{"name":"news"}' http://localhost:8000/tags
```

### GET /tags
Lister les tags existants.

```bash
curl http://localhost:8000/tags
```

### POST /articles/{id}/tags
Associer un tag a un article.

Corps JSON :
- `tag_id`

```bash
curl -X POST -H "Content-Type: application/json" -d '{"tag_id":1}' http://localhost:8000/articles/1/tags
```

### GET /articles?tag={id}
Filtrer les articles par tag.

```bash
curl "http://localhost:8000/articles?tag=1"
```

## Reinitialisation de mot de passe

### POST /password-reset/request
Generer un jeton de reinitialisation.

Corps JSON :
- `email`

```bash
curl -X POST -H "Content-Type: application/json" -d '{"email":"user@example.com"}' http://localhost:8000/password-reset/request
```

### POST /password-reset/confirm
Valider le jeton et definir le nouveau mot de passe (hash).

Corps JSON :
- `token`
- `new_pass_hash`

```bash
curl -X POST -H "Content-Type: application/json" -d '{"token":"<TOKEN>","new_pass_hash":"<HASH>"}' http://localhost:8000/password-reset/confirm
```

## Messagerie

### POST /messages
Envoyer un message direct.

Corps JSON :
- `sender_id` (optionnel)
- `receiver_id`
- `content`

```bash
curl -X POST -H "Content-Type: application/json" -d '{"receiver_id":2,"content":"Bonjour"}' http://localhost:8000/messages
```

### GET /messages/{user_id}
Recuperer les messages pour un utilisateur.

```bash
curl http://localhost:8000/messages/2
```

## Notifications

### POST /notifications
Creer une notification.

Corps JSON :
- `user_id` (optionnel)
- `message`

```bash
curl -X POST -H "Content-Type: application/json" -d '{"user_id":2,"message":"Salut"}' http://localhost:8000/notifications
```

### GET /notifications/{user_id}
Lister les notifications d'un utilisateur.

```bash
curl http://localhost:8000/notifications/2
```

### POST /notifications/{id}/read
Marquer une notification comme lue.

```bash
curl -X POST http://localhost:8000/notifications/1/read
```

## Roles et permissions

### POST /roles
Creer un role.

Corps JSON :
- `name`
- `description` (optionnel)

```bash
curl -X POST -H "Content-Type: application/json" -d '{"name":"admin"}' http://localhost:8000/roles
```

### GET /roles
Lister les roles.

```bash
curl http://localhost:8000/roles
```

### GET /roles/{id}
Detail d'un role.

```bash
curl http://localhost:8000/roles/1
```

### PATCH /roles/{id}
Mettre a jour un role.

```bash
curl -X PATCH -H "Content-Type: application/json" -d '{"description":"Mod"}' http://localhost:8000/roles/1
```

### DELETE /roles/{id}
Supprimer un role.

```bash
curl -X DELETE http://localhost:8000/roles/1
```

### POST /permissions
Creer une permission.

Corps JSON :
- `name`
- `description` (optionnel)

```bash
curl -X POST -H "Content-Type: application/json" -d '{"name":"delete_post"}' http://localhost:8000/permissions
```

### GET /permissions
Lister les permissions.

```bash
curl http://localhost:8000/permissions
```

### GET /permissions/{id}
Detail d'une permission.

```bash
curl http://localhost:8000/permissions/1
```

### PATCH /permissions/{id}
Mettre a jour une permission.

```bash
curl -X PATCH -H "Content-Type: application/json" -d '{"description":"Peut supprimer"}' http://localhost:8000/permissions/1
```

### DELETE /permissions/{id}
Supprimer une permission.

```bash
curl -X DELETE http://localhost:8000/permissions/1
```

### POST /users/{user_id}/roles
Assigner un role a un utilisateur.

Corps JSON :
- `role_id`

```bash
curl -X POST -H "Content-Type: application/json" -d '{"role_id":1}' http://localhost:8000/users/1/roles
```

### GET /users/{user_id}/roles
Lister les roles d'un utilisateur.

```bash
curl http://localhost:8000/users/1/roles
```

### DELETE /users/{user_id}/roles/{role_id}
Retirer un role.

```bash
curl -X DELETE http://localhost:8000/users/1/roles/1
```

## Fichiers joints

### POST /attachments
Envoyer un fichier via `multipart/form-data`.

```bash
curl -X POST -F "file=@image.png" http://localhost:8000/attachments
```

### GET /attachments/{id}
Telecharger un fichier.

```bash
curl http://localhost:8000/attachments/1 -O
```

### DELETE /attachments/{id}
Supprimer un fichier envoye.

```bash
curl -X DELETE http://localhost:8000/attachments/1
```

---
Ce guide resume les principales interactions avec l'API Kopo Forum. Consultez `backend/schemas.py` pour la liste complete des champs disponibles.
