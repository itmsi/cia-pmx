<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="margin-bottom: 30px;">
    <h1 style="color: #2c3e50; margin-bottom: 5px;"><?= esc($board['name']) ?></h1>
    <p style="color: #7f8c8d; margin-top: 0;">Manage your tasks and workflow</p>
</div>

<!-- Add Column Form -->
<form method="post" action="/columns/create" style="
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
">
    <?= csrf_field() ?>
    <input type="hidden" name="board_id" value="<?= $board['id'] ?>">
    <input 
        type="text" 
        name="name" 
        placeholder="Enter column name" 
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
            padding: 0 20px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        "
        onmouseover="this.style.backgroundColor='#3a7bc8'"
        onmouseout="this.style.backgroundColor='#4a90e2'"
    >
        Add Column
    </button>
</form>

<!-- Columns Container -->
<div style="
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding-bottom: 20px;
    margin-top: 10px;
    min-height: 400px;
">
    <?php foreach ($columns as $column): ?>
        <div class="column"
            data-column-id="<?= $column['id'] ?>" style="
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            width: 280px;
            min-width: 280px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: fit-content;
            max-height: calc(100vh - 250px);
        ">
            <!-- Column Header -->
            <div style="
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            ">
                <h3 style="
                    margin: 0;
                    font-size: 15px;
                    font-weight: 600;
                    color: #2c3e50;
                ">
                    <?= esc($column['name']) ?>
                </h3>
                <div style="display: flex; gap: 4px; align-items: center;">
                    <a href="/columns/<?= $column['id'] ?>/edit" 
                    style="
                        color: #5f6368;
                        text-decoration: none;
                        padding: 6px 12px;
                        border-radius: 4px;
                        background: #f8f9fa;
                        border: 1px solid #e0e0e0;
                        font-size: 13px;
                        transition: all 0.2s;
                        line-height: 1.2;
                        height: 32px;
                        display: inline-flex;
                        align-items: center;
                    " 
                    onmouseover="this.style.backgroundColor='#e8eaed'; this.style.borderColor='#d2d6dc';" 
                    onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#e0e0e0';">
                        Edit
                    </a>
                    <form method="post" 
                        action="/columns/<?= $column['id'] ?>/delete" 
                        style="margin: 0;"
                        onsubmit="return confirm('Delete this column and all its cards?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="board_id" value="<?= $board['id'] ?>">
                        <button type="submit" style="
                            background: #f8f9fa;
                            border: 1px solid #e0e0e0;
                            color: #d32f2f;
                            cursor: pointer;
                            padding: 6px 12px;
                            font-size: 13px;
                            border-radius: 4px;
                            transition: all 0.2s;
                            line-height: 1.2;
                            height: 32px;
                            display: inline-flex;
                            align-items: center;
                        " onmouseover="this.style.backgroundColor='#fce8e8'; this.style.borderColor='#f5c6cb';" 
                        onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#e0e0e0';">
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <!-- Add Card Form -->
            <form method="post" action="/cards/create" style="margin-top: 10px;margin-bottom: 10px">
                <?= csrf_field() ?>
                <input type="hidden" name="column_id" value="<?= $column['id'] ?>">
                <div style="display: flex; gap: 8px; flex-direction: column;">
                    <input 
                        type="text" 
                        name="title" 
                        placeholder="Enter card title..." 
                        required
                        style="
                            width: 100%;
                            padding: 10px 12px;
                            border: 1px solid #e0e0e0;
                            border-radius: 4px;
                            font-size: 13px;
                            transition: all 0.2s;
                        "
                        onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 2px rgba(74, 144, 226, 0.2)'"
                        onblur="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none'"
                    >
                    <div style="display: flex; gap: 8px; margin-top: 4px;">
                        <button 
                            type="submit"
                            style="
                                flex: 1;
                                padding: 8px 12px;
                                background: #4a90e2;
                                color: white;
                                border: none;
                                border-radius: 4px;
                                font-size: 13px;
                                font-weight: 500;
                                cursor: pointer;
                                transition: all 0.2s;
                            "
                            onmouseover="this.style.backgroundColor='#3a7bc8'"
                            onmouseout="this.style.backgroundColor='#4a90e2'"
                        >
                            Add Card
                        </button>
                        <button 
                            type="button"
                            onclick="this.closest('form').reset()"
                            style="
                                padding: 0 12px;
                                background: transparent;
                                color: #5f6368;
                                border: 1px solid #e0e0e0;
                                border-radius: 4px;
                                font-size: 20px;
                                cursor: pointer;
                                transition: all 0.2s;
                            "
                            onmouseover="this.style.borderColor='#bdc3c7'; this.style.color='#2c3e50'"
                            onmouseout="this.style.borderColor='#e0e0e0'; this.style.color='#5f6368'"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </form>

            <!-- Cards List -->
            <ul style="
                list-style: none;
                padding: 0;
                margin: 0;
                overflow-y: auto;
                flex-grow: 1;
                min-height: 20px;
            ">
                <?php if (empty($column['cards'])): ?>
                    <li style="
                        color: #95a5a6;
                        font-size: 13px;
                        text-align: center;
                        padding: 10px;
                        background: #f1f3f4;
                        border-radius: 4px;
                        margin-bottom: 10px;
                    ">
                        No cards
                    </li>
                <?php else: ?>
                    <?php foreach ($column['cards'] as $card): ?>
                        <li draggable="true" class="card" data-card-id="<?= $card['id'] ?>"
                            data-column-id="<?= $column['id'] ?>" style="
                            background: white;
                            border-radius: 6px;
                            padding: 12px;
                            margin-bottom: 10px;
                            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
                            border-left: 3px solid #4a90e2;
                        ">
                            <div style="margin-bottom: 8px; font-size: 14px; color: #2c3e50;">
                                <?= esc($card['title']) ?>
                            </div>
                            
                            <!-- Card Actions -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 8px; border-top: 1px solid #f0f0f0;">
                                <div style="display: flex; gap: 4px; font-size: 12px;">
                                    <a href="/cards/<?= $card['id'] ?>/edit" 
                                    style="
                                        color: #5f6368;
                                        text-decoration: none;
                                        padding: 4px 8px;
                                        border-radius: 4px;
                                        background: #f8f9fa;
                                        border: 1px solid #e0e0e0;
                                        transition: all 0.2s;
                                        line-height: 1;
                                    "
                                    onmouseover="this.style.backgroundColor='#e8eaed'; this.style.borderColor='#d2d6dc';" 
                                    onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#e0e0e0';">
                                        Edit
                                    </a>
                                    <form method="post" 
                                        action="/cards/<?= $card['id'] ?>/delete" 
                                        style="margin: 0;"
                                        onsubmit="return confirm('Delete this card?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="column_id" value="<?= $column['id'] ?>">
                                        <input type="hidden" name="board_id" value="<?= $board['id'] ?>">
                                        <button type="submit" style="
                                            background: #f8f9fa;
                                            border: 1px solid #e0e0e0;
                                            color: #d32f2f;
                                            cursor: pointer;
                                            padding: 4px 8px;
                                            font-size: 12px;
                                            border-radius: 4px;
                                            transition: all 0.2s;
                                            line-height: 1;
                                        " onmouseover="this.style.backgroundColor='#fce8e8'; this.style.borderColor='#f5c6cb';" 
                                        onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#e0e0e0';">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Move Buttons -->
                                <div style="display: flex; gap: 4px;">
                                    <?php if (isset($columnOrder[$column['position'] - 1])): ?>
                                        <form method="post" action="/cards/move" style="margin: 0;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="card_id" value="<?= $card['id'] ?>">
                                            <input type="hidden" name="from_column" value="<?= $column['id'] ?>">
                                            <input type="hidden" name="to_column" value="<?= $columnOrder[$column['position'] - 1] ?>">
                                            <input type="hidden" name="position" value="0">
                                            <button type="submit" style="
                                                background: #f1f3f4;
                                                border: none;
                                                border-radius: 3px;
                                                width: 24px;
                                                height: 24px;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                cursor: pointer;
                                                color: #5f6368;
                                                transition: all 0.2s;
                                            " onmouseover="this.style.backgroundColor='#e8eaed'; this.style.color='#1a73e8'" 
                                            onmouseout="this.style.backgroundColor='#f1f3f4'; this.style.color='#5f6368'">
                                                ←
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (isset($columnOrder[$column['position'] + 1])): ?>
                                        <form method="post" action="/cards/move" style="margin: 0;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="card_id" value="<?= $card['id'] ?>">
                                            <input type="hidden" name="from_column" value="<?= $column['id'] ?>">
                                            <input type="hidden" name="to_column" value="<?= $columnOrder[$column['position'] + 1] ?>">
                                            <input type="hidden" name="position" value="0">
                                            <button type="submit" style="
                                                background: #f1f3f4;
                                                border: none;
                                                border-radius: 3px;
                                                width: 24px;
                                                height: 24px;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                cursor: pointer;
                                                color: #5f6368;
                                                transition: all 0.2s;
                                            " onmouseover="this.style.backgroundColor='#e8eaed'; this.style.color='#1a73e8'" 
                                            onmouseout="this.style.backgroundColor='#f1f3f4'; this.style.color='#5f6368'">
                                                →
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>