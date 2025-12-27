<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 600px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/permissions/<?= $permission['id'] ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Permission
        </a>
        <h1 style="margin: 0; color: #2c3e50;">Edit Permission</h1>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
        <form method="post" action="/permissions/<?= $permission['id'] ?>">
            <?= csrf_field() ?>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Permission Name <span style="color: #d32f2f;">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    value="<?= old('name', $permission['name']) ?>"
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
                ><?= old('description', $permission['description'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 30px;">
                <a href="/permissions/<?= $permission['id'] ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Permission
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

