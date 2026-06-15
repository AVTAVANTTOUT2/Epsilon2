<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">Connexion</h1>
        <p class="auth-subtitle">Accédez à votre espace d'apprentissage</p>

        <form method="POST" action="/login" class="auth-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" value="<?= old('email') ?>" required
                       placeholder="votre@email.com" autocomplete="email" autofocus>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                <input type="password" id="password" name="password" required
                       placeholder="Votre mot de passe" autocomplete="current-password">
            </div>
            <div class="form-group form-checkbox">
                <label>
                    <input type="checkbox" name="remember" value="1">
                    <span>Rester connecté</span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>

        <div class="auth-links">
            <a href="/password-reset">Mot de passe oublié ?</a>
            <span class="auth-separator">|</span>
            <a href="/register">Créer un compte</a>
        </div>
    </div>
</div>
