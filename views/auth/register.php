<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">Inscription</h1>
        <p class="auth-subtitle">Rejoignez la communauté d'apprentissage</p>

        <form method="POST" action="/register" class="auth-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Nom (optionnel)</label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>"
                       placeholder="Votre nom ou pseudo" autocomplete="name">
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" value="<?= old('email') ?>" required
                       placeholder="votre@email.com" autocomplete="email" autofocus>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                <input type="password" id="password" name="password" required
                       placeholder="Minimum 8 caractères" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="password_confirmation"><i class="fas fa-lock"></i> Confirmation</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       placeholder="Confirmez votre mot de passe" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-user-plus"></i> S'inscrire
            </button>
        </form>

        <div class="auth-links">
            <a href="/login">Déjà un compte ? Connectez-vous</a>
        </div>
    </div>
</div>
