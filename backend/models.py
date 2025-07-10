from sqlalchemy import Column, Integer, String, Text, Boolean, DateTime, Date, ForeignKey, UniqueConstraint, JSON, func
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

    profile = relationship('UserProfile', uselist=False, back_populates='user')
    articles = relationship('Article', back_populates='author')
    comments = relationship('Comment', back_populates='user')
    threads = relationship('ForumThread', back_populates='user')
    replies = relationship('ForumReply', back_populates='user')
    reset_tokens = relationship('PasswordResetToken', back_populates='user')
    messages_sent = relationship('Message', foreign_keys='Message.sender_id', back_populates='sender')
    messages_received = relationship('Message', foreign_keys='Message.receiver_id', back_populates='receiver')
    sessions = relationship('Session', back_populates='user')


class UserProfile(Base):
    __tablename__ = 'user_profiles'

    user_id = Column(Integer, ForeignKey('users.id', ondelete='CASCADE'), primary_key=True)
    display_name = Column(String(100))
    location = Column(String(100))
    website = Column(Text)
    birth_date = Column(Date)
    gender = Column(String(20))
    joined_at = Column(DateTime, server_default=func.now())

    user = relationship('User', back_populates='profile')


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
    name = Column(String(100), unique=True, nullable=False)
    description = Column(Text)

    role_permissions = relationship('RolePermission', back_populates='permission')


class UserRole(Base):
    __tablename__ = 'user_roles'
    user_id = Column(Integer, ForeignKey('users.id', ondelete='CASCADE'), primary_key=True)
    role_id = Column(Integer, ForeignKey('roles.id', ondelete='CASCADE'), primary_key=True)

    user = relationship('User')
    role = relationship('Role', back_populates='user_roles')


class RolePermission(Base):
    __tablename__ = 'role_permissions'
    role_id = Column(Integer, ForeignKey('roles.id', ondelete='CASCADE'), primary_key=True)
    permission_id = Column(Integer, ForeignKey('permissions.id', ondelete='CASCADE'), primary_key=True)

    role = relationship('Role', back_populates='role_permissions')
    permission = relationship('Permission', back_populates='role_permissions')


class Admin(Base):
    __tablename__ = 'admin'

    id = Column(Integer, primary_key=True, index=True)
    username = Column(String(50), unique=True, nullable=False)
    pass_hash = Column(Text, nullable=False)
    bio = Column(Text)
    avatar_url = Column(Text)


class Article(Base):
    __tablename__ = 'articles'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id', ondelete='SET NULL'))
    title = Column(String(255), nullable=False)
    content = Column(Text, nullable=False)
    image_url = Column(Text)
    is_pub = Column(Boolean, default=False)
    created_at = Column(DateTime, server_default=func.now())
    updated_at = Column(DateTime, server_default=func.now(), onupdate=func.now())

    author = relationship('User', back_populates='articles')
    comments = relationship(
        'Comment',
        back_populates='article',
        cascade='all, delete-orphan',
        passive_deletes=True,
    )
    article_tags = relationship(
        'ArticleTag',
        back_populates='article',
        cascade='all, delete-orphan',
        passive_deletes=True,
    )

    @property
    def tags(self):
        return [at.tag_id for at in self.article_tags]


class Tag(Base):
    __tablename__ = 'tags'

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(50), unique=True, nullable=False)

    article_tags = relationship('ArticleTag', back_populates='tag')


class ArticleTag(Base):
    __tablename__ = 'article_tags'
    article_id = Column(Integer, ForeignKey('articles.id', ondelete='CASCADE'), primary_key=True)
    tag_id = Column(Integer, ForeignKey('tags.id', ondelete='CASCADE'), primary_key=True)

    article = relationship('Article', back_populates='article_tags')
    tag = relationship('Tag', back_populates='article_tags')



class Comment(Base):
    __tablename__ = 'comments'

    id = Column(Integer, primary_key=True, index=True)
    post_id = Column(Integer, ForeignKey('articles.id', ondelete='CASCADE'))
    user_id = Column(Integer, ForeignKey('users.id', ondelete='SET NULL'))
    content = Column(Text, nullable=False)
    parent_id = Column(Integer, ForeignKey('comments.id', ondelete='CASCADE'))
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
    user_id = Column(Integer, ForeignKey('users.id', ondelete='SET NULL'))
    category_id = Column(Integer, ForeignKey('forum_categories.id', ondelete='SET NULL'))
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
    thread_id = Column(Integer, ForeignKey('forum_threads.id', ondelete='CASCADE'))
    user_id = Column(Integer, ForeignKey('users.id', ondelete='SET NULL'))
    content = Column(Text, nullable=False)
    parent_id = Column(Integer, ForeignKey('forum_replies.id', ondelete='CASCADE'))
    created_at = Column(DateTime, server_default=func.now())
    updated_at = Column(DateTime, server_default=func.now(), onupdate=func.now())

    user = relationship('User', back_populates='replies')
    thread = relationship('ForumThread', back_populates='replies')
    replies = relationship('ForumReply', backref='parent', remote_side=[id])


