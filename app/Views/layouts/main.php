<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanban MVP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --primary-color: #4a90e2;
            --primary-hover: #3a7bc8;
            --text-color: #2c3e50;
            --text-secondary: #5f6368;
            --border-color: #e0e0e0;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.2s ease-in-out;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif;
            color: var(--text-color);
            line-height: 1.5;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: var(--bg-white);
            box-shadow: var(--shadow-sm);
            padding: 0 24px;
            height: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-icon {
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-link {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .nav-link i {
            font-size: 0.9rem;
        }

        .nav-link:hover {
            background-color: var(--bg-light);
            color: var(--primary-color);
        }

        .nav-link.active {
            color: var(--primary-color);
            background-color: rgba(74, 144, 226, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-email {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }

        .btn-outline:hover {
            background-color: var(--bg-light);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        main {
            flex: 1;
            padding: 30px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 0 15px;
            }

            .nav-links {
                gap: 8px;
                flex-wrap: wrap;
            }

            .nav-link {
                padding: 6px 10px;
                font-size: 0.85rem;
            }

            .nav-link {
                font-size: 0.8rem;
            }

            .nav-link i {
                font-size: 1rem;
            }

            main {
                padding: 20px 15px;
            }
        }

        @media (max-width: 480px) {
            .logo span:last-child {
                display: none; /* Hide logo text on very small screens */
            }

            .nav-links {
                gap: 5px;
            }

            .user-email {
                display: none; /* Hide email on mobile */
            }
        }
    </style>
    <script>
    window.CSRF_TOKEN = "<?= csrf_hash() ?>";
    </script>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="/" class="logo">
                <span class="logo-icon">📋</span>
                <span>Kanban MVP</span>
            </a>

            <nav class="nav-links">
                <?php if (session()->get('user_id')): ?>
                    <a href="/projects" class="nav-link <?= strpos(current_url(), base_url('projects')) !== false ? 'active' : '' ?>">
                        <i class="fas fa-folder"></i> Projects
                    </a>
                    <a href="/workspaces" class="nav-link <?= strpos(current_url(), base_url('workspaces')) !== false ? 'active' : '' ?>">
                        <i class="fas fa-building"></i> Workspaces
                    </a>
                    <a href="/boards" class="nav-link <?= strpos(current_url(), base_url('boards')) !== false ? 'active' : '' ?>">
                        <i class="fas fa-columns"></i> Boards
                    </a>
                    <a href="/activity-logs" class="nav-link <?= strpos(current_url(), base_url('activity-logs')) !== false ? 'active' : '' ?>">
                        <i class="fas fa-history"></i> Activity
                    </a>
                    <?php
                    // Check if user is admin (role_id = 1 or role slug = 'admin')
                    // For now, we'll show Users, Roles & Permissions if role_id exists and is 1
                    // This can be enhanced later with proper permission checking
                    $userRoleId = session()->get('role_id');
                    if ($userRoleId == 1): // Assuming role_id 1 is Admin
                    ?>
                        <a href="/users" class="nav-link <?= strpos(current_url(), base_url('users')) !== false ? 'active' : '' ?>">
                            <i class="fas fa-users"></i> Users
                        </a>
                        <a href="/roles" class="nav-link <?= strpos(current_url(), base_url('roles')) !== false ? 'active' : '' ?>">
                            <i class="fas fa-user-shield"></i> Roles
                        </a>
                        <a href="/permissions" class="nav-link <?= strpos(current_url(), base_url('permissions')) !== false ? 'active' : '' ?>">
                            <i class="fas fa-key"></i> Permissions
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <div class="user-info">
                <?php if (session()->get('user_id')): ?>
                    <span class="user-email"><?= esc(session()->get('user_email')) ?></span>
                    <a href="/logout" class="btn btn-outline">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <?= $this->renderSection('content') ?>
    </main>
    <script src="/js/kanban.js"></script>
</body>
</html>