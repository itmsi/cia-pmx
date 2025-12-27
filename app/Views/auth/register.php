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

    .auth-actions a {
        display: inline-block;
        margin-top: 15px;
        font-size: 14px;
    }
</style>

<div class="auth-container">

    <h1>Register</h1>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="auth-error">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/register">
        <?= csrf_field() ?>

        <div class="auth-field">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= old('email') ?>"
                required
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

        <div style="font-size:13px; color:#555; margin-top:5px;">
            Password should contain:
            <ul style="margin:5px 0 0 18px;">
                <li>At least 6 characters</li>
                <li>One uppercase letter</li>
                <li>One number</li>
            </ul>
        </div>

        <div class="auth-field">
            <label for="password_confirm">Confirm Password</label>
            <input
                type="password"
                id="password_confirm"
                name="password_confirm"
                required
            >
        </div>

        <div class="auth-actions">
            <button type="submit">Create Account</button>

            <a href="/login">Already have an account? Login</a>
        </div>
    </form>

</div>

<?= $this->endSection() ?>
