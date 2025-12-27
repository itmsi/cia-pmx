app/
├── Controllers/
│   ├── BoardController.php
│   ├── ColumnController.php
│   ├── CardController.php
│   ├── AuthController.php
│   └── ActivityLogController.php
├── Services/
│   ├── BoardService.php
│   ├── ColumnService.php
│   ├── CardService.php
│   ├── AuthService.php
│   └── ActivityLogService.php
├── Models/
├── Views/
│   ├── boards/
│   ├── columns/
│   ├── cards/
│   └── activity_logs/
public/
└── js/
    └── kanban.js

# Kanban Board — CodeIgniter 4

A full-featured Kanban Board web application built with **CodeIgniter 4**, focusing on clean architecture, correctness, and real-world backend practices.

This project was built as a learning and portfolio project to demonstrate how a production-ready internal tool is designed and implemented.

---

## ✨ Features

### Authentication & Security
- Session-based authentication
- CSRF protection
- Route protection using filters
- Ownership-based authorization (users only see their own data)

### Board Management
- Create, edit, delete boards
- Boards are owned by users
- Secure access enforcement

### Column Management
- Full CRUD UI for columns
- Ordered columns using `position`
- Columns belong to boards

### Card Management
- Full CRUD UI for cards
- Ordered cards using `position`
- Drag & drop support
- Safe reordering with database transactions

### Drag & Drop
- Native HTML5 Drag & Drop
- Backend-driven ordering logic
- No frontend frameworks

### Activity Log (Audit Trail)
- Logs important user actions:
  - Login / logout
  - Board, column, card changes
- Per-user activity visibility
- Paginated for performance

### Performance & Scalability
- Database indexes on hot queries
- N+1 query elimination
- Batch fetching of cards
- Defensive reindexing limits

---

## 🧱 Architecture Overview

This project follows a **Service-Oriented MVC** structure:

- **Controllers**  
  Handle HTTP requests and responses only.

- **Services**  
  Contain business logic (ordering, ownership, validation flow).

- **Models**  
  Interact with the database.

- **Views**  
  Render data only (no business logic).

- **JavaScript**  
  Separated into `/public/js` with no inline scripts.

This separation makes the codebase easier to test, maintain, and extend.

---

## 🧠 Key Engineering Decisions

### 1. Ownership Enforcement
All queries are scoped by `user_id` to prevent data leakage.

### 2. Correct Ordering Logic
Card reordering uses:
- Transactions
- Position shifting
- Reindexing safeguards

This prevents duplicate positions and broken states.

### 3. No Premature Frameworks
Plain PHP and JavaScript were intentionally used to focus on fundamentals.

### 4. Audit Logging
Activity logs were added early to support debugging and accountability.

---

## 🧪 Example Use Case

1. User logs in
2. Creates a board
3. Adds columns (Todo, In Progress, Review, Done)
4. Creates cards
5. Drags cards between columns
6. All actions are logged automatically

---

## 🛠️ Tech Stack

- PHP 8+
- CodeIgniter 4
- MySQL
- HTML5 Drag & Drop
- Vanilla JavaScript

---

## 📈 What I Learned

- Designing clean backend architecture
- Implementing safe ordering algorithms
- Session-based authentication
- Authorization vs authentication
- Performance optimization with indexing
- Avoiding N+1 query problems
- Building maintainable CRUD systems

---

## 🚀 Future Improvements

- Role-based permissions
- REST API (API/V1)
- Automated tests (PHPUnit)
- Real-time updates (WebSocket)
- UI/UX improvements

---
