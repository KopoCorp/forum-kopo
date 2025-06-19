from sqlalchemy import Column, Integer, String, Text, Boolean, DateTime, ForeignKey, func, UniqueConstraint
from sqlalchemy.orm import relationship

from .database import Base


class User(Base):
    __tablename__ = 'users'

    id = Column(Integer, primary_key=True, index=True)
    username = Column(String(50), unique=True, nullable=False)
    email = Column(String(255), unique=True, nullable=False)
    password_hash = Column(Text, nullable=False)
    bio = Column(Text)
    avatar_url = Column(Text)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, server_default=func.now())

    articles = relationship('Article', back_populates='author')
    comments = relationship('Comment', back_populates='user')
    threads = relationship('ForumThread', back_populates='user')
    replies = relationship('ForumReply', back_populates='user')
    profile = relationship('UserProfile', uselist=False, back_populates='user')
    reset_tokens = relationship('PasswordResetToken', back_populates='user')


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
    article_tags = relationship('ArticleTag', back_populates='article')


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


class Report(Base):
    __tablename__ = 'reports'

    id = Column(Integer, primary_key=True, index=True)
    reporter_id = Column(Integer, ForeignKey('users.id'))
    target_type = Column(String(50), nullable=False)
    target_id = Column(Integer, nullable=False)
    reason = Column(Text, nullable=False)
    is_resolved = Column(Boolean, default=False)
    created_at = Column(DateTime, server_default=func.now())

    reporter = relationship('User')


class Ban(Base):
    __tablename__ = 'bans'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id'), nullable=False)
    reason = Column(Text, nullable=False)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, server_default=func.now())

    user = relationship('User')


class AuditLog(Base):
    __tablename__ = 'audit_logs'

    id = Column(Integer, primary_key=True, index=True)
    action = Column(String(100), nullable=False)
    performed_by = Column(Integer, ForeignKey('users.id'))
    target_type = Column(String(50))
    target_id = Column(Integer)
    details = Column(Text)
    created_at = Column(DateTime, server_default=func.now())

    user = relationship('User')
    
class Tag(Base):
    __tablename__ = 'tags'

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(50), unique=True, nullable=False)

    article_tags = relationship('ArticleTag', back_populates='tag')


class ArticleTag(Base):
    __tablename__ = 'article_tags'

    id = Column(Integer, primary_key=True, index=True)
    article_id = Column(Integer, ForeignKey('articles.id'))
    tag_id = Column(Integer, ForeignKey('tags.id'))

    __table_args__ = (UniqueConstraint('article_id', 'tag_id'),)

    article = relationship('Article', back_populates='article_tags')
    tag = relationship('Tag', back_populates='article_tags')
    
class UserProfile(Base):
    __tablename__ = 'user_profiles'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id'), unique=True)
    full_name = Column(String(100))
    website = Column(String(255))
    location = Column(String(100))
    about_me = Column(Text)

    user = relationship('User', back_populates='profile')


class PasswordResetToken(Base):
    __tablename__ = 'password_reset_tokens'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id'), nullable=False)
    token = Column(String(100), unique=True, nullable=False, index=True)
    is_used = Column(Boolean, default=False)
    created_at = Column(DateTime, server_default=func.now())

    user = relationship('User', back_populates='reset_tokens')
    
class DirectMessage(Base):
    __tablename__ = 'direct_messages'

    id = Column(Integer, primary_key=True, index=True)
    sender_id = Column(Integer, ForeignKey('users.id'), nullable=False)
    receiver_id = Column(Integer, ForeignKey('users.id'), nullable=False)
    content = Column(Text, nullable=False)
    is_read = Column(Boolean, default=False)
    created_at = Column(DateTime, server_default=func.now())

    sender = relationship('User', foreign_keys=[sender_id])
    receiver = relationship('User', foreign_keys=[receiver_id])

class Notification(Base):
    __tablename__ = 'notifications'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id'), nullable=False)
    message = Column(Text, nullable=False)
    is_read = Column(Boolean, default=False)
    created_at = Column(DateTime, server_default=func.now())
    user = relationship('User')
    
class Attachment(Base):
    __tablename__ = 'attachments'
    
    id = Column(Integer, primary_key=True, index=True)
    filename = Column(String(255), nullable=False)
    path = Column(String(255), nullable=False)
    content_type = Column(String(100))
    created_at = Column(DateTime, server_default=func.now())


class Role(Base):
    __tablename__ = 'roles'

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(50), unique=True, nullable=False)
    description = Column(Text)
    user_roles = relationship('UserRole', back_populates='role')
    role_permissions = relationship('RolePermission', back_populates='role')


class Permission(Base):
    __tablename__ = 'permissions'

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(50), unique=True, nullable=False)
    description = Column(Text)
    role_permissions = relationship('RolePermission', back_populates='permission')


class UserRole(Base):
    __tablename__ = 'user_roles'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id'))
    role_id = Column(Integer, ForeignKey('roles.id'))

    __table_args__ = (UniqueConstraint('user_id', 'role_id'),)

    user = relationship('User')
    role = relationship('Role', back_populates='user_roles')


class RolePermission(Base):
    __tablename__ = 'role_permissions'

    id = Column(Integer, primary_key=True, index=True)
    role_id = Column(Integer, ForeignKey('roles.id'))
    permission_id = Column(Integer, ForeignKey('permissions.id'))

    __table_args__ = (UniqueConstraint('role_id', 'permission_id'),)

    role = relationship('Role', back_populates='role_permissions')
    permission = relationship('Permission', back_populates='role_permissions')
    