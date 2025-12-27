<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1200px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <a href="/projects/<?= $project['id'] ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                <i class="fas fa-arrow-left"></i> Back to Project
            </a>
            <h1 style="margin: 0; color: #2c3e50;">Sprints - <?= esc($project['name']) ?></h1>
        </div>
        <a href="/sprints/create?project_id=<?= $project['id'] ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Sprint
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
        <?php if (empty($sprints)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <i class="fas fa-calendar-alt" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                <p style="color: #666;">No sprints yet. Create your first sprint!</p>
            </div>
        <?php else: ?>
            <?php foreach ($sprints as $sprint): ?>
                <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <div>
                            <h3 style="margin: 0 0 5px 0; color: #2c3e50;">
                                <a href="/sprints/<?= $sprint['id'] ?>" style="color: inherit; text-decoration: none;">
                                    <?= esc($sprint['name']) ?>
                                </a>
                            </h3>
                            <span style="padding: 4px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
                                <?php
                                $statusColors = [
                                    'planned' => 'background: #e3f2fd; color: #1976d2;',
                                    'active' => 'background: #c8e6c9; color: #388e3c;',
                                    'completed' => 'background: #f5f5f5; color: #616161;'
                                ];
                                echo $statusColors[$sprint['status']] ?? '';
                                ?>
                            ">
                                <?= esc($sprint['status']) ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($sprint['goal']): ?>
                        <p style="color: #5f6368; margin: 10px 0; font-size: 0.9rem;"><?= esc($sprint['goal']) ?></p>
                    <?php endif; ?>

                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #5f6368; margin-bottom: 5px;">
                            <span><i class="fas fa-calendar"></i> Start:</span>
                            <span><?= date('M d, Y', strtotime($sprint['start_date'])) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #5f6368; margin-bottom: 5px;">
                            <span><i class="fas fa-calendar-check"></i> End:</span>
                            <span><?= date('M d, Y', strtotime($sprint['end_date'])) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #5f6368;">
                            <span><i class="fas fa-clock"></i> Duration:</span>
                            <span><?= $sprint['duration_weeks'] ?> week(s)</span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <a href="/sprints/<?= $sprint['id'] ?>" class="btn btn-outline" style="flex: 1; text-align: center;">
                            View
                        </a>
                        <a href="/sprints/<?= $sprint['id'] ?>/edit" class="btn btn-outline" style="flex: 1; text-align: center;">
                            Edit
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

