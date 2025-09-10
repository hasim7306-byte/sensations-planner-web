from fastapi import FastAPI, APIRouter, HTTPException, Depends, status, Form, File, UploadFile
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from dotenv import load_dotenv
from starlette.middleware.cors import CORSMiddleware
from motor.motor_asyncio import AsyncIOMotorClient
import os
import logging
from pathlib import Path
from pydantic import BaseModel, Field
from typing import List, Optional, Dict, Any
import uuid
from datetime import datetime, date, time, timezone, timedelta
import jwt
from passlib.context import CryptContext
import asyncio
from enum import Enum

ROOT_DIR = Path(__file__).parent
load_dotenv(ROOT_DIR / '.env')

# MongoDB connection
mongo_url = os.environ['MONGO_URL']
client = AsyncIOMotorClient(mongo_url)
db = client[os.environ['DB_NAME']]

# JWT and Password hashing
SECRET_KEY = "your-secret-key-here-make-it-strong-in-production"
ALGORITHM = "HS256"
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
security = HTTPBearer()

# Create the main app
app = FastAPI(title="Senations To Go Planner", version="1.0.0")
api_router = APIRouter(prefix="/api")

# Enums
class UserRole(str, Enum):
    ADMIN = "admin"
    MANAGER = "manager" 
    EMPLOYEE = "employee"

class ShiftStatus(str, Enum):
    SCHEDULED = "scheduled"
    CONFIRMED = "confirmed"
    COMPLETED = "completed"
    CANCELLED = "cancelled"

class LeaveStatus(str, Enum):
    PENDING = "pending"
    APPROVED = "approved"
    REJECTED = "rejected"

class LeaveType(str, Enum):
    VACATION = "vacation"
    SICK = "sick"
    PERSONAL = "personal"
    MATERNITY = "maternity"
    OTHER = "other"

