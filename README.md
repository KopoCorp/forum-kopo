# Kopo Forum API

This repository contains a static front‑end and a minimal REST API for the Kopo forum/blog project. The API is built with **FastAPI** and uses **SQLAlchemy** to connect to a PostgreSQL database.

## Requirements

- Python 3.9+
- PostgreSQL database

Install Python dependencies:

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

- `POST /users` – create a new user
- `GET /users/{id}` – retrieve a user
- `POST /articles` – create a new article
- `GET /articles` – list articles
- `POST /attachments` – upload a file
- `GET /attachments/{id}` – fetch an attachment
- `DELETE /attachments/{id}` – delete an attachment

This is only a starting point. More models and endpoints can be added following the same pattern.
