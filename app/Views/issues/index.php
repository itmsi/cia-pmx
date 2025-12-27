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

<?php if (session()->getFlashdata('error')): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<!-- Filter Section -->
<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="margin: 0; color: #2c3e50; font-size: 1.1rem;">
            <i class="fas fa-filter"></i> Filters
        </h3>
        <button type="button" onclick="toggleFilterForm()" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">
            <i class="fas fa-chevron-<?= !empty($filters) ? 'up' : 'down' ?>" id="filterToggleIcon"></i> Toggle Filters
        </button>
    </div>

    <form method="GET" action="/issues" id="filterForm" style="display: <?= !empty($filters) ? 'block' : 'none' ?>;">
        <?php if ($projectId): ?>
            <input type="hidden" name="project_id" value="<?= $projectId ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
            <!-- Search -->
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2c3e50; font-size: 0.9rem;">Search</label>
                <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>" placeholder="Search issues..." style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.9rem;">
            </div>

            <!-- Status/Column -->
            <?php if (!empty($columns)): ?>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2c3e50; font-size: 0.9rem;">Status</label>
                <select name="column_id" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.9rem;">
                    <option value="">All Status</option>
                    <?php foreach ($columns as $column): ?>
                        <option value="<?= $column['id'] ?>" <?= (isset($filters['column_id']) && $filters['column_id'] == $column['id']) ? 'selected' : '' ?>>
                            <?= esc($column['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Priority -->
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2c3e50; font-size: 0.9rem;">Priority</label>
                <select name="priority" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.9rem;">
                    <option value="">All Priorities</option>
                    <option value="lowest" <?= (isset($filters['priority']) && $filters['priority'] == 'lowest') ? 'selected' : '' ?>>Lowest</option>
                    <option value="low" <?= (isset($filters['priority']) && $filters['priority'] == 'low') ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= (isset($filters['priority']) && $filters['priority'] == 'medium') ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= (isset($filters['priority']) && $filters['priority'] == 'high') ? 'selected' : '' ?>>High</option>
                    <option value="critical" <?= (isset($filters['priority']) && $filters['priority'] == 'critical') ? 'selected' : '' ?>>Critical</option>
                </select>
            </div>

            <!-- Assignee -->
            <?php if (!empty($projectUsers)): ?>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2c3e50; font-size: 0.9rem;">Assignee</label>
                <select name="assignee_id" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.9rem;">
                    <option value="">All Assignees</option>
                    <option value="null" <?= (isset($filters['assignee_id']) && $filters['assignee_id'] === 'null') ? 'selected' : '' ?>>Unassigned</option>
                    <?php foreach ($projectUsers as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= (isset($filters['assignee_id']) && $filters['assignee_id'] == $user['id']) ? 'selected' : '' ?>>
                            <?= esc($user['full_name'] ?? $user['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Label -->
            <?php if (!empty($labels)): ?>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2c3e50; font-size: 0.9rem;">Label</label>
                <select name="label_id" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.9rem;">
                    <option value="">All Labels</option>
                    <?php foreach ($labels as $label): ?>
                        <option value="<?= $label['id'] ?>" <?= (isset($filters['label_id']) && $filters['label_id'] == $label['id']) ? 'selected' : '' ?>>
                            <?= esc($label['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Issue Type -->
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2c3e50; font-size: 0.9rem;">Type</label>
                <select name="issue_type" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.9rem;">
                    <option value="">All Types</option>
                    <option value="task" <?= (isset($filters['issue_type']) && $filters['issue_type'] == 'task') ? 'selected' : '' ?>>Task</option>
                    <option value="bug" <?= (isset($filters['issue_type']) && $filters['issue_type'] == 'bug') ? 'selected' : '' ?>>Bug</option>
                    <option value="story" <?= (isset($filters['issue_type']) && $filters['issue_type'] == 'story') ? 'selected' : '' ?>>Story</option>
                    <option value="epic" <?= (isset($filters['issue_type']) && $filters['issue_type'] == 'epic') ? 'selected' : '' ?>>Epic</option>
                    <option value="sub_task" <?= (isset($filters['issue_type']) && $filters['issue_type'] == 'sub_task') ? 'selected' : '' ?>>Sub Task</option>
                </select>
            </div>

            <!-- Due Date From -->
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2c3e50; font-size: 0.9rem;">Due Date From</label>
                <input type="date" name="due_date_from" value="<?= esc($filters['due_date_from'] ?? '') ?>" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.9rem;">
            </div>

            <!-- Due Date To -->
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2c3e50; font-size: 0.9rem;">Due Date To</label>
                <input type="date" name="due_date_to" value="<?= esc($filters['due_date_to'] ?? '') ?>" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.9rem;">
            </div>

            <!-- Overdue -->
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2c3e50; font-size: 0.9rem;">Overdue</label>
                <select name="due_date_overdue" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.9rem;">
                    <option value="">All</option>
                    <option value="1" <?= (isset($filters['due_date_overdue']) && $filters['due_date_overdue'] == '1') ? 'selected' : '' ?>>Overdue Only</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">
                <i class="fas fa-search"></i> Apply Filters
            </button>
            <a href="/issues<?= $projectId ? '?project_id=' . $projectId : '' ?>" class="btn btn-outline" style="padding: 8px 16px;">
                <i class="fas fa-times"></i> Clear
            </a>
        </div>
    </form>
</div>

<!-- Saved Filters Section -->
<?php if (!empty($savedFilters)): ?>
<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px;">
    <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 1.1rem;">
        <i class="fas fa-bookmark"></i> Saved Filters
    </h3>
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        <?php foreach ($savedFilters as $savedFilter): ?>
            <div style="display: flex; align-items: center; gap: 8px; background: #f8f9fa; padding: 8px 12px; border-radius: 4px; border: 1px solid #e0e0e0;">
                <?php if ($savedFilter['is_default']): ?>
                    <i class="fas fa-star" style="color: #ffc107;"></i>
                <?php endif; ?>
                <a href="/issues/filters/load/<?= $savedFilter['id'] ?>" style="color: #1a73e8; text-decoration: none; font-weight: 500;">
                    <?= esc($savedFilter['name']) ?>
                </a>
                <form method="POST" action="/issues/filters/delete/<?= $savedFilter['id'] ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this filter?');">
                    <button type="submit" style="background: none; border: none; color: #dc3545; cursor: pointer; padding: 0; margin-left: 5px;" title="Delete filter">
                        <i class="fas fa-times"></i>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Save Filter Modal -->
<div id="saveFilterModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3 style="margin: 0 0 20px 0; color: #2c3e50;">Save Filter</h3>
        <form method="POST" action="/issues/filters/save">
            <?php if ($projectId): ?>
                <input type="hidden" name="project_id" value="<?= $projectId ?>">
            <?php endif; ?>
            
            <!-- Include current filter values -->
            <?php foreach ($filters as $key => $value): ?>
                <?php if ($key !== 'project_id'): ?>
                    <?php if (is_array($value)): ?>
                        <?php foreach ($value as $v): ?>
                            <input type="hidden" name="<?= esc($key) ?>[]" value="<?= esc($v) ?>">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <input type="hidden" name="<?= esc($key) ?>" value="<?= esc($value) ?>">
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #2c3e50;">Filter Name</label>
                <input type="text" name="name" required style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 0.9rem;" placeholder="e.g., My High Priority Issues">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_default" value="1">
                    <span style="color: #2c3e50;">Set as default filter</span>
                </label>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeSaveFilterModal()" class="btn btn-outline" style="padding: 8px 16px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">
                    <i class="fas fa-save"></i> Save Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Save Filter Button -->
<?php if (!empty($filters)): ?>
<div style="margin-bottom: 20px; text-align: right;">
    <button onclick="openSaveFilterModal()" class="btn btn-outline" style="padding: 8px 16px;">
        <i class="fas fa-bookmark"></i> Save Current Filter
    </button>
</div>
<?php endif; ?>

<!-- Issues Table -->
<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px;">
    <?php if (empty($issues)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-tasks" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
            <p>No issues found. <?= !empty($filters) ? 'Try adjusting your filters.' : 'Create your first issue!' ?></p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e0e0e0;">
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Key</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Title</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Type</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Priority</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Status</th>
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
                        <td style="padding: 12px;">
                            <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: #f0f0f0; color: #666;">
                                <?= esc($issue['column_name'] ?? 'Unknown') ?>
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

<script>
function toggleFilterForm() {
    const form = document.getElementById('filterForm');
    const icon = document.getElementById('filterToggleIcon');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        form.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

// Initialize filter form state on page load
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    const icon = document.getElementById('filterToggleIcon');
    if (form && form.style.display !== 'none') {
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    }
});

function openSaveFilterModal() {
    document.getElementById('saveFilterModal').style.display = 'flex';
}

function closeSaveFilterModal() {
    document.getElementById('saveFilterModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('saveFilterModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeSaveFilterModal();
    }
});
</script>

<?= $this->endSection() ?>
