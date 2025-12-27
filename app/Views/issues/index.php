<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 style="margin: 0; color: #2c3e50;">Issues</h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;">Manage your tasks and issues</p>
    </div>
    <?php if (!empty($projectId)): ?>
        <a href="/issues/create?project_id=<?= $projectId ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Issue
        </a>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px;">
    <?php if (empty($issues)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-tasks" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
            <p>No issues yet. Create your first issue!</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e0e0e0;">
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Key</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Title</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Type</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Priority</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Assignee</th>
                    <th style="text-align: right; padding: 12px; font-weight: 600; color: #2c3e50;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issues as $issue): ?>
                    <tr style="border-bottom: 1px solid #f0f0f0; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='#fff'">
                        <td style="padding: 12px;">
                            <a href="/issues/<?= $issue['id'] ?>" style="color: #1a73e8; text-decoration: none; font-weight: 500; font-family: monospace;">
                                <?= esc($issue['issue_key'] ?? 'ISSUE-' . $issue['id']) ?>
                            </a>
                        </td>
                        <td style="padding: 12px;">
                            <a href="/issues/<?= $issue['id'] ?>" style="color: #2c3e50; text-decoration: none;">
                                <?= esc($issue['title']) ?>
                            </a>
                        </td>
                        <td style="padding: 12px;">
                            <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: #e3f2fd; color: #1976d2;">
                                <?= ucfirst($issue['issue_type'] ?? 'Task') ?>
                            </span>
                        </td>
                        <td style="padding: 12px;">
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
                            <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: <?= $color['bg'] ?>; color: <?= $color['text'] ?>;">
                                <?= ucfirst($issue['priority'] ?? 'Medium') ?>
                            </span>
                        </td>
                        <td style="padding: 12px; color: #5f6368;">
                            <?= $issue['assignee_name'] ?? 'Unassigned' ?>
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            <a href="/issues/<?= $issue['id'] ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

