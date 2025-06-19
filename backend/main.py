from typing import List, Optional
from datetime import datetime, timedelta
from fastapi import FastAPI, Depends, HTTPException
from fastapi.security import OAuth2PasswordBearer, OAuth2PasswordRequestForm
from jose import JWTError, jwt
from passlib.context import CryptContext
from sqlalchemy.orm import Session

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
    if db_user:
        raise HTTPException(status_code=400, detail="Username already registered")
    db_user = models.User(
        username=user.username,
        email=user.email,
        bio=user.bio,
        avatar_url=user.avatar_url,
        password_hash=get_password_hash(user.password),
    )
    db.add(db_user)
    db.commit()
    db.refresh(db_user)
    return db_user


@app.post("/login")
def login(form_data: OAuth2PasswordRequestForm = Depends(), db: Session = Depends(get_db)):
    user = db.query(models.User).filter(models.User.username == form_data.username).first()
    if not user or not verify_password(form_data.password, user.password_hash):
        raise HTTPException(status_code=401, detail="Invalid credentials")
    access_token_expires = timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    token = create_access_token(data={"sub": str(user.id)}, expires_delta=access_token_expires)
    return {"access_token": token, "token_type": "bearer"}


@app.post("/logout")
def logout(token: str = Depends(oauth2_scheme)):
    revoked_tokens.add(token)
    return {"detail": "Logged out"}


@app.get("/users/{user_id}", response_model=schemas.UserOut)
def read_user(user_id: int, db: Session = Depends(get_db)):
    user = db.query(models.User).get(user_id)
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    return user


@app.post("/articles", response_model=schemas.ArticleOut)
def create_article(article: schemas.ArticleCreate, db: Session = Depends(get_db)):
    db_article = models.Article(**article.dict())
    db.add(db_article)
    db.commit()
    db.refresh(db_article)
    return db_article


@app.get("/articles", response_model=List[schemas.ArticleOut])
def read_articles(skip: int = 0, limit: int = 10, db: Session = Depends(get_db)):
    return db.query(models.Article).offset(skip).limit(limit).all()


@app.get("/articles/{article_id}", response_model=schemas.ArticleOut)
def read_article(article_id: int, db: Session = Depends(get_db)):
    article = db.query(models.Article).get(article_id)
    if not article:
        raise HTTPException(status_code=404, detail="Article not found")
    return article


@app.post("/articles/{article_id}/comments", response_model=schemas.CommentOut)
def create_comment(article_id: int, comment: schemas.CommentCreate, db: Session = Depends(get_db)):
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


@app.post("/forum/categories", response_model=schemas.ForumCategoryOut)
def create_category(category: schemas.ForumCategoryCreate, db: Session = Depends(get_db)):
    db_cat = models.ForumCategory(**category.dict())
    db.add(db_cat)
    db.commit()
    db.refresh(db_cat)
    return db_cat


@app.get("/forum/categories", response_model=List[schemas.ForumCategoryOut])
def list_categories(db: Session = Depends(get_db)):
    return db.query(models.ForumCategory).order_by(models.ForumCategory.order_index).all()


@app.post("/forum/threads", response_model=schemas.ForumThreadOut)
def create_thread(thread: schemas.ForumThreadCreate, db: Session = Depends(get_db)):
    db_thread = models.ForumThread(**thread.dict())
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


@app.post("/forum/threads/{thread_id}/replies", response_model=schemas.ForumReplyOut)
def create_reply(thread_id: int, reply: schemas.ForumReplyCreate, db: Session = Depends(get_db)):
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


@app.post("/likes", response_model=schemas.LikeOut)
def create_like(like: schemas.LikeCreate, db: Session = Depends(get_db)):
    db_like = models.Like(**like.dict())
    db.add(db_like)
    try:
        db.commit()
    except Exception:
        db.rollback()
        raise HTTPException(status_code=400, detail="Like already exists")
    db.refresh(db_like)
    return db_like
