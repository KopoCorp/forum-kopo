from typing import List, Optional
from datetime import datetime, timedelta
from uuid import uuid4

from fastapi import FastAPI, Depends, HTTPException, UploadFile, File
from fastapi.responses import FileResponse
from fastapi.security import OAuth2PasswordBearer, OAuth2PasswordRequestForm

from jose import JWTError, jwt
from passlib.context import CryptContext

from sqlalchemy.orm import Session
import os
import uuid
import shutil

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


@app.post("/articles", response_model=schemas.ArticleOut)
def create_article(article: schemas.ArticleCreate, db: Session = Depends(get_db)):
    db_article = models.Article(**article.dict())
    db.add(db_article)
    db.commit()
    db.refresh(db_article)
    return db_article


@app.get("/articles", response_model=List[schemas.ArticleOut])
def read_articles(skip: int = 0, limit: int = 10, tag: Optional[int] = None, db: Session = Depends(get_db)):
    query = db.query(models.Article)
    if tag is not None:
        query = query.join(models.ArticleTag).filter(models.ArticleTag.tag_id == tag)
    return query.offset(skip).limit(limit).all()


@app.get("/articles/{article_id}", response_model=schemas.ArticleOut)
def read_article(article_id: int, db: Session = Depends(get_db)):
    article = db.query(models.Article).get(article_id)
    if not article:
        raise HTTPException(status_code=404, detail="Article not found")
    return article


@app.put("/articles/{article_id}", response_model=schemas.ArticleOut)
@app.patch("/articles/{article_id}", response_model=schemas.ArticleOut)
def update_article(article_id: int, article: schemas.ArticleUpdate, db: Session = Depends(get_db)):
    db_article = db.query(models.Article).get(article_id)
    if not db_article:
        raise HTTPException(status_code=404, detail="Article not found")
    for key, value in article.dict(exclude_unset=True).items():
        setattr(db_article, key, value)
    db.commit()
    db.refresh(db_article)
    return db_article


@app.delete("/articles/{article_id}", status_code=204)
def delete_article(article_id: int, db: Session = Depends(get_db)):
    db_article = db.query(models.Article).get(article_id)
    if not db_article:
        raise HTTPException(status_code=404, detail="Article not found")
    db.delete(db_article)
    db.commit()


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


@app.put("/comments/{comment_id}", response_model=schemas.CommentOut)
@app.patch("/comments/{comment_id}", response_model=schemas.CommentOut)
def update_comment(comment_id: int, comment: schemas.CommentUpdate, db: Session = Depends(get_db)):
    db_comment = db.query(models.Comment).get(comment_id)
    if not db_comment:
        raise HTTPException(status_code=404, detail="Comment not found")
    for key, value in comment.dict(exclude_unset=True).items():
        setattr(db_comment, key, value)
    db.commit()
    db.refresh(db_comment)
    return db_comment


@app.delete("/comments/{comment_id}", status_code=204)
def delete_comment(comment_id: int, db: Session = Depends(get_db)):
    db_comment = db.query(models.Comment).get(comment_id)
    if not db_comment:
        raise HTTPException(status_code=404, detail="Comment not found")
    db.delete(db_comment)
    db.commit()


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


@app.put("/forum/threads/{thread_id}", response_model=schemas.ForumThreadOut)
@app.patch("/forum/threads/{thread_id}", response_model=schemas.ForumThreadOut)
def update_thread(thread_id: int, thread: schemas.ForumThreadUpdate, db: Session = Depends(get_db)):
    db_thread = db.query(models.ForumThread).get(thread_id)
    if not db_thread:
        raise HTTPException(status_code=404, detail="Thread not found")
    for key, value in thread.dict(exclude_unset=True).items():
        setattr(db_thread, key, value)
    db.commit()
    db.refresh(db_thread)
    return db_thread


@app.delete("/forum/threads/{thread_id}", status_code=204)
def delete_thread(thread_id: int, db: Session = Depends(get_db)):
    db_thread = db.query(models.ForumThread).get(thread_id)
    if not db_thread:
        raise HTTPException(status_code=404, detail="Thread not found")
    db.delete(db_thread)
    db.commit()


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


@app.put("/forum/replies/{reply_id}", response_model=schemas.ForumReplyOut)
@app.patch("/forum/replies/{reply_id}", response_model=schemas.ForumReplyOut)
def update_reply(reply_id: int, reply: schemas.ForumReplyUpdate, db: Session = Depends(get_db)):
    db_reply = db.query(models.ForumReply).get(reply_id)
    if not db_reply:
        raise HTTPException(status_code=404, detail="Reply not found")
    for key, value in reply.dict(exclude_unset=True).items():
        setattr(db_reply, key, value)
    db.commit()
    db.refresh(db_reply)
    return db_reply


@app.delete("/forum/replies/{reply_id}", status_code=204)
def delete_reply(reply_id: int, db: Session = Depends(get_db)):
    db_reply = db.query(models.ForumReply).get(reply_id)
    if not db_reply:
        raise HTTPException(status_code=404, detail="Reply not found")
    db.delete(db_reply)
    db.commit()


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


