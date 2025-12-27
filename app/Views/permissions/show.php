<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/permissions" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Permissions
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; color: #2c3e50;"><?= esc($permission['name']) ?></h1>
                <p style="margin: 5px 0 0 0; color: #5f6368;">
                    <code style="background: #f1f3f4; padding: 2px 6px; border-radius: 3px; font-size: 0.9em;"><?= esc($permission['slug']) ?></code>
                </p>
            </div>
            <a href="/permissions/<?= $permission['id'] ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Permission
            </a>
        </div>
    </div>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
        <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">Description</h3>
        <p style="color: #5f6368; margin: 0;">
            <?= esc($permission['description'] ?? 'No description provided.') ?>
        </p>
    </div>
</div>

<?= $this->endSection() ?>

