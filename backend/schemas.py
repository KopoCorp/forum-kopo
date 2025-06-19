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
