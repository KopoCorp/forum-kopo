from typing import List
from fastapi import FastAPI, Depends, HTTPException
from sqlalchemy.orm import Session

from . import models, schemas
from .database import Base, engine, get_db

# Create database tables if they don't exist
Base.metadata.create_all(bind=engine)

app = FastAPI(title="Kopo Forum API")


@app.post("/users", response_model=schemas.UserOut)
def create_user(user: schemas.UserCreate, db: Session = Depends(get_db)):
    db_user = db.query(models.User).filter(models.User.username == user.username).first()
    if db_user:
        raise HTTPException(status_code=400, detail="Username already registered")
    db_user = models.User(**user.dict())
    db.add(db_user)
    db.commit()
    db.refresh(db_user)
    return db_user


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


@app.post("/reports", response_model=schemas.ReportOut)
def create_report(report: schemas.ReportCreate, db: Session = Depends(get_db)):
    db_report = models.Report(**report.dict())
    db.add(db_report)
    db.commit()
    db.refresh(db_report)
    return db_report


@app.get("/reports", response_model=List[schemas.ReportOut])
def list_reports(db: Session = Depends(get_db)):
    return db.query(models.Report).all()


@app.patch("/reports/{report_id}/resolve", response_model=schemas.ReportOut)
def resolve_report(report_id: int, db: Session = Depends(get_db)):
    report = db.query(models.Report).get(report_id)
    if not report:
        raise HTTPException(status_code=404, detail="Report not found")
    report.is_resolved = True
    log = models.AuditLog(action="resolve_report", target_type="report", target_id=report_id)
    db.add(log)
    db.commit()
    db.refresh(report)
    return report


@app.post("/bans", response_model=schemas.BanOut)
def create_ban(ban: schemas.BanCreate, db: Session = Depends(get_db)):
    user = db.query(models.User).get(ban.user_id)
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    user.is_active = False
    db_ban = models.Ban(**ban.dict())
    db.add(db_ban)
    db.add(models.AuditLog(action="ban_user", target_type="user", target_id=ban.user_id))
    db.commit()
    db.refresh(db_ban)
    return db_ban


@app.get("/bans", response_model=List[schemas.BanOut])
def list_bans(db: Session = Depends(get_db)):
    return db.query(models.Ban).filter(models.Ban.is_active == True).all()


@app.delete("/bans/{ban_id}", response_model=schemas.BanOut)
def lift_ban(ban_id: int, db: Session = Depends(get_db)):
    ban = db.query(models.Ban).get(ban_id)
    if not ban:
        raise HTTPException(status_code=404, detail="Ban not found")
    user = db.query(models.User).get(ban.user_id)
    if user:
        user.is_active = True
    ban.is_active = False
    db.add(models.AuditLog(action="unban_user", target_type="user", target_id=ban.user_id))
    db.commit()
    db.refresh(ban)
    return ban
