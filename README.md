# 🎯 Kanban Project Management System — CodeIgniter 4

A comprehensive, production-ready **Project Management System** built with **CodeIgniter 4**, featuring Kanban boards, Sprint management, Wiki documentation, advanced reporting, and complete RBAC (Role-Based Access Control).

This system provides a full-featured project management solution with clean architecture, security best practices, and real-world backend implementation patterns.

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Database Setup](#-database-setup)
- [Running the Application](#-running-the-application)
- [Project Structure](#-project-structure)
- [Architecture](#-architecture)
- [Key Features Detail](#-key-features-detail)

---

## ✨ Features

### 🔐 Authentication & Security
- ✅ Session-based authentication with email verification
- ✅ CSRF protection on all forms
- ✅ Route protection using filters
- ✅ Force logout functionality for administrators
- ✅ Activity logging for security audit
- ✅ Password requirements (uppercase, number, min 6 chars)

### 👥 User & Role Management
- ✅ Complete CRUD for users
- ✅ User status management (active/inactive)
- ✅ Role assignment (Admin, PM, Developer, QA, Viewer)
- ✅ Granular permission system (27+ permissions)
- ✅ Role-Permission mapping
- ✅ User profile with photo and contact information
- ✅ Multi-workspace support per user

### 🏢 Workspace & Organization
- ✅ Multi-workspace support (multi-company/team)
- ✅ Workspace settings (logo, timezone, default role)
- ✅ User assignment to multiple workspaces
- ✅ Workspace-level permissions

### 📊 Project Management
- ✅ Complete CRUD for projects
- ✅ Project visibility (private/workspace/public)
- ✅ Project status tracking (planning, active, on-hold, completed, cancelled)
- ✅ Project users assignment
- ✅ Auto-generated issue keys (e.g., MSI-1, MSI-2)
- ✅ Project boards with customizable columns

### 📋 Issue/Task Management
- ✅ Complete CRUD for issues
- ✅ Issue types (Task, Bug, Story, Epic, Sub-task)
- ✅ Priority levels (Lowest, Low, Medium, High, Critical)
- ✅ Status management via board columns
- ✅ Assignee assignment
- ✅ Due date tracking
- ✅ Story point estimation
- ✅ Labels/Tags system
- ✅ Parent-child issue relationships
- ✅ Drag & drop between columns
- ✅ Workflow validation rules

### 🏷️ Labels & Tags
- ✅ Workspace-level and project-level labels
- ✅ Color-coded labels
- ✅ Multiple labels per issue
- ✅ Label management UI

### 💬 Comments & Collaboration
- ✅ Threaded comments on issues
- ✅ Comment editing and deletion
- ✅ User mentions support
- ✅ Activity logging for comments

### 📎 Attachments
- ✅ File upload (images, PDFs, documents)
- ✅ File size validation (max 10MB)
- ✅ File type categorization
- ✅ Download and preview functionality
- ✅ Attachment management per issue

### 🔄 Workflow Management
- ✅ Customizable workflow rules
- ✅ Global and board-specific rules
- ✅ Blocked transitions (e.g., Done → Backlog)
- ✅ Conditional rules (require assignee, require description, min priority)
- ✅ Workflow validation on status changes
- ✅ Status change history tracking

### 🏃 Sprint & Scrum
- ✅ Sprint CRUD operations
- ✅ Sprint duration (1-4 weeks)
- ✅ Sprint status (planned, active, completed)
- ✅ Sprint goal tracking
- ✅ Auto-calculate end dates
- ✅ Issue assignment to sprints
- ✅ Auto carry-over unfinished issues
- ✅ Sprint capacity calculation
- ✅ Sprint backlog management

### 📈 Reports & Analytics
- ✅ Velocity charts
- ✅ Burndown charts
- ✅ Burnup charts
- ✅ Lead time analysis
- ✅ Productivity per user
- ✅ Project statistics dashboard

### 📚 Wiki Documentation
- ✅ Wiki pages per project
- ✅ Hierarchical page structure
- ✅ Version history
- ✅ Version restore functionality
- ✅ Wiki permissions (view, edit, delete)
- ✅ Markdown support

### 🔍 Search & Filtering
- ✅ Filter by status, priority, assignee, label, due date
- ✅ Search in title, description, issue key
- ✅ Saved filters (favorites)
- ✅ Default filter support
- ✅ Project-specific and global filters

### 📊 Dashboard
- ✅ Overview statistics
- ✅ Recent activity feed
- ✅ Overdue tasks
- ✅ Tasks by assignee
- ✅ Project summaries

### 🔒 Audit & Security
- ✅ Activity logging for all major actions
- ✅ Login history tracking
- ✅ Force logout functionality
- ✅ Security event logging

---

## 🛠️ Tech Stack

- **Backend Framework:** CodeIgniter 4.6.4+
- **PHP Version:** 8.1+
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Frontend:** Vanilla JavaScript, HTML5, CSS3
- **Icons:** Font Awesome 6.0
- **Session:** File-based sessions
- **File Storage:** Local filesystem

---

## 📦 Requirements

### Server Requirements
- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- Composer (for dependency management)
- PHP Extensions:
  - `mysqli` or `pdo_mysql`
  - `mbstring`
  - `curl`
  - `json`
  - `zip`
  - `gd` (for image processing)

### Development Tools (Optional)
- Git
- PHPUnit (for testing)
- Mailpit/MailHog (for email testing)

---

## 🚀 Installation

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd kanban-ci4
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Environment Configuration

Copy the environment file:

```bash
cp env .env
```

Edit `.env` file and configure:

```ini
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'http://localhost:8080/'
app.forceGlobalSecureRequests = false

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = kanban_db
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.port = 3306

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------
session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
session.cookieName = 'ci_session'
session.expiration = 7200

#--------------------------------------------------------------------
# EMAIL (Optional - for email verification)
#--------------------------------------------------------------------
email.protocol = 'smtp'
email.SMTPHost = 'localhost'
email.SMTPPort = 1025
email.SMTPUser = ''
email.SMTPPass = ''
```

### Step 4: Create Database

Create a MySQL database:

```sql
CREATE DATABASE kanban_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or using command line:

```bash
mysql -u root -p -e "CREATE DATABASE kanban_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 5: Set Permissions

Make sure the `writable` directory is writable:

```bash
chmod -R 775 writable/
```

On Linux/Mac:
```bash
sudo chown -R www-data:www-data writable/
```

### Step 6: Run Migrations

Run all database migrations:

```bash
php spark migrate
```

This will create all necessary tables:
- users, roles, permissions
- workspaces, projects
- boards, columns, issues
- sprints, labels, comments, attachments
- activity_logs, workflow_rules
- saved_filters, wiki_pages
- And all relationship tables

### Step 7: Run Seeders (Optional)

Seed initial data (roles, permissions, sample data):

```bash
php spark db:seed RolesSeeder
php spark db:seed PermissionsSeeder
php spark db:seed RolePermissionsSeeder
```

### Step 8: Create Upload Directory

Create directory for file uploads:

```bash
mkdir -p writable/uploads/attachments
chmod -R 775 writable/uploads/
```

---

## ⚙️ Configuration

### Database Configuration

Edit `app/Config/Database.php` or use `.env`:

```ini
database.default.hostname = localhost
database.default.database = kanban_db
database.default.username = your_username
database.default.password = your_password
database.default.DBDriver = MySQLi
```

### Application Configuration

Edit `app/Config/App.php` or use `.env`:

```ini
app.baseURL = 'http://localhost:8080/'
app.forceGlobalSecureRequests = false
```

### Session Configuration

Edit `app/Config/Session.php`:

```php
public string $driver = FileHandler::class;
public string $cookieName = 'ci_session';
public int $expiration = 7200; // 2 hours
public string $savePath = WRITEPATH . 'session';
```

### Email Configuration (for email verification)

Edit `app/Config/Email.php` or use `.env`:

For development with Mailpit:
```ini
email.protocol = 'smtp'
email.SMTPHost = 'localhost'
email.SMTPPort = 1025
email.SMTPUser = ''
email.SMTPPass = ''
```

---

## 🗄️ Database Setup

### Manual Migration

If you need to run migrations manually:

```bash
# Run all migrations
php spark migrate

# Run specific migration
php spark migrate -g default

# Rollback last migration
php spark migrate:rollback

# Rollback all migrations
php spark migrate:rollback -b 0
```

### Seeders

Available seeders:

```bash
# Seed roles (Admin, PM, Developer, QA, Viewer)
php spark db:seed RolesSeeder

# Seed permissions (27+ permissions)
php spark db:seed PermissionsSeeder

# Seed role-permission mappings
php spark db:seed RolePermissionsSeeder
```

### Database Structure

Key tables:
- `users` - User accounts
- `roles` - User roles
- `permissions` - System permissions
- `role_permissions` - Role-Permission mapping
- `workspaces` - Organizations/companies
- `workspace_users` - Workspace membership
- `projects` - Projects
- `project_users` - Project membership
- `boards` - Kanban boards
- `columns` - Board columns (status)
- `issues` - Tasks/issues
- `sprints` - Sprint management
- `labels` - Tags/labels
- `issue_labels` - Issue-Label mapping
- `comments` - Issue comments
- `attachments` - File attachments
- `workflow_rules` - Workflow validation rules
- `activity_logs` - Audit trail
- `saved_filters` - User saved filters
- `wiki_pages` - Wiki documentation
- `wiki_versions` - Wiki version history

---

## 🏃 Running the Application

### Development Server

Start the built-in PHP development server:

```bash
php spark serve
```

The application will be available at: `http://localhost:8080`

### Production Server

For production, configure your web server (Apache/Nginx) to point to the `public` directory.

**Apache .htaccess example:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

**Nginx configuration example:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/kanban-ci4/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### First Time Setup

1. **Register a new account:**
   - Navigate to `/register`
   - Fill in email and password
   - Check your email for verification link (or check Mailpit if in development)

2. **Verify email:**
   - Click the verification link in your email
   - Or access directly: `/verify-email/{token}`

3. **Login:**
   - Navigate to `/login`
   - Use your verified credentials

4. **Create workspace:**
   - Go to `/workspaces/create`
   - Create your first workspace

5. **Create project:**
   - Go to `/projects/create`
   - Create your first project

6. **Start managing issues:**
   - Navigate to your project
   - Create boards and columns
   - Start creating issues!

---

## 📁 Project Structure

```
kanban-ci4/
├── app/
│   ├── Config/              # Configuration files
│   │   ├── App.php
│   │   ├── Database.php
│   │   ├── Routes.php
│   │   ├── Session.php
│   │   └── ...
│   ├── Controllers/         # HTTP request handlers
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── ProjectController.php
│   │   ├── IssueController.php
│   │   ├── SprintController.php
│   │   ├── DashboardController.php
│   │   └── ...
│   ├── Services/            # Business logic layer
│   │   ├── AuthService.php
│   │   ├── UserService.php
│   │   ├── ProjectService.php
│   │   ├── IssueService.php
│   │   ├── SprintService.php
│   │   ├── WorkflowService.php
│   │   └── ...
│   ├── Models/              # Database models
│   │   ├── UserModel.php
│   │   ├── ProjectModel.php
│   │   ├── IssueModel.php
│   │   └── ...
│   ├── Views/               # View templates
│   │   ├── layouts/
│   │   ├── auth/
│   │   ├── users/
│   │   ├── projects/
│   │   ├── issues/
│   │   └── ...
│   ├── Database/
│   │   ├── Migrations/      # Database migrations
│   │   └── Seeds/           # Database seeders
│   ├── Filters/             # Route filters
│   │   └── AuthFilter.php
│   └── Helpers/             # Helper functions
├── public/                  # Public web root
│   ├── index.php           # Entry point
│   ├── js/
│   │   └── kanban.js       # Frontend JavaScript
│   └── favicon.ico
├── writable/               # Writable directories
│   ├── cache/
│   ├── logs/
│   ├── session/
│   └── uploads/
│       └── attachments/
├── tests/                   # PHPUnit tests
├── vendor/                  # Composer dependencies
├── .env                     # Environment configuration
├── composer.json
└── README.md
```

---

## 🏗️ Architecture

This project follows a **Service-Oriented MVC** architecture:

### Controllers
- Handle HTTP requests and responses only
- Validate input
- Call services for business logic
- Return views or JSON responses

### Services
- Contain all business logic
- Handle complex operations
- Coordinate between models
- Implement validation rules
- Manage transactions

### Models
- Interact with database
- Define table structure
- Handle data validation
- Provide query builders

### Views
- Render data only (no business logic)
- Use PHP templating
- Include reusable components
- Handle form submissions

### Key Principles
- **Separation of Concerns:** Each layer has a specific responsibility
- **DRY (Don't Repeat Yourself):** Reusable services and helpers
- **Security First:** Input validation, CSRF protection, authorization checks
- **Audit Trail:** All important actions are logged
- **Performance:** Database indexes, query optimization, batch operations

---

## 🔑 Key Features Detail

### 1. Role-Based Access Control (RBAC)
- **5 Default Roles:** Admin, Project Manager, Developer, QA, Viewer
- **27+ Permissions:** Granular control over actions
- **Role-Permission Mapping:** Flexible permission assignment
- **Project-Level Permissions:** Additional permissions per project

### 2. Workflow Management
- **Customizable Rules:** Define allowed/blocked transitions
- **Conditional Rules:** Require assignee, description, minimum priority
- **Global & Board-Specific:** Rules can apply globally or per board
- **Validation:** Automatic validation on status changes

### 3. Sprint Management
- **Sprint Planning:** Create sprints with goals and duration
- **Auto-Carry Over:** Unfinished issues automatically move to next sprint
- **Capacity Tracking:** Calculate sprint capacity and completion
- **Sprint Backlog:** Manage issues within sprints

### 4. Advanced Filtering
- **Multiple Filters:** Status, priority, assignee, label, due date
- **Saved Filters:** Save frequently used filters
- **Default Filters:** Set default filter per project
- **Search:** Full-text search in titles and descriptions

### 5. Wiki Documentation
- **Hierarchical Pages:** Create nested wiki pages
- **Version History:** Track all changes
- **Version Restore:** Restore previous versions
- **Permissions:** Control who can view/edit wiki pages

### 6. Reporting & Analytics
- **Velocity Charts:** Track team velocity over time
- **Burndown/Burnup:** Sprint progress visualization
- **Lead Time:** Measure time from creation to completion
- **Productivity Reports:** User productivity metrics

---

## 🧪 Testing

Run PHPUnit tests:

```bash
# Run all tests
composer test

# Or using PHPUnit directly
vendor/bin/phpunit
```

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📞 Support

For issues, questions, or contributions, please open an issue on the repository.

---

## 🎯 Roadmap

- [ ] REST API endpoints
- [ ] Real-time updates (WebSocket)
- [ ] Mobile app support
- [ ] Advanced reporting dashboard
- [ ] Integration with external tools (Jira, Slack, etc.)
- [ ] Two-factor authentication (2FA)
- [ ] OAuth integration (Google, GitHub)

---

**Built with ❤️ using CodeIgniter 4**
