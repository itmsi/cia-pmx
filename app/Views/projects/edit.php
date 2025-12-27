<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects/<?= $project['id'] ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
        <h1 style="margin: 0; color: #2c3e50;">Edit Project</h1>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
        <form method="post" action="/projects/<?= $project['id'] ?>">
            <?= csrf_field() ?>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Project Key <span style="color: #d32f2f;">*</span>
                </label>
                <input 
                    type="text" 
                    name="key" 
                    value="<?= old('key', $project['project_key']) ?>"
                    required
                    maxlength="10"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; text-transform: uppercase;"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Project Name <span style="color: #d32f2f;">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    value="<?= old('name', $project['name']) ?>"
                    required
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Description
                </label>
                <textarea 
                    name="description" 
                    rows="3"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"
                ><?= old('description', $project['description'] ?? '') ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Visibility
                    </label>
                    <select name="visibility" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="private" <?= old('visibility', $project['visibility']) === 'private' ? 'selected' : '' ?>>Private</option>
                        <option value="workspace" <?= old('visibility', $project['visibility']) === 'workspace' ? 'selected' : '' ?>>Workspace</option>
                        <option value="public" <?= old('visibility', $project['visibility']) === 'public' ? 'selected' : '' ?>>Public</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Status
                    </label>
                    <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="planning" <?= old('status', $project['status']) === 'planning' ? 'selected' : '' ?>>Planning</option>
                        <option value="active" <?= old('status', $project['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="on_hold" <?= old('status', $project['status']) === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                        <option value="completed" <?= old('status', $project['status']) === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="archived" <?= old('status', $project['status']) === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Start Date
                    </label>
                    <input 
                        type="date" 
                        name="start_date" 
                        value="<?= old('start_date', $project['start_date'] ?? '') ?>"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                    >
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        End Date
                    </label>
                    <input 
                        type="date" 
                        name="end_date" 
                        value="<?= old('end_date', $project['end_date'] ?? '') ?>"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                    >
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 30px;">
                <a href="/projects/<?= $project['id'] ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Project
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

