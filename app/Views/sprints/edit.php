<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/sprints/<?= $sprint['id'] ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Sprint
        </a>
        <h1 style="margin: 0; color: #2c3e50;">Edit Sprint</h1>
        <p style="color: #5f6368; margin: 5px 0 0 0;">Project: <?= esc($project['name']) ?></p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
        <form method="post" action="/sprints/<?= $sprint['id'] ?>">
            <?= csrf_field() ?>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Sprint Name <span style="color: #d32f2f;">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    value="<?= old('name', $sprint['name']) ?>"
                    required
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Sprint Goal
                </label>
                <textarea 
                    name="goal" 
                    rows="3"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"
                ><?= old('goal', $sprint['goal']) ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Start Date <span style="color: #d32f2f;">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="start_date" 
                        value="<?= old('start_date', $sprint['start_date']) ?>"
                        required
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                    >
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                        Duration (Weeks) <span style="color: #d32f2f;">*</span>
                    </label>
                    <select name="duration_weeks" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="1" <?= old('duration_weeks', $sprint['duration_weeks']) == '1' ? 'selected' : '' ?>>1 week</option>
                        <option value="2" <?= old('duration_weeks', $sprint['duration_weeks']) == '2' ? 'selected' : '' ?>>2 weeks</option>
                        <option value="3" <?= old('duration_weeks', $sprint['duration_weeks']) == '3' ? 'selected' : '' ?>>3 weeks</option>
                        <option value="4" <?= old('duration_weeks', $sprint['duration_weeks']) == '4' ? 'selected' : '' ?>>4 weeks</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Status
                </label>
                <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="planned" <?= old('status', $sprint['status']) === 'planned' ? 'selected' : '' ?>>Planned</option>
                    <option value="active" <?= old('status', $sprint['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="completed" <?= old('status', $sprint['status']) === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 30px;">
                <a href="/sprints/<?= $sprint['id'] ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Sprint
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

