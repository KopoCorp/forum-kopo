import os
from sqlalchemy import create_engine
from sqlalchemy.orm import declarative_base, sessionmaker

# Optional tuning parameters for the connection pool.  These can be
# adjusted through environment variables to better suit the deployment
# environment without changing the code.
POOL_SIZE = int(os.getenv("DB_POOL_SIZE", "5"))
MAX_OVERFLOW = int(os.getenv("DB_MAX_OVERFLOW", "10"))

DATABASE_URL = os.getenv("DATABASE_URL", "postgresql://user:password@localhost/forumdb")

engine = create_engine(
    DATABASE_URL,
    connect_args={"client_encoding": "utf8"},
    pool_size=POOL_SIZE,
    max_overflow=MAX_OVERFLOW,
    pool_pre_ping=True,
)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

Base = declarative_base()


def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
