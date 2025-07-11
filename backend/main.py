from typing import List, Optional
from datetime import datetime, timedelta
from uuid import uuid4

from fastapi import FastAPI, Depends, HTTPException, UploadFile, File
from fastapi.responses import FileResponse
from fastapi.security import OAuth2PasswordBearer, OAuth2PasswordRequestForm

from jose import JWTError, jwt
from passlib.context import CryptContext

from sqlalchemy.orm import Session
from threading import Lock
import os
import uuid
import shutil
import html
import requests
import feedparser

from . import models, schemas
from .database import Base, engine, get_db

# Create database tables if they don't exist
Base.metadata.create_all(bind=engine)

app = FastAPI(title="Kopo Forum API")

# Security configuration
SECRET_KEY = "secret-key"  # In production use environment variable
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="login")
revoked_tokens: set[str] = set()


def verify_password(plain_password: str, hashed_password: str) -> bool:
    return pwd_context.verify(plain_password, hashed_password)


def get_password_hash(password: str) -> str:
    return pwd_context.hash(password)


def create_access_token(data: dict, expires_delta: Optional[timedelta] = None) -> str:
    to_encode = data.copy()
    expire = datetime.utcnow() + (expires_delta or timedelta(minutes=15))
    to_encode.update({"exp": expire})
    return jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)


def get_current_user(token: str = Depends(oauth2_scheme), db: Session = Depends(get_db)):
    if token in revoked_tokens:
        raise HTTPException(status_code=401, detail="Token revoked")
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        user_id: str = payload.get("sub")
        if user_id is None:
            raise HTTPException(status_code=401, detail="Invalid token")
    except JWTError:
        raise HTTPException(status_code=401, detail="Invalid token")
    user = db.query(models.User).get(int(user_id))
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    return user


@app.post("/users", response_model=schemas.UserOut)
def create_user(user: schemas.UserCreate, db: Session = Depends(get_db)):
    db_user = db.query(models.User).filter(models.User.username == user.username).first()
    db_usermail = db.query(models.User).filter(models.User.email == user.email).first()
    if db_user:
        raise HTTPException(status_code=400, detail="This username already exist")
    if db_usermail:
        raise HTTPException(status_code=400, detail="This email is already use by another account")
    db_user = models.User(
        username=user.username,
        email=user.email,
        bio=user.bio,
        avatar_url=user.avatar_url,
        pass_hash=get_password_hash(user.password),
    )
    db.add(db_user)
    db.commit()
    db.refresh(db_user)
    return db_user


