<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects/<?= $project['id'] ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
        <h1 style="margin: 0; color: #2c3e50;">Create Issue</h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;">Project: <?= esc($project['name']) ?></p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
        <form method="post" action="/issues">
            <?= csrf_field() ?>
            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Column <span style="color: #d32f2f;">*</span>
                </label>
                <select name="column_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="">Select Column</option>
                    <?php foreach ($columns as $column): ?>
                        <option value="<?= $column['id'] ?>">
                            <?= esc($column['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Title <span style="color: #d32f2f;">*</span>
                </label>
                <input 
                    type="text" 
                    name="title" 
                    value="<?= old('title') ?>"
                    required
                    placeholder="e.g., Fix login bug"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                >
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Type
                    </label>
                    <select name="issue_type" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="task" <?= old('issue_type', 'task') === 'task' ? 'selected' : '' ?>>Task</option>
                        <option value="bug" <?= old('issue_type') === 'bug' ? 'selected' : '' ?>>Bug</option>
                        <option value="story" <?= old('issue_type') === 'story' ? 'selected' : '' ?>>Story</option>
                        <option value="epic" <?= old('issue_type') === 'epic' ? 'selected' : '' ?>>Epic</option>
                        <option value="sub_task" <?= old('issue_type') === 'sub_task' ? 'selected' : '' ?>>Sub-task</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Priority
                    </label>
                    <select name="priority" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="lowest" <?= old('priority', 'medium') === 'lowest' ? 'selected' : '' ?>>Lowest</option>
                        <option value="low" <?= old('priority', 'medium') === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= old('priority', 'medium') === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= old('priority', 'medium') === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="critical" <?= old('priority', 'medium') === 'critical' ? 'selected' : '' ?>>Critical</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Description
                </label>
                <textarea 
                    name="description" 
                    rows="5"
                    placeholder="Describe the issue..."
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"
                ><?= old('description') ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Assignee
                    </label>
                    <select name="assignee_id" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">Unassigned</option>
                        <?php if (!empty($projectUsers)): ?>
                            <?php foreach ($projectUsers as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= old('assignee_id') == $user['id'] ? 'selected' : '' ?>>
                                    <?= esc($user['full_name'] ?? $user['email']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Due Date
                    </label>
                    <input 
                        type="date" 
                        name="due_date" 
                        value="<?= old('due_date') ?>"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                    >
                </div>
            </div>

            <?php if (!empty($labels)): ?>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Labels
                    </label>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa;">
                        <?php foreach ($labels as $label): ?>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="checkbox" name="labels[]" value="<?= $label['id'] ?>">
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; background: <?= $label['color'] ?? '#e3f2fd' ?>; color: #2c3e50;">
                                    <?= esc($label['name']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 30px;">
                <a href="/projects/<?= $project['id'] ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Issue
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

