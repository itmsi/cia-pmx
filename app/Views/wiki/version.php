<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php helper('markdown'); ?>

<div style="max-width: 1200px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects/<?= $project['id'] ?>/wiki/<?= $wikiPage['id'] ?>/versions" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Versions
        </a>
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h1 style="margin: 0; color: #2c3e50;">
                    Version <?= $version['version_number'] ?> - <?= esc($version['title']) ?>
                </h1>
                <div style="margin-top: 8px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; font-size: 0.9rem; color: #5f6368;">
                    <span>
                        <i class="fas fa-user"></i> <?= esc($version['creator_name'] ?? $version['creator_email'] ?? 'Unknown') ?>
                    </span>
                    <span>
                        <i class="fas fa-calendar"></i> <?= date('M d, Y H:i', strtotime($version['created_at'])) ?>
                    </span>
                    <?php if ($version['change_summary']): ?>
                        <span style="padding: 4px 10px; background: #e3f2fd; color: #1976d2; border-radius: 12px;">
                            <?= esc($version['change_summary']) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($canEdit && !$isLatestVersion): ?>
                <form method="post" action="/projects/<?= $project['id'] ?>/wiki/<?= $wikiPage['id'] ?>/versions/<?= $version['version_number'] ?>/restore" style="display: inline;" onsubmit="return confirm('Are you sure you want to restore this version? This will create a new version with the content from this version.')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-undo"></i> Restore This Version
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 40px;">
        <?php if ($version['content']): ?>
            <div style="line-height: 1.8; color: #2c3e50;">
                <?= markdown_to_html($version['content']) ?>
            </div>
        <?php else: ?>
            <p style="color: #666; font-style: italic;">No content in this version.</p>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
