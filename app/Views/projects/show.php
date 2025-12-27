<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1200px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; color: #2c3e50;">
                    <?= esc($project['name']) ?>
                    <code style="background: #f1f3f4; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; margin-left: 10px; color: #5f6368;">
                        <?= esc($project['project_key']) ?>
                    </code>
                </h1>
                <?php if ($project['description']): ?>
                    <p style="margin: 5px 0 0 0; color: #5f6368;"><?= esc($project['description']) ?></p>
                <?php endif; ?>
            </div>
            <a href="/projects/<?= $project['id'] ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Project
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div>
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 20px;">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">
                    Project Members (<?= count($users) ?>)
                </h3>

                <?php if (empty($users)): ?>
                    <p style="color: #666;">No members in this project.</p>
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
                                    <form method="post" action="/projects/<?= $project['id'] ?>/users/<?= $user['user_id'] ?>/remove" style="display: inline;" onsubmit="return confirm('Remove this user from project?')">
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

            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; color: #2c3e50;">Issues</h3>
                    <a href="/issues/create?project_id=<?= $project['id'] ?>" class="btn btn-primary" style="padding: 8px 16px;">
                        <i class="fas fa-plus"></i> Create Issue
                    </a>
                </div>
                <p style="color: #666;">View all issues for this project.</p>
                <a href="/issues?project_id=<?= $project['id'] ?>" class="btn btn-outline" style="margin-top: 10px;">
                    View All Issues
                </a>
            </div>
        </div>

        <div>
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">Details</h3>
                
                <div style="margin-bottom: 15px;">
                    <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 4px;">Status</div>
                    <div>
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
                </div>

                <div style="margin-bottom: 15px;">
                    <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 4px;">Visibility</div>
                    <div style="font-weight: 600; color: #2c3e50;">
                        <?= ucfirst($project['visibility']) ?>
                    </div>
                </div>

                <?php if ($project['start_date']): ?>
                    <div style="margin-bottom: 15px;">
                        <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 4px;">Start Date</div>
                        <div style="font-weight: 600; color: #2c3e50;">
                            <?= date('M d, Y', strtotime($project['start_date'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($project['end_date']): ?>
                    <div style="margin-bottom: 15px;">
                        <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 4px;">End Date</div>
                        <div style="font-weight: 600; color: #2c3e50;">
                            <?= date('M d, Y', strtotime($project['end_date'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom: 15px;">
                    <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 4px;">Created</div>
                    <div style="font-weight: 600; color: #2c3e50;">
                        <?= date('M d, Y', strtotime($project['created_at'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

