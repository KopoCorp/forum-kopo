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

## Running the API

Start the API with `uvicorn`:

```bash
uvicorn backend.main:app --reload --host 0.0.0.0
```

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

This is only a starting point. More models and endpoints can be added following the same pattern.
