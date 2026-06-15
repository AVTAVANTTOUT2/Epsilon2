<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Plateforme de peer-learning de l'EPSI Lille">
    <title><?= e($title ?? 'Epsilon') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div id="stars"></div>
    <div id="stars2"></div>
    <div id="stars3"></div>

    <nav class="navbar">
        <div class="navbar-inner">
            <a href="/" class="logo">
                <span class="logo-icon">&#x03B5;</span>
                <span class="logo-text">Epsilon</span>
            </a>
            <div class="nav-links">
                <?php if (\App\Core\Session::isLoggedIn()): ?>
                    <a href="/dashboard" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a>
                    <a href="/courses" class="nav-link"><i class="fas fa-route"></i> Parcours</a>
                    <a href="/submissions" class="nav-link"><i class="fas fa-folder-open"></i> Mes travaux</a>
                    <a href="/evaluate" class="nav-link"><i class="fas fa-star"></i> Évaluer</a>
                    <div class="nav-user">
                        <a href="/profile" class="nav-link user-link">
                            <i class="fas fa-user-circle"></i>
                            <?= e(\App\Core\Session::user()['name'] ?: explode('@', \App\Core\Session::user()['email'])[0]) ?>
                        </a>
                        <a href="/logout" class="nav-link logout-link" title="Déconnexion">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="/login" class="nav-link"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                    <a href="/register" class="nav-link nav-link-primary"><i class="fas fa-user-plus"></i> Inscription</a>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-btn" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <?php
    $flashSuccess = flash('success');
    $flashError = flash('error');
    ?>
    <?php if ($flashSuccess): ?>
        <div class="flash flash-success"><?= $flashSuccess ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="flash flash-error"><?= $flashError ?></div>
    <?php endif; ?>

    <main class="main-content">
        <?= $content ?>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <p>&copy; <?= date('Y') ?> EPSI Lille &mdash; Plateforme Epsilon</p>
        </div>
    </footer>

    <script>
    document.querySelector('.mobile-menu-btn')?.addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('open');
    });
    </script>
</body>
</html>
