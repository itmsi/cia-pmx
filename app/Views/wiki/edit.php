<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1000px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects/<?= $project['id'] ?>/wiki/<?= esc($wikiPage['slug']) ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Page
        </a>
        <h1 style="margin: 0; color: #2c3e50;">Edit Wiki Page</h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;">Project: <?= esc($project['name']) ?></p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
        <form method="post" action="/projects/<?= $project['id'] ?>/wiki/<?= $wikiPage['id'] ?>">
            <?= csrf_field() ?>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Title <span style="color: #d32f2f;">*</span>
                </label>
                <input 
                    type="text" 
                    name="title" 
                    value="<?= old('title', $wikiPage['title']) ?>"
                    required
                    placeholder="Page title"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Content (Markdown) <span style="color: #d32f2f;">*</span>
                </label>
                <textarea 
                    name="content" 
                    rows="20"
                    required
                    placeholder="Write your content in Markdown format..."
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; font-family: 'Courier New', monospace; resize: vertical;"
                ><?= old('content', $wikiPage['content']) ?></textarea>
                <small style="color: #666; display: block; margin-top: 4px;">
                    Supports Markdown syntax: **bold**, *italic*, `code`, [links](url), # headers, lists, etc.
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
                    Change Summary (Optional)
                </label>
                <input 
                    type="text" 
                    name="change_summary" 
                    value="<?= old('change_summary') ?>"
                    placeholder="Brief description of changes made"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                >
                <small style="color: #666; display: block; margin-top: 4px;">
                    This summary will be saved with the version history
                </small>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 30px;">
                <a href="/projects/<?= $project['id'] ?>/wiki/<?= esc($wikiPage['slug']) ?>" class="btn btn-outline" style="padding: 10px 20px;">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
