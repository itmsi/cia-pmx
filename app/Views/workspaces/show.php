<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1200px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/workspaces" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Workspaces
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; color: #2c3e50;"><?= esc($workspace['name']) ?></h1>
                <?php if ($workspace['description']): ?>
                    <p style="margin: 5px 0 0 0; color: #5f6368;"><?= esc($workspace['description']) ?></p>
                <?php endif; ?>
            </div>
            <?php if ($isOwner): ?>
                <a href="/workspaces/<?= $workspace['id'] ?>/edit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Workspace
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div>
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 20px;">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">
                    Workspace Members (<?= count($users) ?>)
                </h3>

                <?php if (empty($users)): ?>
                    <p style="color: #666;">No members in this workspace.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($users as $user): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 6px;">
                                <div>
                                    <div style="font-weight: 600; color: #2c3e50;">
                                        <?= esc($user['full_name'] ?? $user['email']) ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: #5f6368;">
                                        <?= esc($user['email']) ?>
                                        <?php if ($user['role_name']): ?>
                                            <span style="margin-left: 8px; padding: 2px 8px; background: #e3f2fd; color: #1976d2; border-radius: 12px; font-size: 0.75rem;">
                                                <?= esc($user['role_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($isOwner && $user['user_id'] != session()->get('user_id')): ?>
                                    <form method="post" action="/workspaces/<?= $workspace['id'] ?>/users/<?= $user['user_id'] ?>/remove" style="display: inline;" onsubmit="return confirm('Remove this user from workspace?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" style="background: none; border: none; color: #d32f2f; cursor: pointer; padding: 4px 8px;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">Details</h3>
                
                <div style="margin-bottom: 15px;">
                    <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 4px;">Timezone</div>
                    <div style="font-weight: 600; color: #2c3e50;"><?= esc($workspace['timezone']) ?></div>
                </div>

                <div style="margin-bottom: 15px;">
                    <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 4px;">Created</div>
                    <div style="font-weight: 600; color: #2c3e50;">
                        <?= date('M d, Y', strtotime($workspace['created_at'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

