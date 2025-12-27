<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php helper('markdown'); ?>

<div style="max-width: 1200px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <a href="/projects/<?= $project['id'] ?>" style="color: #1a73e8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; color: #2c3e50;">
                    Wiki - <?= esc($project['name']) ?>
                </h1>
                <p style="margin: 5px 0 0 0; color: #5f6368;">Project documentation and knowledge base</p>
            </div>
            <a href="/projects/<?= $project['id'] ?>/wiki/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Page
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (empty($wikiPages)): ?>
        <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 60px; text-align: center;">
            <i class="fas fa-book" style="font-size: 64px; color: #ddd; margin-bottom: 20px;"></i>
            <h2 style="color: #666; margin-bottom: 10px;">No Wiki Pages Yet</h2>
            <p style="color: #999; margin-bottom: 30px;">Start documenting your project by creating your first wiki page.</p>
            <a href="/projects/<?= $project['id'] ?>/wiki/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create First Page
            </a>
        </div>
    <?php else: ?>
        <div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach ($wikiPages as $page): ?>
                    <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e0e0e0; transition: all 0.2s;">
                        <a href="/projects/<?= $project['id'] ?>/wiki/<?= esc($page['slug']) ?>" style="text-decoration: none; color: inherit;">
                            <h3 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 1.2rem;">
                                <?= esc($page['title']) ?>
                            </h3>
                            <div style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">
                                <?php if ($page['content']): ?>
                                    <?= esc(substr(strip_tags($page['content']), 0, 100)) ?>...
                                <?php else: ?>
                                    <em>No content</em>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: #999;">
                                <span>
                                    <i class="fas fa-user"></i> <?= esc($page['creator_name'] ?? $page['creator_email'] ?? 'Unknown') ?>
                                </span>
                                <span>
                                    <i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($page['created_at'])) ?>
                                </span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
