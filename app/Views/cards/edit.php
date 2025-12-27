<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <h1 style="
        color: #2c3e50;
        margin-bottom: 25px;
        font-size: 24px;
        font-weight: 600;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    ">Edit Card</h1>

    <form method="post" action="/cards/<?= $card['id'] ?>/update" style="
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    ">
        <?= csrf_field() ?>

        <input type="hidden" name="column_id" value="<?= $card['column_id'] ?>">
        <input type="hidden" name="board_id" value="<?= $card['board_id'] ?? '' ?>">

        <div style="margin-bottom: 20px;">
            <label style="
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: #2c3e50;
                font-size: 14px;
            ">Card Title</label>
            <input 
                type="text" 
                name="title" 
                value="<?= esc($card['title']) ?>" 
                required
                style="
                    width: 100%;
                    padding: 10px 12px;
                    border: 1px solid #e0e0e0;
                    border-radius: 4px;
                    font-size: 14px;
                    transition: all 0.2s;
                "
                onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 2px rgba(74, 144, 226, 0.2)'"
                onblur="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none'"
            >
        </div>

        <div style="
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        ">
            <a href="javascript:history.back()" style="
                padding: 8px 16px;
                background: #f8f9fa;
                border: 1px solid #e0e0e0;
                border-radius: 4px;
                color: #5f6368;
                text-decoration: none;
                font-size: 14px;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                height: 36px;
            " onmouseover="this.style.backgroundColor='#e8eaed'; this.style.borderColor='#d2d6dc';" 
              onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#e0e0e0';">
                Cancel
            </a>
            <button type="submit" style="
                padding: 0 16px;
                background: #4a90e2;
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
                height: 36px;
                display: inline-flex;
                align-items: center;
            " onmouseover="this.style.backgroundColor='#3a7bc8'" 
              onmouseout="this.style.backgroundColor='#4a90e2'">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>