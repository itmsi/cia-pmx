<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1200px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/sprints?project_id=<?= $project['id'] ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Sprints
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; color: #2c3e50;">
                    <?= esc($sprint['name']) ?>
                    <span style="padding: 4px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-left: 10px;
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
                </h1>
                <p style="margin: 5px 0 0 0; color: #5f6368;">
                    Project: <a href="/projects/<?= $project['id'] ?>" style="color: #1a73e8;"><?= esc($project['name']) ?></a>
                </p>
            </div>
            <div style="display: flex; gap: 10px;">
                <?php if ($sprint['status'] === 'planned'): ?>
                    <form method="post" action="/sprints/<?= $sprint['id'] ?>/start" style="display: inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-play"></i> Start Sprint
                        </button>
                    </form>
                <?php elseif ($sprint['status'] === 'active'): ?>
                    <form method="post" action="/sprints/<?= $sprint['id'] ?>/complete" style="display: inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Complete Sprint
                        </button>
                    </form>
                <?php endif; ?>
                <a href="/sprints/<?= $sprint['id'] ?>/edit" class="btn btn-outline">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div>
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 20px;">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">
                    Sprint Details
                </h3>

                <?php if ($sprint['goal']): ?>
                    <div style="margin-bottom: 20px;">
                        <strong style="color: #2c3e50;">Goal:</strong>
                        <p style="color: #5f6368; margin: 5px 0 0 0;"><?= esc($sprint['goal']) ?></p>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <strong style="color: #2c3e50; display: block; margin-bottom: 5px;">Start Date:</strong>
                        <span style="color: #5f6368;"><?= date('M d, Y', strtotime($sprint['start_date'])) ?></span>
                    </div>
                    <div>
                        <strong style="color: #2c3e50; display: block; margin-bottom: 5px;">End Date:</strong>
                        <span style="color: #5f6368;"><?= date('M d, Y', strtotime($sprint['end_date'])) ?></span>
                    </div>
                    <div>
                        <strong style="color: #2c3e50; display: block; margin-bottom: 5px;">Duration:</strong>
                        <span style="color: #5f6368;"><?= $sprint['duration_weeks'] ?> week(s)</span>
                    </div>
                    <div>
                        <strong style="color: #2c3e50; display: block; margin-bottom: 5px;">Status:</strong>
                        <span style="color: #5f6368; text-transform: capitalize;"><?= esc($sprint['status']) ?></span>
                    </div>
                </div>
            </div>

            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">
                    Issues in Sprint (<?= count($issues) ?>)
                </h3>

                <?php if (empty($issues)): ?>
                    <p style="color: #666;">No issues in this sprint.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($issues as $issue): ?>
                            <div style="padding: 12px; background: #f8f9fa; border-radius: 6px;">
                                <a href="/issues/<?= $issue['id'] ?>" style="color: #1a73e8; text-decoration: none; font-weight: 600;">
                                    <?= esc($issue['issue_key']) ?>: <?= esc($issue['title']) ?>
                                </a>
                                <?php if ($issue['estimation']): ?>
                                    <span style="margin-left: 10px; padding: 2px 8px; background: #e3f2fd; color: #1976d2; border-radius: 12px; font-size: 0.75rem;">
                                        <?= $issue['estimation'] ?> SP
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">
                    Sprint Capacity
                </h3>

                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="color: #5f6368;">Total:</span>
                        <strong style="color: #2c3e50;"><?= $capacity['total'] ?> SP</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="color: #5f6368;">Completed:</span>
                        <strong style="color: #388e3c;"><?= $capacity['completed'] ?> SP</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="color: #5f6368;">In Progress:</span>
                        <strong style="color: #f57c00;"><?= $capacity['in_progress'] ?> SP</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span style="color: #5f6368;">To Do:</span>
                        <strong style="color: #1976d2;"><?= $capacity['todo'] ?> SP</strong>
                    </div>
                    <div style="background: #f0f0f0; border-radius: 4px; height: 20px; overflow: hidden;">
                        <div style="background: #4caf50; height: 100%; width: <?= $capacity['completion_percentage'] ?>%; transition: width 0.3s;"></div>
                    </div>
                    <div style="text-align: center; margin-top: 5px; font-weight: 600; color: #2c3e50;">
                        <?= $capacity['completion_percentage'] ?>% Complete
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

