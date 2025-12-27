<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 style="margin: 0; color: #2c3e50;">Workspaces</h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;">Manage your workspaces and teams</p>
    </div>
    <a href="/workspaces/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Workspace
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
    <?php if (empty($workspaces)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <i class="fas fa-building" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
            <p style="color: #666;">No workspaces yet. Create your first workspace!</p>
        </div>
    <?php else: ?>
        <?php foreach ($workspaces as $workspace): ?>
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)'">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div>
                        <h3 style="margin: 0 0 5px 0; color: #2c3e50;">
                            <a href="/workspaces/<?= $workspace['id'] ?>" style="color: inherit; text-decoration: none;">
                                <?= esc($workspace['name']) ?>
                            </a>
                        </h3>
                        <?php if ($workspace['description']): ?>
                            <p style="margin: 0; color: #5f6368; font-size: 0.9rem;">
                                <?= esc(substr($workspace['description'], 0, 100)) ?><?= strlen($workspace['description']) > 100 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                    <a href="/workspaces/<?= $workspace['id'] ?>" class="btn btn-outline" style="flex: 1; text-align: center; padding: 8px;">
                        View
                    </a>
                    <a href="/workspaces/<?= $workspace['id'] ?>/edit" class="btn btn-outline" style="flex: 1; text-align: center; padding: 8px;">
                        Edit
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