@app.delete("/users/{user_id}", status_code=204)
def delete_user(
    user_id: int,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    if current_user.id != user_id:
        raise HTTPException(status_code=403, detail="Not authorized to delete this user")
    db_user = db.query(models.User).get(user_id)
    if not db_user:
        raise HTTPException(status_code=404, detail="User not found")
    db.delete(db_user)
    db.commit()


@app.post("/login")
def login(form_data: OAuth2PasswordRequestForm = Depends(), db: Session = Depends(get_db)):
    user = db.query(models.User).filter(models.User.username == form_data.username).first()
    if not user or not verify_password(form_data.password, user.pass_hash):
        raise HTTPException(status_code=401, detail="Invalid credentials")
    access_token_expires = timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    token = create_access_token(data={"sub": str(user.id)}, expires_delta=access_token_expires)
    return {"access_token": token, "token_type": "bearer"}


@app.post("/logout")
def logout(token: str = Depends(oauth2_scheme)):
    revoked_tokens.add(token)
    return {"detail": "Logged out"}


@app.get("/users", response_model=List[schemas.UserOut])
def list_users(
    skip: int = 0,
    limit: int = 10,
    sort: str = "newest",
    db: Session = Depends(get_db),
):
    """List users with optional pagination and sorting."""
    query = db.query(models.User)
    if sort == "newest":
        query = query.order_by(models.User.created_at.desc())
    elif sort == "oldest":
        query = query.order_by(models.User.created_at)
    return query.offset(skip).limit(limit).all()


@app.get("/users/count", response_model=schemas.CountOut)
def count_users(db: Session = Depends(get_db)):
    """Return the total number of registered users."""
    return {"count": db.query(models.User).count()}


@app.get("/users/{user_id}", response_model=schemas.UserOut)
def read_user(user_id: int, db: Session = Depends(get_db)):
    user = db.query(models.User).get(user_id)
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    return user


@app.put("/users/{user_id}", response_model=schemas.UserOut)
@app.patch("/users/{user_id}", response_model=schemas.UserOut)
def update_user(
    user_id: int,
    user_update: schemas.UserUpdate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    if current_user.id != user_id:
        raise HTTPException(status_code=403, detail="Not authorized to modify this user")
    db_user = db.query(models.User).get(user_id)
    if not db_user:
        raise HTTPException(status_code=404, detail="User not found")

    if user_update.username and user_update.username != db_user.username:
        if db.query(models.User).filter(models.User.username == user_update.username).first():
            raise HTTPException(status_code=400, detail="This username already exists")
        db_user.username = user_update.username

    if user_update.email and user_update.email != db_user.email:
        if db.query(models.User).filter(models.User.email == user_update.email).first():
            raise HTTPException(status_code=400, detail="This email is already use by another account")
        db_user.email = user_update.email

    if user_update.password:
        db_user.pass_hash = get_password_hash(user_update.password)

    if user_update.bio is not None:
        db_user.bio = html.escape(user_update.bio.strip()) if user_update.bio else ""

    if user_update.avatar_url is not None:
        db_user.avatar_url = user_update.avatar_url

    if user_update.is_active is not None:
        db_user.is_active = user_update.is_active

    db.commit()
    db.refresh(db_user)
    return db_user


@app.put("/users/{user_id}/bio", response_model=schemas.UserOut)
@app.patch("/users/{user_id}/bio", response_model=schemas.UserOut)
def update_user_bio(
    user_id: int,
    data: schemas.UserBioUpdate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    if current_user.id != user_id:
        raise HTTPException(status_code=403, detail="Not authorized to modify this user")
    db_user = db.query(models.User).get(user_id)
    if not db_user:
        raise HTTPException(status_code=404, detail="User not found")
    sanitized_bio = html.escape(data.bio.strip()) if data.bio else ""
    db_user.bio = sanitized_bio
    db.commit()
    db.refresh(db_user)
    return db_user


@app.get("/users/{user_id}/profile", response_model=schemas.UserProfileOut)
def read_profile(user_id: int, db: Session = Depends(get_db)):
    profile = db.query(models.UserProfile).filter(models.UserProfile.user_id == user_id).first()
    if not profile:
        raise HTTPException(status_code=404, detail="Profile not found")
    return profile


@app.put("/users/{user_id}/profile", response_model=schemas.UserProfileOut)
def update_profile(user_id: int, data: schemas.UserProfileUpdate, db: Session = Depends(get_db)):
    user = db.query(models.User).get(user_id)
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    profile = db.query(models.UserProfile).filter(models.UserProfile.user_id == user_id).first()
    if profile:
        for key, value in data.dict(exclude_unset=True).items():
            setattr(profile, key, value)
    else:
        profile = models.UserProfile(user_id=user_id, **data.dict())
        db.add(profile)
    db.commit()
    db.refresh(profile)
    return profile


@app.get("/users/{user_id}/threads", response_model=List[schemas.ForumThreadOut])
def list_user_threads(
    user_id: int,
    skip: int = 0,
    limit: int = 10,
    db: Session = Depends(get_db),
):
    """List forum threads created by a specific user."""
    if not db.query(models.User).get(user_id):
        raise HTTPException(status_code=404, detail="User not found")
    return (
        db.query(models.ForumThread)
        .filter(models.ForumThread.user_id == user_id)
        .offset(skip)
        .limit(limit)
        .all()
    )

@app.get("/users/{user_id}/articles", response_model=List[schemas.ArticleOut])
def list_user_articles(
    user_id: int,
    skip: int = 0,
    limit: int = 10,
    db: Session = Depends(get_db),
):
    """List articles created by a specific user."""
    if not db.query(models.User).get(user_id):
        raise HTTPException(status_code=404, detail="User not found")
    return (
        db.query(models.Article)
        .filter(models.Article.user_id == user_id)
        .offset(skip)
        .limit(limit)
        .all()
    )

@app.post("/articles", response_model=schemas.ArticleOut)
def create_article(
    article: schemas.ArticleCreate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    data = article.dict()
    tag_ids = set(data.pop("tag_ids", []) or [])
    tag_names = data.pop("tag_names", []) or []
    db_article = models.Article(**data)
    db.add(db_article)
    db.flush()

    if tag_names:
        existing = db.query(models.Tag).filter(models.Tag.name.in_(tag_names)).all()
        name_to_id = {t.name: t.id for t in existing}
        new_names = [n for n in tag_names if n not in name_to_id]
        new_tags = [models.Tag(name=n) for n in new_names]
        if new_tags:
            db.add_all(new_tags)
            db.flush()
            name_to_id.update({t.name: t.id for t in new_tags})
        tag_ids.update(name_to_id[n] for n in tag_names)

    if tag_ids:
        links = [models.ArticleTag(article_id=db_article.id, tag_id=tid) for tid in tag_ids]
        db.bulk_save_objects(links)

    db.commit()
    db.refresh(db_article)
    return db_article


@app.get("/articles", response_model=List[schemas.ArticleOut])
def read_articles(
    skip: int = 0,
    limit: int = 10,
    tag: Optional[int] = None,
    db: Session = Depends(get_db),
):
    query = db.query(models.Article)
    if tag:
        query = query.join(models.ArticleTag).filter(models.ArticleTag.tag_id == tag)
    return query.offset(skip).limit(limit).all()


@app.get("/articles/count", response_model=schemas.CountOut)
def count_articles(db: Session = Depends(get_db)):
    """Return the total number of articles."""
    return {"count": db.query(models.Article).count()}


@app.post("/tags", response_model=schemas.TagOut)
def create_tag(tag: schemas.TagCreate, db: Session = Depends(get_db)):
    existing = db.query(models.Tag).filter(models.Tag.name == tag.name).first()
    if existing:
        raise HTTPException(status_code=400, detail="Tag already exists")
    db_tag = models.Tag(name=tag.name)
    db.add(db_tag)
    db.commit()
    db.refresh(db_tag)
    return db_tag


@app.get("/tags", response_model=List[schemas.TagOut])
def list_tags(db: Session = Depends(get_db)):
    return db.query(models.Tag).all()


@app.post("/articles/{article_id}/tags", response_model=schemas.ArticleTagOut)
def add_tag_to_article(
    article_id: int,
    article_tag: schemas.ArticleTagCreate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    if not db.query(models.Article).get(article_id):
        raise HTTPException(status_code=404, detail="Article not found")
    if not db.query(models.Tag).get(article_tag.tag_id):
        raise HTTPException(status_code=404, detail="Tag not found")
    existing = (
        db.query(models.ArticleTag)
        .filter(
            models.ArticleTag.article_id == article_id,
            models.ArticleTag.tag_id == article_tag.tag_id,
        )
        .first()
    )
    if existing:
        raise HTTPException(status_code=400, detail="Tag already linked")
    db_link = models.ArticleTag(article_id=article_id, tag_id=article_tag.tag_id)
    db.add(db_link)
    db.commit()
    db.refresh(db_link)
    return db_link


@app.get("/articles/{article_id}", response_model=schemas.ArticleOut)
def read_article(article_id: int, db: Session = Depends(get_db)):
    article = db.query(models.Article).get(article_id)
    if not article:
        raise HTTPException(status_code=404, detail="Article not found")
    return article


@app.put("/articles/{article_id}", response_model=schemas.ArticleOut)
@app.patch("/articles/{article_id}", response_model=schemas.ArticleOut)
def update_article(
    article_id: int,
    article: schemas.ArticleUpdate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    db_article = db.query(models.Article).get(article_id)
    if not db_article:
        raise HTTPException(status_code=404, detail="Article not found")
    data = article.dict(exclude_unset=True)
    tag_ids = data.pop("tag_ids", None)
    tag_names = data.pop("tag_names", None)
    for key, value in data.items():
        setattr(db_article, key, value)

    tags_specified = tag_ids is not None or tag_names is not None
    tags_to_set = set(tag_ids or [])

    if tag_names is not None:
        existing = db.query(models.Tag).filter(models.Tag.name.in_(tag_names)).all()
        name_to_id = {t.name: t.id for t in existing}
        new_names = [n for n in tag_names if n not in name_to_id]
        new_tags = [models.Tag(name=n) for n in new_names]
        if new_tags:
            db.add_all(new_tags)
            db.flush()
            name_to_id.update({t.name: t.id for t in new_tags})
        tags_to_set.update(name_to_id[n] for n in tag_names)

    if tags_specified:
        db.query(models.ArticleTag).filter(models.ArticleTag.article_id == article_id).delete()
        if tags_to_set:
            links = [models.ArticleTag(article_id=article_id, tag_id=t) for t in tags_to_set]
            db.bulk_save_objects(links)

    db.commit()
    db.refresh(db_article)
    return db_article


@app.delete("/articles/{article_id}", status_code=204)
def delete_article(
    article_id: int,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    db_article = db.query(models.Article).get(article_id)
    if not db_article:
        raise HTTPException(status_code=404, detail="Article not found")
    db.delete(db_article)
    db.commit()


@app.post("/articles/{article_id}/comments", response_model=schemas.CommentOut)
def create_comment(
    article_id: int,
    comment: schemas.CommentCreate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    if not db.query(models.Article).get(article_id):
        raise HTTPException(status_code=404, detail="Article not found")
    db_comment = models.Comment(post_id=article_id, **comment.dict(exclude={"post_id"}))
    db.add(db_comment)
    db.commit()
    db.refresh(db_comment)
    return db_comment


@app.get("/articles/{article_id}/comments", response_model=List[schemas.CommentOut])
def list_comments(article_id: int, db: Session = Depends(get_db)):
    return db.query(models.Comment).filter(models.Comment.post_id == article_id, models.Comment.parent_id == None).all()


@app.put("/comments/{comment_id}", response_model=schemas.CommentOut)
@app.patch("/comments/{comment_id}", response_model=schemas.CommentOut)
def update_comment(
    comment_id: int,
    comment: schemas.CommentUpdate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    db_comment = db.query(models.Comment).get(comment_id)
    if not db_comment:
        raise HTTPException(status_code=404, detail="Comment not found")
    for key, value in comment.dict(exclude_unset=True).items():
        setattr(db_comment, key, value)
    db.commit()
    db.refresh(db_comment)
    return db_comment


@app.delete("/comments/{comment_id}", status_code=204)
def delete_comment(
    comment_id: int,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    db_comment = db.query(models.Comment).get(comment_id)
    if not db_comment:
        raise HTTPException(status_code=404, detail="Comment not found")
    db.delete(db_comment)
    db.commit()


@app.post("/forum/categories", response_model=schemas.ForumCategoryOut)
def create_category(
    category: schemas.ForumCategoryCreate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    db_cat = models.ForumCategory(**category.dict())
    db.add(db_cat)
    db.commit()
    db.refresh(db_cat)
    return db_cat


@app.get("/forum/categories", response_model=List[schemas.ForumCategoryOut])
def list_categories(db: Session = Depends(get_db)):
    return db.query(models.ForumCategory).order_by(models.ForumCategory.order_index).all()


@app.post("/forum/threads", response_model=schemas.ForumThreadOut)
def create_thread(
    thread: schemas.ForumThreadCreate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    data = thread.dict()
    db_thread = models.ForumThread(**data)
    db.add(db_thread)
    db.commit()
    db.refresh(db_thread)
    return db_thread


@app.get("/forum/threads", response_model=List[schemas.ForumThreadOut])
def list_threads(skip: int = 0, limit: int = 10, db: Session = Depends(get_db)):
    return db.query(models.ForumThread).offset(skip).limit(limit).all()


@app.get("/forum/threads/{thread_id}", response_model=schemas.ForumThreadOut)
def read_thread(thread_id: int, db: Session = Depends(get_db)):
    thread = db.query(models.ForumThread).get(thread_id)
    if not thread:
        raise HTTPException(status_code=404, detail="Thread not found")
    return thread


@app.put("/forum/threads/{thread_id}", response_model=schemas.ForumThreadOut)
@app.patch("/forum/threads/{thread_id}", response_model=schemas.ForumThreadOut)
def update_thread(
    thread_id: int,
    thread: schemas.ForumThreadUpdate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    db_thread = db.query(models.ForumThread).get(thread_id)
    if not db_thread:
        raise HTTPException(status_code=404, detail="Thread not found")
    data = thread.dict(exclude_unset=True)
    for key, value in data.items():
        setattr(db_thread, key, value)
    db.commit()
    db.refresh(db_thread)
    return db_thread


@app.delete("/forum/threads/{thread_id}", status_code=204)
def delete_thread(
    thread_id: int,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    db_thread = db.query(models.ForumThread).get(thread_id)
    if not db_thread:
        raise HTTPException(status_code=404, detail="Thread not found")
    db.delete(db_thread)
    db.commit()


@app.post("/forum/threads/{thread_id}/replies", response_model=schemas.ForumReplyOut)
def create_reply(
    thread_id: int,
    reply: schemas.ForumReplyCreate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    if not db.query(models.ForumThread).get(thread_id):
        raise HTTPException(status_code=404, detail="Thread not found")
    db_reply = models.ForumReply(thread_id=thread_id, **reply.dict(exclude={"thread_id"}))
    db.add(db_reply)
    db.commit()
    db.refresh(db_reply)
    return db_reply


@app.get("/forum/threads/{thread_id}/replies", response_model=List[schemas.ForumReplyOut])
def list_replies(thread_id: int, db: Session = Depends(get_db)):
    return db.query(models.ForumReply).filter(models.ForumReply.thread_id == thread_id, models.ForumReply.parent_id == None).all()


@app.put("/forum/replies/{reply_id}", response_model=schemas.ForumReplyOut)
@app.patch("/forum/replies/{reply_id}", response_model=schemas.ForumReplyOut)
def update_reply(
    reply_id: int,
    reply: schemas.ForumReplyUpdate,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    db_reply = db.query(models.ForumReply).get(reply_id)
    if not db_reply:
        raise HTTPException(status_code=404, detail="Reply not found")
    for key, value in reply.dict(exclude_unset=True).items():
        setattr(db_reply, key, value)
    db.commit()
    db.refresh(db_reply)
    return db_reply


@app.delete("/forum/replies/{reply_id}", status_code=204)
def delete_reply(
    reply_id: int,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(get_current_user),
):
    db_reply = db.query(models.ForumReply).get(reply_id)
    if not db_reply:
        raise HTTPException(status_code=404, detail="Reply not found")
    db.delete(db_reply)
    db.commit()


@app.get("/reporting/config", response_model=schemas.ReportingConfigOut)
def get_reporting_config(db: Session = Depends(get_db)):
    config = (
        db.query(models.ReportingConfig)
        .order_by(models.ReportingConfig.id.desc())
        .first()
    )
    if not config:
        raise HTTPException(status_code=404, detail="Config not found")
    return config


CERTFR_FEED_URL = "https://www.cert.ssi.gouv.fr/feed/"
_certfr_cache = {"timestamp": datetime.min, "data": []}
_certfr_cache_lock = Lock()
_certfr_cache_ttl = timedelta(minutes=5)


def _fetch_certfr_feed(limit: int = 10):
    """Retrieve and parse the CERT-FR RSS feed."""
    try:
        resp = requests.get(CERTFR_FEED_URL, timeout=10)
        resp.raise_for_status()
    except Exception as exc:
        raise HTTPException(status_code=502, detail=f"Failed to fetch RSS feed: {exc}") from exc
    feed = feedparser.parse(resp.text)
    alerts = [
        schemas.SecurityAlertOut(
            title=entry.get("title", ""),
            link=entry.get("link", ""),
            summary=entry.get("summary"),
            published=entry.get("published"),
        )
        for entry in feed.entries[:limit]
    ]
    return alerts


def _get_certfr_feed_cached(limit: int = 5):
    """Return cached CERT-FR alerts, refreshing if expired."""
    with _certfr_cache_lock:
        now = datetime.utcnow()
        if now - _certfr_cache["timestamp"] > _certfr_cache_ttl:
            _certfr_cache["data"] = _fetch_certfr_feed(limit=20)
            _certfr_cache["timestamp"] = now
        return _certfr_cache["data"][:limit]


@app.get("/security/alerts", response_model=list[schemas.SecurityAlertOut])
def list_security_alerts(limit: int = 5):
    """Return the most recent security alerts from CERT-FR."""
    return _get_certfr_feed_cached(limit)


@app.get("/security/alerts/latest", response_model=schemas.SecurityAlertOut)
def latest_security_alert():
    """Return the latest security alert from CERT-FR."""
    alerts = _get_certfr_feed_cached(1)
    if not alerts:
        raise HTTPException(status_code=404, detail="No alerts found")
    return alerts[0]
