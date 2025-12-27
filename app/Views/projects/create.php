<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
        <h1 style="margin: 0; color: #2c3e50;">Create Project</h1>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
        <form method="post" action="/projects">
            <?= csrf_field() ?>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Workspace <span style="color: #d32f2f;">*</span>
                </label>
                <select name="workspace_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="">Select Workspace</option>
                    <?php foreach ($workspaces as $ws): ?>
                        <option value="<?= $ws['id'] ?>" <?= old('workspace_id') == $ws['id'] ? 'selected' : '' ?>>
                            <?= esc($ws['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Project Key <span style="color: #d32f2f;">*</span>
                </label>
                <input 
                    type="text" 
                    name="key" 
                    value="<?= old('key') ?>"
                    required
                    placeholder="e.g., PROJ, MSI"
                    maxlength="10"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; text-transform: uppercase;"
                >
                <small style="color: #5f6368; font-size: 12px; margin-top: 4px; display: block;">
                    Short unique identifier (max 10 characters, uppercase)
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Project Name <span style="color: #d32f2f;">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    value="<?= old('name') ?>"
                    required
                    placeholder="e.g., Mobile App Development"
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
                    placeholder="Describe your project..."
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"
                ><?= old('description') ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Visibility
                    </label>
                    <select name="visibility" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="private" <?= old('visibility', 'private') === 'private' ? 'selected' : '' ?>>Private</option>
                        <option value="workspace" <?= old('visibility') === 'workspace' ? 'selected' : '' ?>>Workspace</option>
                        <option value="public" <?= old('visibility') === 'public' ? 'selected' : '' ?>>Public</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Status
                    </label>
                    <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="planning" <?= old('status', 'planning') === 'planning' ? 'selected' : '' ?>>Planning</option>
                        <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="on_hold" <?= old('status') === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                        <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="archived" <?= old('status') === 'archived' ? 'selected' : '' ?>>Archived</option>
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
                        value="<?= old('start_date') ?>"
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
                        value="<?= old('end_date') ?>"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                    >
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 30px;">
                <a href="/projects" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Project
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

