<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 25px; max-width: 500px; margin: 0 auto;">
    <h1 style="margin-top: 0; color: #333; font-weight: 500; margin-bottom: 25px;">
        Edit Column
    </h1>

    <form method="post" action="/columns/<?= $column['id'] ?>/update">
        <?= csrf_field() ?>
        <input type="hidden" name="board_id" value="<?= $column['board_id'] ?>">

        <div style="margin-bottom: 20px;">
            <label style="
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: #333;
            ">
                Column Name
            </label>
            <input 
                type="text" 
                name="name" 
                value="<?= esc($column['name']) ?>" 
                required
                style="
                    width: 95%;
                    padding: 10px 15px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                    transition: border-color 0.2s;
                "
                onfocus="this.style.borderColor='#4a90e2'"
                onblur="this.style.borderColor='#ddd'"
            >
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <a href="/boards/<?= $column['board_id'] ?>" style="
                display: inline-flex;
                align-items: center;
                padding: 8px 16px;
                background: #f1f3f4;
                color: #333;
                text-decoration: none;
                border-radius: 4px;
                font-size: 14px;
                transition: background-color 0.2s;
            " onmouseover="this.style.backgroundColor='#e8eaed'" onmouseout="this.style.backgroundColor='#f1f3f4'">
                Cancel
            </a>
            <button type="submit" style="
                background: #4a90e2;
                color: white;
                border: none;
                border-radius: 4px;
                padding: 8px 20px;
                font-weight: 500;
                cursor: pointer;
                font-size: 14px;
                transition: background-color 0.2s;
            " onmouseover="this.style.backgroundColor='#3a7bc8'" onmouseout="this.style.backgroundColor='#4a90e2'">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>