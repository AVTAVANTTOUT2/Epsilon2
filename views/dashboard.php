<div class="dashboard">
    <div class="dashboard-header">
        <h1>Tableau de bord</h1>
        <p class="welcome-text">Bienvenue, <strong><?= e($user['name'] ?: explode('@', $user['email'])[0]) ?></strong></p>
    </div>

    <!-- Stats rapides -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-folder-open stat-icon"></i>
            <div class="stat-value"><?= count($submissions) ?></div>
            <div class="stat-label">Travaux soumis</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-star stat-icon"></i>
            <div class="stat-value"><?= count($evaluationsGiven) ?></div>
            <div class="stat-label">Évaluations données</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-comment stat-icon"></i>
            <div class="stat-value"><?= count($evaluationsReceived) ?></div>
            <div class="stat-label">Retours reçus</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-award stat-icon"></i>
            <div class="stat-value"><?= count($badges) ?>/<?= count($allBadges) ?></div>
            <div class="stat-label">Badges obtenus</div>
        </div>
    </div>

    <!-- Parcours -->
    <section class="section">
        <h2 class="section-title">Mes Parcours</h2>
        <div class="courses-grid">
            <?php foreach ($courses as $course): ?>
                <?php
                    $courseIndex = (int)$course['course_index'];
                    $level = $accessCodeArray[$courseIndex] ?? 0;
                    $isJoined = \App\Models\Course::isJoined($courseIndex, $accessCodeArray);
                    $canJoin = \App\Models\Course::canJoin($courseIndex, $accessCodeArray);
                    $rankName = \App\Models\Course::rankName($level);
                    $rankIcon = \App\Models\Course::rankIcon($level);
                    $rankClass = \App\Models\Course::rankClass($level);
                ?>
                <div class="course-card <?= $isJoined ? 'course-joined' : '' ?>">
                    <div class="course-card-header" style="border-left-color: <?= e($course['color']) ?>;">
                        <i class="fas <?= e($course['icon']) ?> course-icon" style="color:<?= e($course['color']) ?>;"></i>
                        <h3><?= e($course['name']) ?></h3>
                    </div>
                    <p class="course-desc"><?= e($course['description']) ?></p>
                    <div class="course-rank">
                        <i class="fas <?= e($rankIcon) ?> <?= e($rankClass) ?>"></i>
                        <span class="<?= e($rankClass) ?>"><?= e($rankName) ?></span>
                    </div>
                    <div class="course-actions">
                        <?php if ($isJoined): ?>
                            <?php $next = \App\Models\Course::getNextChallengeLevel($courseIndex, $accessCodeArray); ?>
                            <a href="/courses/<?= $course['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-arrow-right"></i> Continuer
                            </a>
                        <?php elseif ($canJoin): ?>
                            <form method="POST" action="/courses/<?= $course['id'] ?>/join" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus"></i> Rejoindre
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="btn btn-sm btn-disabled" title="Complétez d'autres défis d'abord">
                                <i class="fas fa-lock"></i> Verrouillé
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Badges -->
    <section class="section">
        <h2 class="section-title">Mes Badges</h2>
        <div class="badges-grid">
            <?php foreach ($allBadges as $badge): ?>
                <?php $earned = false;
                foreach ($badges as $ub) {
                    if ((int)$ub['id'] === (int)$badge['id']) {$earned = true; break;}
                } ?>
                <div class="badge-card <?= $earned ? 'badge-earned' : 'badge-locked' ?>">
                    <i class="fas <?= e($badge['icon']) ?> badge-icon" style="color:<?= $earned ? e($badge['color']) : '#4a5568' ?>;"></i>
                    <div class="badge-name"><?= e($badge['name']) ?></div>
                    <div class="badge-desc"><?= e($badge['description']) ?></div>
                    <div class="badge-status"><?= $earned ? '<span style="color:#10b981;">Obtenu</span>' : '<span style="color:#64748b;">Verrouillé</span>' ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Dernières soumissions -->
    <?php if (!empty($submissions)): ?>
    <section class="section">
        <h2 class="section-title">Mes dernières soumissions</h2>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Défi</th>
                        <th>Parcours</th>
                        <th>Fichier</th>
                        <th>Date</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($submissions, 0, 5) as $s): ?>
                    <tr>
                        <td><?= e($s['challenge_title']) ?></td>
                        <td><?= e($s['course_name']) ?></td>
                        <td><i class="fas fa-file"></i> <?= e($s['original_filename']) ?></td>
                        <td><?= e(date('d/m/Y', strtotime($s['created_at']))) ?></td>
                        <td>
                            <?php if ($s['status'] === 'pending'): ?>
                                <span class="badge badge-pending">En attente</span>
                            <?php else: ?>
                                <span class="badge badge-reviewed">Évalué</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>
</div>
