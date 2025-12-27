<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 style="margin: 0; color: #2c3e50;">Projects</h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;">Manage your projects</p>
    </div>
    <a href="/projects/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Project
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (!empty($workspaces)): ?>
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 15px; margin-bottom: 20px;">
        <form method="get" action="/projects" style="display: flex; gap: 10px; align-items: center;">
            <label style="font-weight: 600; color: #2c3e50;">Filter by Workspace:</label>
            <select name="workspace_id" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" onchange="this.form.submit()">
                <option value="">All Workspaces</option>
                <?php foreach ($workspaces as $ws): ?>
                    <option value="<?= $ws['id'] ?>" <?= ($selectedWorkspace ?? null) == $ws['id'] ? 'selected' : '' ?>>
                        <?= esc($ws['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
    <?php if (empty($projects)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <i class="fas fa-folder-open" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
            <p style="color: #666;">No projects yet. Create your first project!</p>
        </div>
    <?php else: ?>
        <?php foreach ($projects as $project): ?>
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)'">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                    <div>
                        <h3 style="margin: 0 0 5px 0; color: #2c3e50;">
                            <a href="/projects/<?= $project['id'] ?>" style="color: inherit; text-decoration: none;">
                                <?= esc($project['name']) ?>
                            </a>
                        </h3>
                        <code style="background: #f1f3f4; padding: 2px 6px; border-radius: 3px; font-size: 0.85rem; color: #5f6368;">
                            <?= esc($project['project_key']) ?>
                        </code>
                    </div>
                    <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; background: <?= 
                        $project['status'] === 'active' ? '#d4edda' : 
                        ($project['status'] === 'completed' ? '#cfe2ff' : 
                        ($project['status'] === 'on_hold' ? '#fff3cd' : '#f8d7da')) ?>; 
                        color: <?= 
                        $project['status'] === 'active' ? '#155724' : 
                        ($project['status'] === 'completed' ? '#084298' : 
                        ($project['status'] === 'on_hold' ? '#664d03' : '#721c24')) ?>;">
                        <?= ucfirst(str_replace('_', ' ', $project['status'])) ?>
                    </span>
                </div>

                <?php if ($project['description']): ?>
                    <p style="margin: 10px 0; color: #5f6368; font-size: 0.9rem; line-height: 1.4;">
                        <?= esc(substr($project['description'], 0, 100)) ?><?= strlen($project['description']) > 100 ? '...' : '' ?>
                    </p>
                <?php endif; ?>

                <div style="display: flex; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                    <a href="/projects/<?= $project['id'] ?>" class="btn btn-outline" style="flex: 1; text-align: center; padding: 8px;">
                        View
                    </a>
                    <a href="/projects/<?= $project['id'] ?>/edit" class="btn btn-outline" style="flex: 1; text-align: center; padding: 8px;">
                        Edit
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