@app.post("/tags", response_model=schemas.TagOut)
def create_tag(tag: schemas.TagCreate, db: Session = Depends(get_db)):
    db_tag = models.Tag(name=tag.name)
    db.add(db_tag)
    try:
        db.commit()
    except Exception:
        db.rollback()
        raise HTTPException(status_code=400, detail="Tag already exists")
    db.refresh(db_tag)
    return db_tag


@app.get("/tags", response_model=List[schemas.TagOut])
def list_tags(db: Session = Depends(get_db)):
    return db.query(models.Tag).all()


@app.post("/articles/{article_id}/tags", response_model=schemas.ArticleTagOut)
def assign_tag(article_id: int, data: schemas.ArticleTagCreate, db: Session = Depends(get_db)):
    if not db.query(models.Article).get(article_id):
        raise HTTPException(status_code=404, detail="Article not found")
    if not db.query(models.Tag).get(data.tag_id):
        raise HTTPException(status_code=404, detail="Tag not found")
    db_at = models.ArticleTag(article_id=article_id, tag_id=data.tag_id)
    db.add(db_at)
    try:
        db.commit()
    except Exception:
        db.rollback()
        raise HTTPException(status_code=400, detail="Tag already assigned")
    db.refresh(db_at)
    return db_at

@app.post("/password-reset/request")
def request_password_reset(data: schemas.PasswordResetRequest, db: Session = Depends(get_db)):
    user = db.query(models.User).filter(models.User.email == data.email).first()
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    token_str = uuid4().hex
    token = models.PasswordResetToken(user_id=user.id, token=token_str)
    db.add(token)
    db.commit()
    db.refresh(token)
    # In real app, send token via email. Here we return it for simplicity.
    return {"token": token_str}


@app.post("/password-reset/confirm")
def confirm_password_reset(data: schemas.PasswordResetConfirm, db: Session = Depends(get_db)):
    token = db.query(models.PasswordResetToken).filter(models.PasswordResetToken.token == data.token, models.PasswordResetToken.is_used == False).first()
    if not token:
        raise HTTPException(status_code=404, detail="Invalid token")
    user = token.user
    user.pass_hash = data.new_pass_hash
    token.is_used = True
    db.commit()
    return {"status": "password updated"}
  
@app.post("/messages", response_model=schemas.DirectMessageOut)
def send_message(message: schemas.DirectMessageCreate, db: Session = Depends(get_db)):
    db_msg = models.DirectMessage(**message.dict())
    db.add(db_msg)
    db.commit()
    db.refresh(db_msg)
    return db_msg


@app.get("/messages/{user_id}", response_model=List[schemas.DirectMessageOut])
def read_messages(user_id: int, db: Session = Depends(get_db)):
    msgs = db.query(models.DirectMessage).filter(models.DirectMessage.receiver_id == user_id).all()
    for msg in msgs:
        if not msg.is_read:
            msg.is_read = True
    db.commit()
    return msgs


@app.post("/notifications", response_model=schemas.NotificationOut)
def create_notification(notification: schemas.NotificationCreate, db: Session = Depends(get_db)):
    db_notif = models.Notification(**notification.dict())
    db.add(db_notif)
    db.commit()
    db.refresh(db_notif)
    return db_notif


@app.get("/notifications/{user_id}", response_model=List[schemas.NotificationOut])
def list_notifications(user_id: int, db: Session = Depends(get_db)):
    return db.query(models.Notification).filter(models.Notification.user_id == user_id).all()


@app.post("/notifications/{notif_id}/read", response_model=schemas.NotificationOut)
def mark_notification_read(notif_id: int, db: Session = Depends(get_db)):
    notif = db.query(models.Notification).get(notif_id)
    if not notif:
        raise HTTPException(status_code=404, detail="Notification not found")
    notif.is_read = True
    db.commit()
    db.refresh(notif)
    return notif

@app.post("/attachments", response_model=schemas.AttachmentOut)
async def upload_attachment(file: UploadFile = File(...), db: Session = Depends(get_db)):
    upload_dir = "uploads"
    os.makedirs(upload_dir, exist_ok=True)
    ext = os.path.splitext(file.filename)[1]
    unique_name = f"{uuid.uuid4()}{ext}"
    file_path = os.path.join(upload_dir, unique_name)
    with open(file_path, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)

    db_obj = models.Attachment(filename=file.filename, path=file_path, content_type=file.content_type)
    db.add(db_obj)
    db.commit()
    db.refresh(db_obj)
    return db_obj


@app.get("/attachments/{attachment_id}")
def get_attachment(attachment_id: int, db: Session = Depends(get_db)):
    att = db.query(models.Attachment).get(attachment_id)
    if not att:
        raise HTTPException(status_code=404, detail="Attachment not found")
    return FileResponse(att.path, media_type=att.content_type, filename=att.filename)


@app.delete("/attachments/{attachment_id}")
def delete_attachment(attachment_id: int, db: Session = Depends(get_db)):
    att = db.query(models.Attachment).get(attachment_id)
    if not att:
        raise HTTPException(status_code=404, detail="Attachment not found")
    try:
        os.remove(att.path)
    except FileNotFoundError:
        pass
    db.delete(att)
    db.commit()
    return {"detail": "Attachment deleted"}
