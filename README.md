# Kopo Forum API

This repository contains a static front‑end and a minimal REST API for the Kopo forum/blog project. The API is built with **FastAPI** and uses **SQLAlchemy** to connect to a PostgreSQL database.

## Requirements

- Python 3.9+
- PostgreSQL database

Install Python dependencies (includes `passlib` for password hashing and
`python-jose` for JWT handling):

```bash
pip install -r requirements.txt
```

Set the `DATABASE_URL` environment variable to point to your PostgreSQL instance. Example:

```bash
export DATABASE_URL=postgresql://user:password@localhost/forumdb
```

The JWT `SECRET_KEY` can also be customised using an environment variable. The
database connection pool size may be tuned with `DB_POOL_SIZE` and
`DB_MAX_OVERFLOW` if needed:

```bash
export SECRET_KEY="your-secret"
export DB_POOL_SIZE=10
export DB_MAX_OVERFLOW=20
```

## Running the API

Start the API with `uvicorn`:

```bash
uvicorn backend.main:app --reload --host 0.0.0.0
```

The application no longer creates database tables automatically. Make sure
the schema exists beforehand (for example using migrations). Set the
`AUTO_CREATE_TABLES` environment variable to `1` if you still want the API to
attempt table creation on startup.

The API will be available on port **8000** of the container. Replace
`<container-ip>` with the actual address of your container, e.g.
`http://<container-ip>:8000`.

## Endpoints

- `POST /users` – create a new user (passwords are hashed server-side)
- `GET /users/{id}` – retrieve a user
- `GET /users` – list users
- `GET /users/count` – total number of users
- `GET /users/{id}/threads` – list an author's threads
- `GET /users/{id}/articles` – list an author's articles
- `PUT/PATCH /users/{id}` – update account information *(requires token)*
- `PUT/PATCH /users/{id}/bio` – update your bio *(requires token)*
- `DELETE /users/{id}` – delete a user account *(requires token)*
- `POST /login` – obtain a JWT access token
- `POST /logout` – revoke the current token
- `POST /articles` – create an article *(requires token)*
- `GET /articles` – list articles
- `GET /articles/count` – total number of articles
- `GET /articles/{id}` – read a single article
- `PUT/PATCH /articles/{id}` – modify an article *(requires token)*
- `DELETE /articles/{id}` – delete an article *(requires token)*
- `POST /articles/{id}/comments` – add a comment *(requires token)*
- `GET /articles/{id}/comments` – list comments
- `PUT/PATCH /comments/{id}` – edit a comment *(requires token)*
- `DELETE /comments/{id}` – remove a comment *(requires token)*
- `POST /tags` – create a tag
- `GET /tags` – list available tags
- `POST /articles/{id}/tags` – link a tag to an article *(requires token)*
- `GET /articles?tag={id}` – filter articles by tag
- `POST /forum/categories` – create a category *(requires token)*
- `GET /forum/categories` – list categories
- `POST /forum/threads` – create a thread *(requires token)*
- `GET /forum/threads` – list threads
- `GET /forum/threads/{id}` – read a thread
- `PUT/PATCH /forum/threads/{id}` – edit a thread *(requires token)*
- `DELETE /forum/threads/{id}` – delete a thread *(requires token)*
- `POST /forum/threads/{thread_id}/replies` – reply to a thread *(requires token)*
- `GET /forum/threads/{thread_id}/replies` – list replies
- `PUT/PATCH /forum/replies/{id}` – edit a reply *(requires token)*
- `DELETE /forum/replies/{id}` – delete a reply *(requires token)*

- `GET /security/alerts` – list recent CERT-FR security alerts
- `GET /security/alerts/latest` – fetch the latest CERT-FR alert

### Admin-only endpoints (require moderator role)

- `GET /admin/users` – list all users
- `DELETE /admin/users/{id}` – remove any user
- `GET /admin/users/{id}/roles` – list a user's roles
- `POST /admin/users/{id}/roles` – assign a role to a user
- `DELETE /admin/users/{id}/roles/{role_id}` – remove a role
- `GET /admin/articles` – list all articles
- `DELETE /admin/articles/{id}` – delete an article
- `GET /admin/comments` – list all comments
- `DELETE /admin/comments/{id}` – delete a comment
- `GET /admin/threads` – list forum threads
- `DELETE /admin/threads/{id}` – delete a thread
- `POST /admin/categories` – create a forum category
- `DELETE /admin/categories/{id}` – delete a forum category
- `POST /admin/tags` – create a tag
- `DELETE /admin/tags/{id}` – delete a tag

This is only a starting point. More models and endpoints can be added following the same pattern.
