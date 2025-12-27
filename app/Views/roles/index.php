<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 style="margin: 0; color: #2c3e50;">Roles</h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;">Manage user roles and permissions</p>
    </div>
    <a href="/roles/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Role
    </a>
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

<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px;">
    <?php if (empty($roles)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-user-shield" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
            <p>No roles created yet. Create your first role!</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e0e0e0;">
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Name</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Slug</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Description</th>
                    <th style="text-align: right; padding: 12px; font-weight: 600; color: #2c3e50;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr style="border-bottom: 1px solid #f0f0f0; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='#fff'">
                        <td style="padding: 12px;">
                            <a href="/roles/<?= $role['id'] ?>" style="color: #1a73e8; text-decoration: none; font-weight: 500;">
                                <?= esc($role['name']) ?>
                            </a>
                        </td>
                        <td style="padding: 12px; color: #5f6368; font-family: monospace;">
                            <?= esc($role['slug']) ?>
                        </td>
                        <td style="padding: 12px; color: #5f6368;">
                            <?= esc($role['description'] ?? '-') ?>
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="/roles/<?= $role['id'] ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">
                                    View
                                </a>
                                <a href="/roles/<?= $role['id'] ?>/edit" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">
                                    Edit
                                </a>
                                <form method="post" action="/roles/<?= $role['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem; color: #d32f2f; border-color: #d32f2f;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

