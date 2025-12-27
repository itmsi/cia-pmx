<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h1>My Boards</h1>
<p>Boards you own and manage.</p>

<!-- CREATE BOARD FORM -->
<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 30px;">
    <h3 style="margin-top: 0; color: #333; font-weight: 500;">Create New Board</h3>
    <form method="post" action="/boards" style="display: flex; gap: 10px; align-items: center;">
        <?= csrf_field() ?>
        <input 
            type="text" 
            name="name" 
            placeholder="Enter board name"
            required
            style="
                flex: 1;
                padding: 10px 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
                transition: border-color 0.2s;
            "
            onfocus="this.style.borderColor='#4a90e2'"
            onblur="this.style.borderColor='#ddd'"
        >
        <button 
            type="submit" 
            style="
                background: #4a90e2;
                color: white;
                border: none;
                border-radius: 4px;
                padding: 10px 20px;
                font-weight: 500;
                cursor: pointer;
                transition: background-color 0.2s;
            "
            onmouseover="this.style.backgroundColor='#3a7bc8'"
            onmouseout="this.style.backgroundColor='#4a90e2'"
        >
            Create Board
        </button>
    </form>
</div>

<!-- BOARD LIST -->
<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-top: 20px;">
    <h3 style="margin-top: 0; color: #333; font-weight: 500; margin-bottom: 20px;">Your Boards</h3>
    
    <?php if (empty($boards)): ?>
        <div style="text-align: center; padding: 30px; color: #666; background: #f9f9f9; border-radius: 4px;">
            <p style="margin: 0;">No boards created yet. Create your first board above!</p>
        </div>
    <?php else: ?>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <?php foreach ($boards as $board): ?>
                <li style="
                    display: flex;
                    align-items: center;
                    padding: 12px 15px;
                    border-bottom: 1px solid #eee;
                    transition: background-color 0.2s;
                " onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='#fff'">
                    <a href="/boards/<?= $board['id'] ?>" style="
                        flex: 1;
                        color: #2c3e50;
                        text-decoration: none;
                        font-weight: 500;
                        transition: color 0.2s;
                    " onmouseover="this.style.color='#1a73e8'" onmouseout="this.style.color='#2c3e50'">
                        <?= esc($board['name']) ?>
                    </a>

                    <div style="display: flex; gap: 10px;">
                        <a href="/boards/<?= $board['id'] ?>/edit" style="
                            color: #5f6368;
                            text-decoration: none;
                            font-size: 14px;
                            padding: 6px 12px;
                            border-radius: 4px;
                            transition: all 0.2s;
                        " onmouseover="this.style.backgroundColor='#f1f3f4'; this.style.color='#1a73e8'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#5f6368'">
                            Edit
                        </a>

                        <form method="post" action="/boards/delete/<?= $board['id'] ?>" style="margin: 0;">
                            <?= csrf_field() ?>
                            <button type="submit" style="
                                background: none;
                                border: none;
                                color: #d32f2f;
                                cursor: pointer;
                                font-size: 14px;
                                padding: 6px 12px;
                                border-radius: 4px;
                                transition: all 0.2s;
                            " onmouseover="this.style.backgroundColor='#f1f3f4'; this.style.color='#b71c1c'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#d32f2f'" onclick="return confirm('Are you sure you want to delete this board? This action cannot be undone.')">
                                Delete
                            </button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
