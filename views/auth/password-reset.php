<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">Mot de passe oublié</h1>
        <p class="auth-subtitle">Recevez un lien de réinitialisation par email</p>

        <form method="POST" action="/password-reset/request" class="auth-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" value="<?= old('email') ?>" required
                       placeholder="votre@email.com" autocomplete="email" autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-paper-plane"></i> Envoyer le lien
            </button>
        </form>

        <div class="auth-links">
            <a href="/login">Retour à la connexion</a>
        </div>
    </div>
</div>
