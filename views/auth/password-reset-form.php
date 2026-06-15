<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">Nouveau mot de passe</h1>
        <p class="auth-subtitle">Choisissez un nouveau mot de passe</p>

        <form method="POST" action="/password-reset?token=<?= e($token) ?>" class="auth-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Nouveau mot de passe</label>
                <input type="password" id="password" name="password" required
                       placeholder="Minimum 8 caractères" autocomplete="new-password" autofocus>
            </div>
            <div class="form-group">
                <label for="password_confirmation"><i class="fas fa-lock"></i> Confirmation</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       placeholder="Confirmez le mot de passe" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-save"></i> Mettre à jour
            </button>
        </form>
    </div>
</div>
