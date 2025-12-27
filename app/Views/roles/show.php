<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/roles" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; color: #2c3e50;"><?= esc($role['name']) ?></h1>
                <p style="margin: 5px 0 0 0; color: #5f6368;">
                    <code style="background: #f1f3f4; padding: 2px 6px; border-radius: 3px; font-size: 0.9em;"><?= esc($role['slug']) ?></code>
                </p>
            </div>
            <a href="/roles/<?= $role['id'] ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Role
            </a>
        </div>
    </div>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 20px;">
        <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">Description</h3>
        <p style="color: #5f6368; margin: 0;">
            <?= esc($role['description'] ?? 'No description provided.') ?>
        </p>
    </div>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
        <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">
            Permissions (<?= count($permissions) ?>)
        </h3>

        <?php if (empty($permissions)): ?>
            <div style="text-align: center; padding: 30px; color: #666;">
                <i class="fas fa-key" style="font-size: 36px; color: #ddd; margin-bottom: 10px;"></i>
                <p>No permissions assigned to this role.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px;">
                <?php foreach ($permissions as $permission): ?>
                    <div style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px; background: #f8f9fa;">
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 4px;">
                            <?= esc($permission['name']) ?>
                        </div>
                        <?php if ($permission['description']): ?>
                            <div style="font-size: 0.85rem; color: #5f6368; margin-top: 4px;">
                                <?= esc($permission['description']) ?>
                            </div>
                        <?php endif; ?>
                        <code style="display: block; font-size: 0.75rem; color: #666; margin-top: 6px; background: #fff; padding: 2px 6px; border-radius: 3px; width: fit-content;">
                            <?= esc($permission['slug']) ?>
                        </code>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

