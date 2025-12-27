<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1200px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects/<?= $project['id'] ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h1 style="margin: 0; color: #2c3e50;">
                    <?= esc($issue['title']) ?>
                </h1>
                <div style="margin-top: 8px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <code style="background: #f1f3f4; padding: 4px 8px; border-radius: 4px; font-size: 0.9rem; color: #5f6368;">
                        <?= esc($issue['issue_key'] ?? 'ISSUE-' . $issue['id']) ?>
                    </code>
                    <span style="padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: #e3f2fd; color: #1976d2;">
                        <?= ucfirst($issue['issue_type'] ?? 'Task') ?>
                    </span>
                    <?php
                    $priorityColors = [
                        'lowest' => ['bg' => '#f5f5f5', 'text' => '#666'],
                        'low' => ['bg' => '#e8f5e9', 'text' => '#2e7d32'],
                        'medium' => ['bg' => '#fff3e0', 'text' => '#e65100'],
                        'high' => ['bg' => '#ffe0b2', 'text' => '#f57c00'],
                        'critical' => ['bg' => '#ffcdd2', 'text' => '#c62828'],
                    ];
                    $priority = strtolower($issue['priority'] ?? 'medium');
                    $color = $priorityColors[$priority] ?? $priorityColors['medium'];
                    ?>
                    <span style="padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: <?= $color['bg'] ?>; color: <?= $color['text'] ?>;">
                        <?= ucfirst($issue['priority'] ?? 'Medium') ?>
                    </span>
                </div>
            </div>
            <a href="/issues/<?= $issue['id'] ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Issue
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Main Content -->
        <div>
            <!-- Description -->
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 20px;">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">Description</h3>
                <?php if ($issue['description']): ?>
                    <div style="color: #2c3e50; line-height: 1.6; white-space: pre-wrap;"><?= esc($issue['description']) ?></div>
                <?php else: ?>
                    <p style="color: #666; font-style: italic;">No description provided.</p>
                <?php endif; ?>
            </div>

            <!-- Labels -->
            <?php if (!empty($labels)): ?>
                <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 15px;">Labels</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($labels as $label): ?>
                            <span style="padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 500; background: <?= esc($label['color'] ?? '#e3f2fd') ?>; color: #2c3e50; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fas fa-tag"></i>
                                <?= esc($label['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Sub-tasks -->
            <?php if (!empty($subTasks)): ?>
                <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">
                        Sub-tasks (<?= count($subTasks) ?>)
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($subTasks as $subTask): ?>
                            <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8f9fa; border-radius: 6px;">
                                <input type="checkbox" <?= $subTask['status'] === 'completed' ? 'checked' : '' ?> disabled style="cursor: not-allowed;">
                                <div style="flex: 1;">
                                    <a href="/issues/<?= $subTask['id'] ?>" style="color: #1a73e8; text-decoration: none; font-weight: 500;">
                                        <?= esc($subTask['title']) ?>
                                    </a>
                                    <?php if ($subTask['issue_key']): ?>
                                        <code style="margin-left: 8px; background: #f1f3f4; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; color: #5f6368;">
                                            <?= esc($subTask['issue_key']) ?>
                                        </code>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Comments Section -->
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">
                    Comments (<?= count($comments) ?>)
                </h3>

                <!-- Add Comment Form -->
                <form method="post" action="/comments" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e0e0e0;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="issue_id" value="<?= $issue['id'] ?>">
                    <div style="margin-bottom: 12px;">
                        <textarea 
                            name="content" 
                            rows="3"
                            placeholder="Add a comment..."
                            required
                            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical; font-family: inherit;"
                        ></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary" style="padding: 8px 20px;">
                            <i class="fas fa-comment"></i> Add Comment
                        </button>
                    </div>
                </form>

                <!-- Comments List -->
                <?php if (empty($comments)): ?>
                    <div style="text-align: center; padding: 30px; color: #666;">
                        <i class="fas fa-comments" style="font-size: 36px; color: #ddd; margin-bottom: 10px;"></i>
                        <p>No comments yet. Be the first to comment!</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <?php foreach ($comments as $comment): ?>
                            <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 3px solid #4a90e2;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <div>
                                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 4px;">
                                            <?= esc($comment['user_name'] ?? $comment['user_email'] ?? 'Unknown User') ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: #5f6368;">
                                            <?= date('M d, Y H:i', strtotime($comment['created_at'])) ?>
                                            <?php if ($comment['updated_at'] != $comment['created_at']): ?>
                                                <span style="margin-left: 8px; color: #999;">(edited)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (($comment['user_id'] ?? null) == session()->get('user_id')): ?>
                                        <div style="display: flex; gap: 8px;">
                                            <form method="post" action="/comments/<?= $comment['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Delete this comment?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" style="background: none; border: none; color: #d32f2f; cursor: pointer; padding: 4px 8px; font-size: 0.85rem;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div style="color: #2c3e50; line-height: 1.6; white-space: pre-wrap;">
                                    <?= esc($comment['content']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 20px;">
                <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">Details</h3>
                
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 6px;">Assignee</div>
                    <div style="font-weight: 600; color: #2c3e50;">
                        <?= $issue['assignee_name'] ?? 'Unassigned' ?>
                    </div>
                    <?php if (!$issue['assignee_id']): ?>
                        <form method="post" action="/issues/<?= $issue['id'] ?>/assign" style="margin-top: 8px;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">
                                Assign to me
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 6px;">Reporter</div>
                    <div style="font-weight: 600; color: #2c3e50;">
                        <?= $issue['reporter_name'] ?? 'Unknown' ?>
                    </div>
                </div>

                <?php if ($issue['due_date']): ?>
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 6px;">Due Date</div>
                        <div style="font-weight: 600; color: <?= strtotime($issue['due_date']) < time() ? '#d32f2f' : '#2c3e50' ?>;">
                            <?= date('M d, Y', strtotime($issue['due_date'])) ?>
                            <?php if (strtotime($issue['due_date']) < time() && !$issue['status'] === 'completed'): ?>
                                <span style="margin-left: 8px; padding: 2px 6px; background: #ffcdd2; color: #c62828; border-radius: 3px; font-size: 0.75rem;">Overdue</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($issue['estimation']): ?>
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 6px;">Estimation</div>
                        <div style="font-weight: 600; color: #2c3e50;">
                            <?= esc($issue['estimation']) ?> <?= $issue['estimation'] == 1 ? 'point' : 'points' ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom: 20px;">
                    <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 6px;">Project</div>
                    <div>
                        <a href="/projects/<?= $project['id'] ?>" style="color: #1a73e8; text-decoration: none; font-weight: 600;">
                            <?= esc($project['name']) ?>
                        </a>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="font-size: 0.85rem; color: #5f6368; margin-bottom: 6px;">Created</div>
                    <div style="font-weight: 600; color: #2c3e50;">
                        <?= date('M d, Y', strtotime($issue['created_at'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

