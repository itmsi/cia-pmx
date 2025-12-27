<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .auth-container {
        max-width: 380px;
        margin: 80px auto;
        padding: 30px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: #fff;
    }

    .auth-container h1 {
        margin-bottom: 20px;
        text-align: center;
    }

    .auth-field {
        margin-bottom: 15px;
    }

    .auth-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .auth-field input {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .auth-error {
        background: #ffe5e5;
        color: #b30000;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        font-size: 14px;
    }

    .auth-actions {
        margin-top: 20px;
        text-align: center;
    }

    .auth-actions button {
        width: 100%;
        padding: 10px;
        background: #222;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .auth-actions button:hover {
        background: #000;
    }
</style>

<div class="auth-container">

    <h1>Login</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div style="background:#e6ffed; color:#146c2e; padding:10px; margin-bottom:15px; border-radius:4px;">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="auth-error">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/login">
        <?= csrf_field() ?>

        <div class="auth-field">
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email"
                name="email" 
                required 
                autofocus
            >
        </div>

        <div class="auth-field">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password"
                name="password" 
                required
            >
        </div>

        <div class="auth-actions">
            <button type="submit">Login</button>
        </div>
        <div class="auth-actions">
            <p style="margin-top:15px; font-size:14px;">
                Don’t have an account?
                <a href="/register">Register</a>
            </p>
        </div>
    </form>

</div>

<?= $this->endSection() ?>
