<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/issues/<?= $issue['id'] ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Issue
        </a>
        <h1 style="margin: 0; color: #2c3e50;">Edit Issue</h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;">
            <?= esc($issue['issue_key'] ?? 'ISSUE-' . $issue['id']) ?> - <?= esc($project['name']) ?>
        </p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
        <form method="post" action="/issues/<?= $issue['id'] ?>">
            <?= csrf_field() ?>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Column <span style="color: #d32f2f;">*</span>
                </label>
                <select name="column_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="">Select Column</option>
                    <?php foreach ($columns as $column): ?>
                        <option value="<?= $column['id'] ?>" <?= old('column_id', $issue['column_id']) == $column['id'] ? 'selected' : '' ?>>
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
                    value="<?= old('title', $issue['title']) ?>"
                    required
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                >
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Type
                    </label>
                    <select name="issue_type" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="task" <?= old('issue_type', strtolower($issue['issue_type'] ?? 'task')) === 'task' ? 'selected' : '' ?>>Task</option>
                        <option value="bug" <?= old('issue_type', strtolower($issue['issue_type'] ?? 'task')) === 'bug' ? 'selected' : '' ?>>Bug</option>
                        <option value="story" <?= old('issue_type', strtolower($issue['issue_type'] ?? 'task')) === 'story' ? 'selected' : '' ?>>Story</option>
                        <option value="epic" <?= old('issue_type', strtolower($issue['issue_type'] ?? 'task')) === 'epic' ? 'selected' : '' ?>>Epic</option>
                        <option value="sub_task" <?= old('issue_type', strtolower($issue['issue_type'] ?? 'task')) === 'sub_task' ? 'selected' : '' ?>>Sub-task</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Priority
                    </label>
                    <select name="priority" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="lowest" <?= old('priority', strtolower($issue['priority'] ?? 'medium')) === 'lowest' ? 'selected' : '' ?>>Lowest</option>
                        <option value="low" <?= old('priority', strtolower($issue['priority'] ?? 'medium')) === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= old('priority', strtolower($issue['priority'] ?? 'medium')) === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= old('priority', strtolower($issue['priority'] ?? 'medium')) === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="critical" <?= old('priority', strtolower($issue['priority'] ?? 'medium')) === 'critical' ? 'selected' : '' ?>>Critical</option>
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
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"
                ><?= old('description', $issue['description'] ?? '') ?></textarea>
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
                                <option value="<?= $user['id'] ?>" <?= old('assignee_id', $issue['assignee_id']) == $user['id'] ? 'selected' : '' ?>>
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
                        value="<?= old('due_date', $issue['due_date'] ?? '') ?>"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                    >
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Estimation (Story Points)
                </label>
                <input 
                    type="number" 
                    name="estimation" 
                    value="<?= old('estimation', $issue['estimation'] ?? '') ?>"
                    min="0"
                    step="0.5"
                    placeholder="e.g., 3, 5, 8"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                >
                <small style="color: #5f6368; font-size: 12px; margin-top: 4px; display: block;">
                    Story points untuk estimation (Fibonacci: 1, 2, 3, 5, 8, 13, etc.)
                </small>
            </div>

            <?php if (!empty($labels)): ?>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Labels
                    </label>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa; max-height: 200px; overflow-y: auto;">
                        <?php foreach ($labels as $label): ?>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 6px 10px; background: #fff; border-radius: 4px; border: 1px solid #e0e0e0;">
                                <input 
                                    type="checkbox" 
                                    name="labels[]" 
                                    value="<?= $label['id'] ?>"
                                    <?= in_array($label['id'], $issueLabels) ? 'checked' : '' ?>
                                    style="cursor: pointer;"
                                >
                                <span style="padding: 2px 6px; border-radius: 3px; font-size: 0.85rem; background: <?= esc($label['color'] ?? '#e3f2fd') ?>; color: #2c3e50;">
                                    <?= esc($label['name']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 30px;">
                <a href="/issues/<?= $issue['id'] ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Issue
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

