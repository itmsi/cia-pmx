<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="max-width: 1000px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects/<?= $project['id'] ?>/wiki/<?= esc($wikiPage['slug']) ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Page
        </a>
        <h1 style="margin: 0; color: #2c3e50;">Version History</h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;"><?= esc($wikiPage['title']) ?></p>
    </div>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
        <?php if (empty($versions)): ?>
            <p style="color: #666; text-align: center; padding: 40px;">No versions found.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php foreach ($versions as $version): ?>
                    <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e0e0e0;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <div>
                                <h3 style="margin: 0 0 8px 0; color: #2c3e50;">
                                    Version <?= $version['version_number'] ?>
                                    <?php if ($version['version_number'] == count($versions)): ?>
                                        <span style="margin-left: 10px; padding: 4px 10px; background: #4caf50; color: white; border-radius: 12px; font-size: 0.75rem; font-weight: normal;">Current</span>
                                    <?php endif; ?>
                                </h3>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <span>
                                        <i class="fas fa-user"></i> <?= esc($version['creator_name'] ?? $version['creator_email'] ?? 'Unknown') ?>
                                    </span>
                                    <span style="margin-left: 15px;">
                                        <i class="fas fa-calendar"></i> <?= date('M d, Y H:i', strtotime($version['created_at'])) ?>
                                    </span>
                                </div>
                                <?php if ($version['change_summary']): ?>
                                    <div style="margin-top: 8px; padding: 8px; background: #fff; border-left: 3px solid #4a90e2; font-size: 0.9rem; color: #2c3e50;">
                                        <?= esc($version['change_summary']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <a href="/projects/<?= $project['id'] ?>/wiki/<?= $wikiPage['id'] ?>/versions/<?= $version['version_number'] ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                        <div style="font-size: 0.85rem; color: #999;">
                            <?php if ($version['title']): ?>
                                <strong>Title:</strong> <?= esc($version['title']) ?><br>
                            <?php endif; ?>
                            <?php if ($version['content']): ?>
                                <strong>Content:</strong> <?= esc(substr(strip_tags($version['content']), 0, 150)) ?>...
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
