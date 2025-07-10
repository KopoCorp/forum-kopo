from datetime import datetime, date
from typing import Optional
from pydantic import BaseModel


class UserBase(BaseModel):
    username: str
    email: str
    bio: Optional[str] = None
    avatar_url: Optional[str] = None


class UserCreate(UserBase):
    password: str


class UserOut(UserBase):
    id: int
    is_active: bool
    created_at: datetime

    class Config:
        orm_mode = True


class UserUpdate(BaseModel):
    username: Optional[str] = None
    email: Optional[str] = None
    password: Optional[str] = None
    bio: Optional[str] = None
    avatar_url: Optional[str] = None
    is_active: Optional[bool] = None


class UserBioUpdate(BaseModel):
    bio: str


class ArticleBase(BaseModel):
    title: str
    content: str
    is_pub: Optional[bool] = False


class ArticleCreate(ArticleBase):
    user_id: Optional[int]


class ArticleUpdate(BaseModel):
    title: Optional[str] = None
    content: Optional[str] = None
    is_pub: Optional[bool] = None
    user_id: Optional[int] = None


class ArticleOut(ArticleBase):
    id: int
    user_id: Optional[int]
    created_at: datetime
    updated_at: datetime

    class Config:
        orm_mode = True


class CountOut(BaseModel):
    """Simple count response schema."""
    count: int


class CommentBase(BaseModel):
    content: str
    parent_id: Optional[int] = None


class CommentCreate(CommentBase):
    post_id: int
    user_id: Optional[int]


class CommentUpdate(BaseModel):
    content: Optional[str] = None
    parent_id: Optional[int] = None
    post_id: Optional[int] = None
    user_id: Optional[int] = None


class CommentOut(CommentBase):
    id: int
    post_id: int
    user_id: Optional[int]
    created_at: datetime

    class Config:
        orm_mode = True


class ForumCategoryBase(BaseModel):
    name: str
    description: Optional[str] = None
    order_index: Optional[int] = None


class ForumCategoryCreate(ForumCategoryBase):
    pass


class ForumCategoryOut(ForumCategoryBase):
    id: int

    class Config:
        orm_mode = True


class ForumThreadBase(BaseModel):
    title: str
    content: str
    category_id: Optional[int]


class ForumThreadCreate(ForumThreadBase):
    user_id: Optional[int]


class ForumThreadUpdate(BaseModel):
    title: Optional[str] = None
    content: Optional[str] = None
    category_id: Optional[int] = None
    user_id: Optional[int] = None
    is_locked: Optional[bool] = None
    is_pinned: Optional[bool] = None


class ForumThreadOut(ForumThreadBase):
    id: int
    user_id: Optional[int]
    is_locked: bool
    is_pinned: bool
    created_at: datetime
    updated_at: datetime

    class Config:
        orm_mode = True


class ForumReplyBase(BaseModel):
    content: str
    parent_id: Optional[int] = None


class ForumReplyCreate(ForumReplyBase):
    thread_id: int
    user_id: Optional[int]


class ForumReplyUpdate(BaseModel):
    content: Optional[str] = None
    parent_id: Optional[int] = None
    thread_id: Optional[int] = None
    user_id: Optional[int] = None


class ForumReplyOut(ForumReplyBase):
    id: int
    thread_id: int
    user_id: Optional[int]
    created_at: datetime
    updated_at: datetime

    class Config:
        orm_mode = True


class LikeBase(BaseModel):
    target_type: str
    target_id: int


class LikeCreate(LikeBase):
    user_id: Optional[int]


class LikeOut(LikeBase):
    id: int
    user_id: Optional[int]

    class Config:
        orm_mode = True


class ReportBase(BaseModel):
    target_type: str
    target_id: int
    reason: str


class ReportCreate(ReportBase):
    reporter_id: Optional[int]


class ReportOut(ReportBase):
    id: int
    reporter_id: Optional[int]
    status: str
    created_at: datetime

    class Config:
        orm_mode = True


class BanBase(BaseModel):
    user_id: int
    reason: str
    banned_by: Optional[int] = None
    expires_at: Optional[datetime] = None


class BanCreate(BanBase):
    pass


class BanOut(BanBase):
    id: int
    created_at: datetime


class TagBase(BaseModel):
    name: str


class TagCreate(TagBase):
    pass


class TagOut(TagBase):
    id: int


class UserProfileBase(BaseModel):
    display_name: Optional[str] = None
    location: Optional[str] = None
    website: Optional[str] = None
    birth_date: Optional[date] = None
    gender: Optional[str] = None


class UserProfileUpdate(UserProfileBase):
    pass


class UserProfileOut(UserProfileBase):
    user_id: int
    joined_at: datetime


class MessageBase(BaseModel):
    content: str


class MessageCreate(MessageBase):
    sender_id: Optional[int]
    receiver_id: int


class MessageOut(MessageBase):
    id: int
    sender_id: Optional[int]
    receiver_id: int
    is_read: bool
    created_at: datetime

    class Config:
        orm_mode = True


class AuditLogOut(BaseModel):
    id: int
    actor_id: Optional[int]
    action_type: Optional[str] = None
    target_type: Optional[str] = None
    target_id: Optional[int] = None
    description: Optional[str] = None
    created_at: datetime


class ArticleTagCreate(BaseModel):
    tag_id: int


class ArticleTagOut(ArticleTagCreate):
    article_id: int


class PasswordResetRequest(BaseModel):
    email: str


class PasswordResetConfirm(BaseModel):
    token: str
    new_pass_hash: str


class NotificationBase(BaseModel):
    type: Optional[str] = None
    message: Optional[str] = None


class NotificationCreate(NotificationBase):
    user_id: Optional[int]


class NotificationOut(NotificationBase):
    id: int
    user_id: Optional[int]
    is_read: bool


class AttachmentOut(BaseModel):
    id: int
    file_url: str
    file_type: Optional[str] = None
    file_size: Optional[int] = None
    uploaded_at: datetime

    class Config:
        orm_mode = True


class RoleBase(BaseModel):
    name: str
    description: Optional[str] = None


class RoleCreate(RoleBase):
    pass


class RoleOut(RoleBase):
    id: int

    class Config:
        orm_mode = True


class RoleUpdate(BaseModel):
    name: Optional[str] = None
    description: Optional[str] = None


class PermissionBase(BaseModel):
    name: str
    description: Optional[str] = None


class PermissionCreate(PermissionBase):
    pass


class PermissionOut(PermissionBase):
    id: int

    class Config:
        orm_mode = True


class PermissionUpdate(BaseModel):
    name: Optional[str] = None
    description: Optional[str] = None


class UserRoleAssign(BaseModel):
    role_id: int
