<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 style="margin: 0; color: #2c3e50;">Users</h1>
        <p style="margin: 5px 0 0 0; color: #5f6368;">Manage system users</p>
    </div>
    <a href="/users/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create User
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
    <?php if (empty($users)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-users" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
            <p>No users found. Create your first user!</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e0e0e0;">
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Name</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Email</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Role</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Status</th>
                    <th style="text-align: left; padding: 12px; font-weight: 600; color: #2c3e50;">Phone</th>
                    <th style="text-align: right; padding: 12px; font-weight: 600; color: #2c3e50;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr style="border-bottom: 1px solid #f0f0f0; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='#fff'">
                        <td style="padding: 12px;">
                            <a href="/users/<?= $user['id'] ?>" style="color: #1a73e8; text-decoration: none; font-weight: 500;">
                                <?= esc($user['full_name'] ?? $user['email']) ?>
                            </a>
                        </td>
                        <td style="padding: 12px; color: #5f6368;">
                            <?= esc($user['email']) ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php if ($user['role_name']): ?>
                                <span style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                    <?= esc($user['role_name']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php if ($user['status'] === 'active'): ?>
                                <span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                    Active
                                </span>
                            <?php else: ?>
                                <span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px; color: #5f6368;">
                            <?= esc($user['phone'] ?? '-') ?>
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="/users/<?= $user['id'] ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">
                                    View
                                </a>
                                <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">
                                    Edit
                                </a>
                                <?php if ($user['id'] != session()->get('user_id')): ?>
                                <form method="post" action="/users/<?= $user['id'] ?>/force-logout" style="display: inline;" onsubmit="return confirm('Are you sure you want to force logout this user? They will be logged out on their next request.')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem; color: #f57c00; border-color: #f57c00;" title="Force logout user">
                                        <i class="fas fa-sign-out-alt"></i> Force Logout
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="post" action="/users/<?= $user['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
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

