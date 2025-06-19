from sqlalchemy import Column, Integer, String, Text, Boolean, DateTime, ForeignKey, func, UniqueConstraint
from sqlalchemy.orm import relationship

from .database import Base


class User(Base):
    __tablename__ = 'users'

    id = Column(Integer, primary_key=True, index=True)
    username = Column(String(50), unique=True, nullable=False)
    email = Column(String(255), unique=True, nullable=False)
    pass_hash = Column(Text, nullable=False)
    bio = Column(Text)
    avatar_url = Column(Text)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, server_default=func.now())

    articles = relationship('Article', back_populates='author')
    comments = relationship('Comment', back_populates='user')
    threads = relationship('ForumThread', back_populates='user')
    replies = relationship('ForumReply', back_populates='user')


class Article(Base):
    __tablename__ = 'articles'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id'))
    title = Column(String(255), nullable=False)
    content = Column(Text, nullable=False)
    is_pub = Column(Boolean, default=False)
    created_at = Column(DateTime, server_default=func.now())
    updated_at = Column(DateTime, server_default=func.now(), onupdate=func.now())

    author = relationship('User', back_populates='articles')
    comments = relationship('Comment', back_populates='article')


class Comment(Base):
    __tablename__ = 'comments'

    id = Column(Integer, primary_key=True, index=True)
    post_id = Column(Integer, ForeignKey('articles.id'))
    user_id = Column(Integer, ForeignKey('users.id'))
    content = Column(Text, nullable=False)
    parent_id = Column(Integer, ForeignKey('comments.id'))
    created_at = Column(DateTime, server_default=func.now())

    user = relationship('User', back_populates='comments')
    article = relationship('Article', back_populates='comments')
    replies = relationship('Comment', backref='parent', remote_side=[id])


class ForumCategory(Base):
    __tablename__ = 'forum_categories'

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(100), nullable=False)
    description = Column(Text)
    order_index = Column(Integer)

    threads = relationship('ForumThread', back_populates='category')


class ForumThread(Base):
    __tablename__ = 'forum_threads'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id'))
    category_id = Column(Integer, ForeignKey('forum_categories.id'))
    title = Column(String(255), nullable=False)
    content = Column(Text, nullable=False)
    is_locked = Column(Boolean, default=False)
    is_pinned = Column(Boolean, default=False)
    created_at = Column(DateTime, server_default=func.now())
    updated_at = Column(DateTime, server_default=func.now(), onupdate=func.now())

    user = relationship('User', back_populates='threads')
    category = relationship('ForumCategory', back_populates='threads')
    replies = relationship('ForumReply', back_populates='thread')


class ForumReply(Base):
    __tablename__ = 'forum_replies'

    id = Column(Integer, primary_key=True, index=True)
    thread_id = Column(Integer, ForeignKey('forum_threads.id'))
    user_id = Column(Integer, ForeignKey('users.id'))
    content = Column(Text, nullable=False)
    parent_id = Column(Integer, ForeignKey('forum_replies.id'))
    created_at = Column(DateTime, server_default=func.now())
    updated_at = Column(DateTime, server_default=func.now(), onupdate=func.now())

    user = relationship('User', back_populates='replies')
    thread = relationship('ForumThread', back_populates='replies')
    replies = relationship('ForumReply', backref='parent', remote_side=[id])


class Like(Base):
    __tablename__ = 'likes'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id'), nullable=False)
    target_type = Column(String(50), nullable=False)
    target_id = Column(Integer, nullable=False)

    __table_args__ = (
        UniqueConstraint('user_id', 'target_type', 'target_id'),
    )

    user = relationship('User')
