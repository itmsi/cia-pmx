<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php helper('markdown'); ?>

<div style="max-width: 1200px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects/<?= $project['id'] ?>/wiki" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Wiki
        </a>
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h1 style="margin: 0; color: #2c3e50;">
                    <?= esc($wikiPage['title']) ?>
                </h1>
                <div style="margin-top: 8px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; font-size: 0.9rem; color: #5f6368;">
                    <span>
                        <i class="fas fa-user"></i> Created by <?= esc($wikiPage['creator_name'] ?? $wikiPage['creator_email'] ?? 'Unknown') ?>
                    </span>
                    <span>
                        <i class="fas fa-calendar"></i> <?= date('M d, Y H:i', strtotime($wikiPage['created_at'])) ?>
                    </span>
                    <?php if ($wikiPage['updated_at'] != $wikiPage['created_at']): ?>
                        <span>
                            <i class="fas fa-edit"></i> Updated <?= date('M d, Y H:i', strtotime($wikiPage['updated_at'])) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (count($versions) > 1): ?>
                        <a href="/projects/<?= $project['id'] ?>/wiki/<?= $wikiPage['id'] ?>/versions" style="color: #1a73e8; text-decoration: none;">
                            <i class="fas fa-history"></i> <?= count($versions) ?> versions
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <?php if ($canEdit): ?>
                    <a href="/projects/<?= $project['id'] ?>/wiki/<?= $wikiPage['id'] ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                    <form method="post" action="/projects/<?= $project['id'] ?>/wiki/<?= $wikiPage['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this wiki page?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline" style="color: #d32f2f; border-color: #d32f2f;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 40px;">
        <?php if ($wikiPage['content']): ?>
            <div style="line-height: 1.8; color: #2c3e50;">
                <?= markdown_to_html($wikiPage['content']) ?>
            </div>
        <?php else: ?>
            <p style="color: #666; font-style: italic;">No content yet.</p>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
