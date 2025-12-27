<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1000px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="margin: 0; color: #2c3e50;"><?= esc($user['full_name'] ?? $user['email']) ?></h1>
            <p style="margin: 5px 0 0 0; color: #5f6368;"><?= esc($user['email']) ?></p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit User
            </a>
            <a href="/users" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px;">
            <h2 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.2rem;">User Information</h2>
            <dl style="margin: 0;">
                <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;">
                    <dt style="font-weight: 600; color: #5f6368; margin-bottom: 5px; font-size: 0.9rem;">Email</dt>
                    <dd style="margin: 0; color: #2c3e50;"><?= esc($user['email']) ?></dd>
                </div>
                <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;">
                    <dt style="font-weight: 600; color: #5f6368; margin-bottom: 5px; font-size: 0.9rem;">Full Name</dt>
                    <dd style="margin: 0; color: #2c3e50;"><?= esc($user['full_name'] ?? '-') ?></dd>
                </div>
                <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;">
                    <dt style="font-weight: 600; color: #5f6368; margin-bottom: 5px; font-size: 0.9rem;">Phone</dt>
                    <dd style="margin: 0; color: #2c3e50;"><?= esc($user['phone'] ?? '-') ?></dd>
                </div>
                <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;">
                    <dt style="font-weight: 600; color: #5f6368; margin-bottom: 5px; font-size: 0.9rem;">Role</dt>
                    <dd style="margin: 0;">
                        <?php if ($user['role_name']): ?>
                            <span style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                <?= esc($user['role_name']) ?>
                            </span>
                        <?php else: ?>
                            <span style="color: #999;">No role assigned</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;">
                    <dt style="font-weight: 600; color: #5f6368; margin-bottom: 5px; font-size: 0.9rem;">Status</dt>
                    <dd style="margin: 0;">
                        <?php if ($user['status'] === 'active'): ?>
                            <span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                Active
                            </span>
                        <?php else: ?>
                            <span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                Inactive
                            </span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div style="margin-bottom: 0;">
                    <dt style="font-weight: 600; color: #5f6368; margin-bottom: 5px; font-size: 0.9rem;">Created At</dt>
                    <dd style="margin: 0; color: #2c3e50;"><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></dd>
                </div>
            </dl>
        </div>

        <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px;">
            <h2 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.2rem;">Workspaces</h2>
            <?php if (empty($workspaces)): ?>
                <p style="color: #999; margin: 0;">No workspaces assigned</p>
            <?php else: ?>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($workspaces as $workspace): ?>
                        <li style="padding: 10px; margin-bottom: 10px; background: #f8f9fa; border-radius: 4px;">
                            <a href="/workspaces/<?= $workspace['id'] ?>" style="color: #1a73e8; text-decoration: none; font-weight: 500;">
                                <?= esc($workspace['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px;">
        <h2 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.2rem;">Projects</h2>
        <?php if (empty($projects)): ?>
            <p style="color: #999; margin: 0;">No projects assigned</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #e0e0e0;">
                        <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Project</th>
                        <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Key</th>
                        <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Status</th>
                        <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 12px;">
                                <a href="/projects/<?= $project['id'] ?>" style="color: #1a73e8; text-decoration: none;">
                                    <?= esc($project['name']) ?>
                                </a>
                            </td>
                            <td style="padding: 12px; color: #5f6368; font-family: monospace;">
                                <?= esc($project['key']) ?>
                            </td>
                            <td style="padding: 12px;">
                                <span style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                    <?= esc(ucfirst($project['status'])) ?>
                                </span>
                            </td>
                            <td style="padding: 12px; color: #5f6368;">
                                <?= esc($project['project_role_id'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