# Models
class User(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    email: str
    name: str
    role: UserRole
    department: Optional[str] = None
    phone: Optional[str] = None
    hourly_rate: Optional[float] = None
    skills: List[str] = []
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    is_active: bool = True

class UserCreate(BaseModel):
    email: str
    password: str
    name: str
    role: UserRole = UserRole.EMPLOYEE
    department: Optional[str] = None
    phone: Optional[str] = None
    hourly_rate: Optional[float] = None
    skills: List[str] = []

class UserLogin(BaseModel):
    email: str
    password: str

class Token(BaseModel):
    access_token: str
    token_type: str
    user: User

class Shift(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    employee_id: str
    date: date
    start_time: time
    end_time: time
    break_duration: int = 30  # minutes
    department: Optional[str] = None
    notes: Optional[str] = None
    status: ShiftStatus = ShiftStatus.SCHEDULED
    created_by: str
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class ShiftCreate(BaseModel):
    employee_id: str
    date: date
    start_time: time
    end_time: time
    break_duration: int = 30
    department: Optional[str] = None
    notes: Optional[str] = None

class TimeEntry(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    employee_id: str
    date: date
    clock_in: Optional[datetime] = None
    clock_out: Optional[datetime] = None
    break_start: Optional[datetime] = None
    break_end: Optional[datetime] = None
    total_hours: Optional[float] = None
    is_approved: bool = False
    approved_by: Optional[str] = None
    notes: Optional[str] = None
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class LeaveRequest(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    employee_id: str
    leave_type: LeaveType
    start_date: date
    end_date: date
    reason: str
    status: LeaveStatus = LeaveStatus.PENDING
    approved_by: Optional[str] = None
    approved_at: Optional[datetime] = None
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class LeaveRequestCreate(BaseModel):
    leave_type: LeaveType
    start_date: date
    end_date: date
    reason: str

class ChatMessage(BaseModel):
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    sender_id: str
    sender_name: str
    message: str
    timestamp: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    is_system: bool = False

class ChatMessageCreate(BaseModel):
    message: str

# Helper functions
def create_access_token(data: dict):
    to_encode = data.copy()
    expire = datetime.now(timezone.utc) + timedelta(hours=24)
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    return encoded_jwt

def verify_password(plain_password, hashed_password):
    return pwd_context.verify(plain_password, hashed_password)

def get_password_hash(password):
    return pwd_context.hash(password)

def prepare_for_mongo(data):
    if isinstance(data, dict):
        for key, value in data.items():
            if isinstance(value, date):
                data[key] = value.isoformat()
            elif isinstance(value, time):
                data[key] = value.strftime('%H:%M:%S')
            elif isinstance(value, datetime):
                data[key] = value.isoformat()
    return data

def parse_from_mongo(item):
    if isinstance(item, dict):
        for key, value in item.items():
            if key.endswith('_date') and isinstance(value, str):
                try:
                    item[key] = datetime.fromisoformat(value).date()
                except:
                    pass
            elif key.endswith('_time') and isinstance(value, str):
                try:
                    item[key] = datetime.strptime(value, '%H:%M:%S').time()
                except:
                    pass
            elif key.endswith('_at') and isinstance(value, str):
                try:
                    item[key] = datetime.fromisoformat(value)
                except:
                    pass
    return item

async def get_current_user(credentials: HTTPAuthorizationCredentials = Depends(security)):
    try:
        token = credentials.credentials
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        user_id = payload.get("user_id")
        if user_id is None:
            raise HTTPException(status_code=401, detail="Invalid token")
        
        user_data = await db.users.find_one({"id": user_id})
        if user_data is None:
            raise HTTPException(status_code=401, detail="User not found")
        
        return User(**parse_from_mongo(user_data))
    except jwt.PyJWTError:
        raise HTTPException(status_code=401, detail="Invalid token")

# Initialize admin user on startup
async def create_admin_user():
    """Create default admin user if it doesn't exist"""
    admin_email = "admin@sensationstogo.nl"
    admin_password = "Ss202501"
    
    # Check if admin user exists
    existing_admin = await db.users.find_one({"email": admin_email})
    if not existing_admin:
        # Create admin user
        hashed_password = get_password_hash(admin_password)
        admin_user = User(
            email=admin_email,
            name="System Administrator",
            role=UserRole.ADMIN,
            department="IT",
            is_active=True
        )
        
        admin_mongo = prepare_for_mongo(admin_user.dict())
        admin_mongo["password_hash"] = hashed_password
        await db.users.insert_one(admin_mongo)
        
        logger.info("Default admin user created successfully")
    else:
        logger.info("Admin user already exists")

@app.on_event("startup")
async def startup_event():
    await create_admin_user()

# Auth routes - Remove registration endpoint for public use
@api_router.post("/auth/login", response_model=Token)
async def login(login_data: UserLogin):
    # Find user
    user_data = await db.users.find_one({"email": login_data.email})
    if not user_data:
        raise HTTPException(status_code=401, detail="Invalid email or password")
    
    # Verify password
    if not verify_password(login_data.password, user_data["password_hash"]):
        raise HTTPException(status_code=401, detail="Invalid email or password")
    
    # Check if user is active
    if not user_data.get("is_active", True):
        raise HTTPException(status_code=401, detail="Account is deactivated")
    
    user = User(**parse_from_mongo(user_data))
    access_token = create_access_token({"user_id": user.id})
    
    return Token(access_token=access_token, token_type="bearer", user=user)

# User management routes
@api_router.get("/users", response_model=List[User])
async def get_users(current_user: User = Depends(get_current_user)):
    if current_user.role not in [UserRole.ADMIN, UserRole.MANAGER]:
        raise HTTPException(status_code=403, detail="Not authorized")
    
    users = await db.users.find({"is_active": True}).to_list(1000)
    return [User(**parse_from_mongo(user)) for user in users]

@api_router.post("/users", response_model=User)
async def create_user_by_admin(user_data: UserCreate, current_user: User = Depends(get_current_user)):
    if current_user.role != UserRole.ADMIN:
        raise HTTPException(status_code=403, detail="Only admins can create users")
    
    # Check if user exists
    existing_user = await db.users.find_one({"email": user_data.email})
    if existing_user:
        raise HTTPException(status_code=400, detail="Email already registered")
    
    # Hash password
    hashed_password = get_password_hash(user_data.password)
    
    # Create user
    user_dict = user_data.dict()
    user_dict.pop("password")
    user = User(**user_dict)
    
    # Store in database
    user_mongo = prepare_for_mongo(user.dict())
    user_mongo["password_hash"] = hashed_password
    await db.users.insert_one(user_mongo)
    
    return user

@api_router.put("/users/{user_id}", response_model=User)
async def update_user(user_id: str, user_data: dict, current_user: User = Depends(get_current_user)):
    if current_user.role != UserRole.ADMIN:
        raise HTTPException(status_code=403, detail="Only admins can update users")
    
    # Find user
    existing_user = await db.users.find_one({"id": user_id})
    if not existing_user:
        raise HTTPException(status_code=404, detail="User not found")
    
    # Update user
    update_data = {}
    allowed_fields = ["name", "email", "role", "department", "phone", "hourly_rate", "skills", "is_active"]
    
    for field in allowed_fields:
        if field in user_data:
            update_data[field] = user_data[field]
    
    if update_data:
        await db.users.update_one({"id": user_id}, {"$set": update_data})
    
    # Return updated user
    updated_user = await db.users.find_one({"id": user_id})
    return User(**parse_from_mongo(updated_user))

@api_router.delete("/users/{user_id}")
async def deactivate_user(user_id: str, current_user: User = Depends(get_current_user)):
    if current_user.role != UserRole.ADMIN:
        raise HTTPException(status_code=403, detail="Only admins can deactivate users")
    
    if user_id == current_user.id:
        raise HTTPException(status_code=400, detail="Cannot deactivate yourself")
    
    result = await db.users.update_one(
        {"id": user_id}, 
        {"$set": {"is_active": False}}
    )
    
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="User not found")
    
    return {"message": "User deactivated successfully"}

@api_router.get("/users/me", response_model=User)
async def get_current_user_profile(current_user: User = Depends(get_current_user)):
    return current_user

# Shift management routes
@api_router.post("/shifts", response_model=Shift)
async def create_shift(shift_data: ShiftCreate, current_user: User = Depends(get_current_user)):
    if current_user.role not in [UserRole.ADMIN, UserRole.MANAGER]:
        raise HTTPException(status_code=403, detail="Not authorized to create shifts")
    
    shift = Shift(**shift_data.dict(), created_by=current_user.id)
    shift_mongo = prepare_for_mongo(shift.dict())
    await db.shifts.insert_one(shift_mongo)
    
    return shift

@api_router.get("/shifts", response_model=List[Shift])
async def get_shifts(
    start_date: Optional[str] = None,
    end_date: Optional[str] = None,
    employee_id: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    query = {}
    
    # If employee, only show their shifts
    if current_user.role == UserRole.EMPLOYEE:
        query["employee_id"] = current_user.id
    elif employee_id:
        query["employee_id"] = employee_id
    
    # Date filtering
    if start_date:
        query["date"] = {"$gte": start_date}
    if end_date:
        if "date" in query:
            query["date"]["$lte"] = end_date
        else:
            query["date"] = {"$lte": end_date}
    
    shifts = await db.shifts.find(query).to_list(1000)
    return [Shift(**parse_from_mongo(shift)) for shift in shifts]

# Time tracking routes
@api_router.post("/time/clock-in")
async def clock_in(current_user: User = Depends(get_current_user)):
    today = datetime.now(timezone.utc).date()
    
    # Check if already clocked in today
    existing_entry = await db.time_entries.find_one({
        "employee_id": current_user.id,
        "date": today.isoformat()
    })
    
    if existing_entry and existing_entry.get("clock_in") and not existing_entry.get("clock_out"):
        raise HTTPException(status_code=400, detail="Already clocked in")
    
    time_entry = TimeEntry(
        employee_id=current_user.id,
        date=today,
        clock_in=datetime.now(timezone.utc)
    )
    
    time_entry_mongo = prepare_for_mongo(time_entry.dict())
    await db.time_entries.insert_one(time_entry_mongo)
    
    return {"message": "Clocked in successfully", "time": datetime.now(timezone.utc)}

@api_router.post("/time/clock-out")
async def clock_out(current_user: User = Depends(get_current_user)):
    today = datetime.now(timezone.utc).date()
    
    time_entry = await db.time_entries.find_one({
        "employee_id": current_user.id,
        "date": today.isoformat(),
        "clock_out": None
    })
    
    if not time_entry:
        raise HTTPException(status_code=400, detail="No active clock-in found")
    
    clock_out_time = datetime.now(timezone.utc)
    clock_in_time = datetime.fromisoformat(time_entry["clock_in"])
    
    # Calculate total hours
    total_hours = (clock_out_time - clock_in_time).total_seconds() / 3600
    if time_entry.get("break_start") and time_entry.get("break_end"):
        break_start = datetime.fromisoformat(time_entry["break_start"])
        break_end = datetime.fromisoformat(time_entry["break_end"])
        break_hours = (break_end - break_start).total_seconds() / 3600
        total_hours -= break_hours
    
    await db.time_entries.update_one(
        {"id": time_entry["id"]},
        {"$set": {
            "clock_out": clock_out_time.isoformat(),
            "total_hours": round(total_hours, 2)
        }}
    )
    
    return {"message": "Clocked out successfully", "total_hours": round(total_hours, 2)}

@api_router.get("/time/entries", response_model=List[TimeEntry])
async def get_time_entries(
    start_date: Optional[str] = None,
    end_date: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    query = {}
    
    if current_user.role == UserRole.EMPLOYEE:
        query["employee_id"] = current_user.id
    
    if start_date:
        query["date"] = {"$gte": start_date}
    if end_date:
        if "date" in query:
            query["date"]["$lte"] = end_date
        else:
            query["date"] = {"$lte": end_date}
    
    entries = await db.time_entries.find(query).to_list(1000)
    return [TimeEntry(**parse_from_mongo(entry)) for entry in entries]

# Leave management routes
@api_router.post("/leave/request", response_model=LeaveRequest)
async def create_leave_request(leave_data: LeaveRequestCreate, current_user: User = Depends(get_current_user)):
    leave_request = LeaveRequest(**leave_data.dict(), employee_id=current_user.id)
    leave_mongo = prepare_for_mongo(leave_request.dict())
    await db.leave_requests.insert_one(leave_mongo)
    
    return leave_request

@api_router.get("/leave/requests", response_model=List[LeaveRequest])
async def get_leave_requests(current_user: User = Depends(get_current_user)):
    query = {}
    
    if current_user.role == UserRole.EMPLOYEE:
        query["employee_id"] = current_user.id
    
    requests = await db.leave_requests.find(query).to_list(1000)
    return [LeaveRequest(**parse_from_mongo(request)) for request in requests]

@api_router.put("/leave/requests/{request_id}/approve")
async def approve_leave_request(request_id: str, current_user: User = Depends(get_current_user)):
    if current_user.role not in [UserRole.ADMIN, UserRole.MANAGER]:
        raise HTTPException(status_code=403, detail="Not authorized")
    
    await db.leave_requests.update_one(
        {"id": request_id},
        {"$set": {
            "status": LeaveStatus.APPROVED.value,
            "approved_by": current_user.id,
            "approved_at": datetime.now(timezone.utc).isoformat()
        }}
    )
    
    return {"message": "Leave request approved"}

@api_router.put("/leave/requests/{request_id}/reject")
async def reject_leave_request(request_id: str, current_user: User = Depends(get_current_user)):
    if current_user.role not in [UserRole.ADMIN, UserRole.MANAGER]:
        raise HTTPException(status_code=403, detail="Not authorized")
    
    await db.leave_requests.update_one(
        {"id": request_id},
        {"$set": {
            "status": LeaveStatus.REJECTED.value,
            "approved_by": current_user.id,
            "approved_at": datetime.now(timezone.utc).isoformat()
        }}
    )
    
    return {"message": "Leave request rejected"}

# Chat routes
@api_router.get("/chat/messages", response_model=List[ChatMessage])
async def get_chat_messages(current_user: User = Depends(get_current_user)):
    messages = await db.chat_messages.find().sort("timestamp", -1).limit(100).to_list(100)
    return [ChatMessage(**parse_from_mongo(msg)) for msg in reversed(messages)]

@api_router.post("/chat/messages", response_model=ChatMessage)
async def send_chat_message(message_data: ChatMessageCreate, current_user: User = Depends(get_current_user)):
    message = ChatMessage(
        sender_id=current_user.id,
        sender_name=current_user.name,
        message=message_data.message
    )
    
    message_mongo = prepare_for_mongo(message.dict())
    await db.chat_messages.insert_one(message_mongo)
    
    return message

# Dashboard routes
@api_router.get("/dashboard/stats")
async def get_dashboard_stats(current_user: User = Depends(get_current_user)):
    if current_user.role not in [UserRole.ADMIN, UserRole.MANAGER]:
        raise HTTPException(status_code=403, detail="Not authorized")
    
    today = datetime.now(timezone.utc).date()
    week_start = today - timedelta(days=today.weekday())
    
    # Get stats
    total_employees = await db.users.count_documents({"is_active": True, "role": UserRole.EMPLOYEE.value})
    shifts_today = await db.shifts.count_documents({"date": today.isoformat()})
    pending_leaves = await db.leave_requests.count_documents({"status": LeaveStatus.PENDING.value})
    
    # Active time entries (clocked in)
    active_entries = await db.time_entries.count_documents({
        "date": today.isoformat(),
        "clock_in": {"$ne": None},
        "clock_out": None
    })
    
    return {
        "total_employees": total_employees,
        "shifts_today": shifts_today,
        "pending_leaves": pending_leaves,
        "active_employees": active_entries
    }

# Root route
@api_router.get("/")
async def root():
    return {"message": "Senations To Go Planner API", "version": "1.0.0"}

# Include router
app.include_router(api_router)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_credentials=True,
    allow_origins=os.environ.get('CORS_ORIGINS', '*').split(','),
    allow_methods=["*"],
    allow_headers=["*"],
)

# Logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@app.on_event("shutdown")
async def shutdown_db_client():
    client.close()