<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    <div style="
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    ">
        <h1 style="
            color: #2c3e50;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        ">Activity Log</h1>
    </div>

    <div style="
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
    ">
        <ul style="
            list-style: none;
            margin: 0;
            padding: 0;
        ">
            <?php if (empty($logs)): ?>
                <li style="
                    padding: 20px;
                    text-align: center;
                    color: #7f8c8d;
                    font-style: italic;
                ">
                    No activity logs found.
                </li>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <li style="
                        padding: 15px 20px;
                        border-bottom: 1px solid #f5f5f5;
                        transition: background-color 0.2s;
                    " onmouseover="this.style.backgroundColor='#f9f9f9'" 
                      onmouseout="this.style.backgroundColor='#fff'">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="
                                    display: inline-block;
                                    background: #f1f3f4;
                                    color: #5f6368;
                                    font-size: 12px;
                                    font-weight: 500;
                                    padding: 2px 8px;
                                    border-radius: 12px;
                                    margin-right: 10px;
                                    text-transform: capitalize;
                                "><?= esc(ucfirst($log['action'])) ?></span>
                                
                                <span style="
                                    color: #2c3e50;
                                    font-weight: 500;
                                "><?= esc(ucfirst($log['entity_type'])) ?></span>
                                
                                <?php if ($log['description']): ?>
                                    <span style="color: #666; margin-left: 5px;">
                                        <?= esc($log['description']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div style="
                                color: #95a5a6;
                                font-size: 13px;
                                white-space: nowrap;
                                margin-left: 15px;
                            ">
                                <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
<?php if ($pager->getPageCount() > 1) : ?>
<div class="pagination-container">
    <?= $pager->links('default', 'custom_pager') ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 25px 0 0;
        justify-content: center;
        gap: 5px;
    }
    
    .pagination .page-item {
        margin: 0 2px;
    }
    
    .pagination .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        background: #fff;
        color: #5f6368;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .pagination .page-link:hover {
        background: #f1f3f4;
        border-color: #d2d6dc;
        color: #1a73e8;
    }
    
    .pagination .active .page-link {
        background: #4a90e2;
        border-color: #4a90e2;
        color: white;
        font-weight: 600;
    }
    
    .pagination .disabled .page-link {
        color: #bdc3c7;
        background: #f8f9fa;
        border-color: #eee;
        cursor: not-allowed;
    }
    
    .pagination .page-link span[aria-hidden="true"] {
        font-size: 12px;
        line-height: 1;
    }
    
    .pagination-container {
        margin-top: 30px;
        padding: 15px 0;
        border-top: 1px solid #eee;
    }
</style>
<?= $this->endSection() ?>