<div class="profile-page">
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="profile-info">
            <h1><?= e($user['name'] ?: 'Anonyme') ?></h1>
            <p class="profile-email"><i class="fas fa-envelope"></i> <?= e($user['email']) ?></p>
            <p class="profile-date">Membre depuis le <?= e(date('d/m/Y', strtotime($user['created_at']))) ?></p>
        </div>

        <form method="POST" action="/profile" class="profile-edit-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name"><i class="fas fa-user-edit"></i> Modifier mon nom</label>
                <div class="form-inline">
                    <input type="text" id="name" name="name" value="<?= e($user['name']) ?>"
                           placeholder="Votre nom ou pseudo" autocomplete="name">
                    <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>

    <div class="profile-stats">
        <div class="stat-card">
            <div class="stat-value"><?= $submissionCount ?></div>
            <div class="stat-label">Travaux soumis</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $evaluationCount ?></div>
            <div class="stat-label">Évaluations données</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= count($badges) ?>/<?= count($allBadges) ?></div>
            <div class="stat-label">Badges</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= max($accessCodeArray) ?></div>
            <div class="stat-label">Niveau max</div>
        </div>
    </div>

    <section class="section">
        <h2 class="section-title">Mes Parcours</h2>
        <div class="courses-grid">
            <?php foreach ($courses as $course): ?>
                <?php
                    $courseIndex = (int)$course['course_index'];
                    $level = $accessCodeArray[$courseIndex] ?? 0;
                    $rankName = \App\Models\Course::rankName($level);
                    $rankIcon = \App\Models\Course::rankIcon($level);
                    $rankClass = \App\Models\Course::rankClass($level);
                ?>
                <div class="course-card">
                    <i class="fas <?= e($course['icon']) ?> course-card-icon" style="color:<?= e($course['color']) ?>;"></i>
                    <strong><?= e($course['name']) ?></strong>
                    <span class="<?= e($rankClass) ?>"><i class="fas <?= e($rankIcon) ?>"></i> <?= e($rankName) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Badges obtenus</h2>
        <div class="badges-grid">
            <?php foreach ($allBadges as $badge): ?>
                <?php $earned = false;
                foreach ($badges as $ub) {
                    if ((int)$ub['id'] === (int)$badge['id']) {$earned = true; break;}
                } ?>
                <div class="badge-card <?= $earned ? 'badge-earned' : 'badge-locked' ?>">
                    <i class="fas <?= e($badge['icon']) ?> badge-icon" style="color:<?= $earned ? e($badge['color']) : '#4a5568' ?>;"></i>
                    <div class="badge-name"><?= e($badge['name']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
