# Guide de l'API Kopo Forum

Ce document fournit une description détaillée de l'API REST proposée dans ce dépôt. Il s'adresse aux développeurs web souhaitant intégrer les fonctionnalités du forum/blog dans leurs propres applications.

## Mise en place

1. **Prérequis**
   - Python 3.9 ou supérieur
   - Base de données PostgreSQL
2. **Installation des dépendances**
   ```bash
   pip install -r requirements.txt
   ```
3. **Configuration de la base**
   Définissez la variable d'environnement `DATABASE_URL` avec la chaîne de connexion PostgreSQL :
   ```bash
   export DATABASE_URL=postgresql://user:password@localhost/forumdb
   ```
4. **Lancement du serveur**
   ```bash
   uvicorn backend.main:app --reload --host 0.0.0.0
   ```
   L'API sera accessible sur le port **8000**.

## Modèles principaux

- **User** : utilisateur de la plateforme
- **Article** : article de blog rédigé par un utilisateur
- **Comment** : commentaire lié à un article
- **ForumCategory** : catégorie de discussion du forum
- **ForumThread** : fil de discussion dans une catégorie
- **ForumReply** : réponse dans un fil
- **Like** : enregistrement d'un "j'aime" sur un article ou un autre type de contenu

## Endpoints essentiels

### Utilisateurs
| Méthode | Chemin            | Description                    |
|---------|------------------|--------------------------------|
| POST    | `/users`         | Créer un nouvel utilisateur    |
| GET     | `/users/{id}`    | Récupérer un utilisateur       |

### Articles
| Méthode | Chemin                             | Description                     |
|---------|-----------------------------------|---------------------------------|
| POST    | `/articles`                        | Créer un article                |
| GET     | `/articles`                        | Lister les articles             |
| GET     | `/articles/{id}`                   | Obtenir un article              |
| POST    | `/articles/{id}/comments`          | Ajouter un commentaire          |
| GET     | `/articles/{id}/comments`          | Lister les commentaires         |

### Forum
| Méthode | Chemin                                          | Description                              |
|---------|------------------------------------------------|------------------------------------------|
| POST    | `/forum/categories`                             | Créer une catégorie                      |
| GET     | `/forum/categories`                             | Lister les catégories                    |
| POST    | `/forum/threads`                                | Créer un fil de discussion              |
| GET     | `/forum/threads`                                | Lister les fils                         |
| GET     | `/forum/threads/{id}`                           | Récupérer un fil spécifique             |
| POST    | `/forum/threads/{id}/replies`                   | Répondre dans un fil                    |
| GET     | `/forum/threads/{id}/replies`                   | Lister les réponses d'un fil            |

### Likes
| Méthode | Chemin  | Description                       |
|---------|--------|-----------------------------------|
| POST    | `/likes` | Ajouter un "j'aime" sur un contenu |

## Structures de données

Les schémas utilisés par l'API sont définis dans `backend/schemas.py`. Les modèles principaux contiennent les champs suivants (extraits) :

- **User** : `id`, `username`, `email`, `bio`, `avatar_url`, `is_active`, `created_at`
- **Article** : `id`, `user_id`, `title`, `content`, `is_pub`, `created_at`, `updated_at`
- **Comment** : `id`, `post_id`, `user_id`, `content`, `parent_id`, `created_at`
- **ForumThread** : `id`, `title`, `content`, `category_id`, `user_id`, `is_locked`, `is_pinned`, `created_at`, `updated_at`

Les réponses de l'API utilisent le mode `orm_mode` de Pydantic afin de renvoyer les champs cités ci-dessus au format JSON.

## Exécuter des appels de test

Un script `api_test.py` est fourni pour effectuer une série d'appels à l'API et vérifier son fonctionnement basique. Après avoir lancé le serveur, exécutez :
```bash
python api_test.py http://localhost:8000
```

Chaque étape affichera le statut de la requête et la réponse JSON reçue.

---
Ce guide couvre les points principaux permettant de démarrer rapidement avec l'API du forum Kopo. N'hésitez pas à étendre les modèles et endpoints en fonction de vos besoins.
