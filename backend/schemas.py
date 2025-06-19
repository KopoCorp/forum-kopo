from datetime import datetime
from typing import Optional
from pydantic import BaseModel


class UserBase(BaseModel):
    username: str
    email: str
    bio: Optional[str] = None
    avatar_url: Optional[str] = None


class UserCreate(UserBase):
    pass_hash: str


class UserOut(UserBase):
    id: int
    is_active: bool
    created_at: datetime

    class Config:
        orm_mode = True


class ArticleBase(BaseModel):
    title: str
    content: str
    is_pub: Optional[bool] = False


class ArticleCreate(ArticleBase):
    user_id: Optional[int]


class ArticleOut(ArticleBase):
    id: int
    user_id: Optional[int]
    created_at: datetime
    updated_at: datetime

    class Config:
        orm_mode = True


class CommentBase(BaseModel):
    content: str
    parent_id: Optional[int] = None


class CommentCreate(CommentBase):
    post_id: int
    user_id: Optional[int]


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

class DirectMessageBase(BaseModel):
    content: str


class DirectMessageCreate(DirectMessageBase):
    sender_id: Optional[int]
    receiver_id: int


class DirectMessageOut(DirectMessageBase):
    id: int
    sender_id: Optional[int]
    receiver_id: int
    is_read: bool
    created_at: datetime

    class Config:
        orm_mode = True


class NotificationBase(BaseModel):
    message: str


class NotificationCreate(NotificationBase):
    user_id: Optional[int]


class NotificationOut(NotificationBase):
    id: int
    user_id: Optional[int]
    is_read: bool
      
class AttachmentOut(BaseModel):
    id: int
    filename: str
    content_type: Optional[str] = None
    created_at: datetime

    class Config:
        orm_mode = True