class Like(Base):
    __tablename__ = 'likes'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    target_type = Column(String(50), nullable=False)
    target_id = Column(Integer, nullable=False)

    __table_args__ = (
        UniqueConstraint('user_id', 'target_type', 'target_id'),
    )

    user = relationship('User')


class Message(Base):
    __tablename__ = 'messages'

    id = Column(Integer, primary_key=True, index=True)
    sender_id = Column(Integer, ForeignKey('users.id', ondelete='CASCADE'))
    receiver_id = Column(Integer, ForeignKey('users.id', ondelete='CASCADE'))
    content = Column(Text, nullable=False)
    is_read = Column(Boolean, default=False)
    created_at = Column(DateTime, server_default=func.now())

    sender = relationship('User', foreign_keys=[sender_id])
    receiver = relationship('User', foreign_keys=[receiver_id])


class Report(Base):
    __tablename__ = 'reports'

    id = Column(Integer, primary_key=True, index=True)
    reporter_id = Column(Integer, ForeignKey('users.id', ondelete='SET NULL'))
    target_type = Column(String(50), nullable=False)
    target_id = Column(Integer, nullable=False)
    reason = Column(Text, nullable=False)
    status = Column(String(20), default='en attente')
    created_at = Column(DateTime, server_default=func.now())

    reporter = relationship('User')


class Notification(Base):
    __tablename__ = 'notifications'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id', ondelete='CASCADE'))
    type = Column(String(50))
    message = Column(Text)
    is_read = Column(Boolean, default=False)
    created_at = Column(DateTime, server_default=func.now())

    user = relationship('User')


class Ban(Base):
    __tablename__ = 'bans'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id', ondelete='CASCADE'))
    reason = Column(Text, nullable=False)
    banned_by = Column(Integer, ForeignKey('admin.id'))
    expires_at = Column(DateTime)
    created_at = Column(DateTime, server_default=func.now())

    user = relationship('User')


class PasswordResetToken(Base):
    __tablename__ = 'password_reset_tokens'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id', ondelete='CASCADE'))
    token = Column(String(255), unique=True, nullable=False)
    expires_at = Column(DateTime, nullable=False)
    used = Column(Boolean, default=False)
    created_at = Column(DateTime, server_default=func.now())

    user = relationship('User', back_populates='reset_tokens')


class Attachment(Base):
    __tablename__ = 'attachments'

    id = Column(Integer, primary_key=True, index=True)
    uploaded_by = Column(Integer, ForeignKey('users.id', ondelete='SET NULL'))
    file_url = Column(Text, nullable=False)
    file_type = Column(String(100))
    file_size = Column(Integer)
    attached_to_type = Column(String(50))
    attached_to_id = Column(Integer)
    uploaded_at = Column(DateTime, server_default=func.now())


class Session(Base):
    __tablename__ = 'sessions'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id', ondelete='CASCADE'))
    session_token = Column(String(255), unique=True, nullable=False)
    ip_address = Column(String(45))
    user_agent = Column(Text)
    created_at = Column(DateTime, server_default=func.now())
    expires_at = Column(DateTime)

    user = relationship('User', back_populates='sessions')


class AuditLog(Base):
    __tablename__ = 'audit_logs'

    id = Column(Integer, primary_key=True, index=True)
    actor_id = Column(Integer, ForeignKey('users.id'))
    action_type = Column(String(100))
    target_type = Column(String(50))
    target_id = Column(Integer)
    description = Column(Text)
    created_at = Column(DateTime, server_default=func.now())

    actor = relationship('User')


class ActivityLog(Base):
    __tablename__ = 'activity_logs'

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey('users.id', ondelete='SET NULL'))
    action = Column(String(100))
    metadata_json = Column('metadata', JSON)
    created_at = Column(DateTime, server_default=func.now())

    user = relationship('User')


class Setting(Base):
    __tablename__ = 'settings'

    key = Column(String(100), primary_key=True)
    value = Column(Text)
    updated_at = Column(DateTime, server_default=func.now(), onupdate=func.now())
