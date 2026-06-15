<div class="evaluations-page">
    <h1 class="page-title"><i class="fas fa-star"></i> Mes Évaluations</h1>

    <div class="eval-tabs">
        <div class="tab active">Évaluations reçues (<?= count($evaluationsReceived) ?>)</div>
        <div class="tab">Évaluations données (<?= count($evaluationsGiven) ?>)</div>
    </div>

    <section class="section">
        <h2 class="section-title">Évaluations reçues</h2>
        <?php if (empty($evaluationsReceived)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Vous n'avez pas encore reçu d'évaluation.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Évaluateur</th>
                            <th>Défi</th>
                            <th>Parcours</th>
                            <th>Note</th>
                            <th>Commentaire</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evaluationsReceived as $e): ?>
                        <tr>
                            <td><i class="fas fa-user-circle"></i> <?= e($e['evaluator_name'] ?: 'Anonyme') ?></td>
                            <td><?= e($e['challenge_title']) ?></td>
                            <td><?= e($e['course_name']) ?></td>
                            <td>
                                <div class="score-display">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="color:<?= $i <= $e['score'] ? '#f59e0b' : '#4a5568' ?>;font-size:12px;"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td><?= e($e['comment']) ?: '<em style="color:#64748b;">Pas de commentaire</em>' ?></td>
                            <td><?= e(date('d/m/Y', strtotime($e['created_at']))) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="section">
        <h2 class="section-title">Évaluations données</h2>
        <?php if (empty($evaluationsGiven)): ?>
            <div class="empty-state">
                <i class="fas fa-star-half-alt"></i>
                <p>Vous n'avez pas encore évalué de travaux.</p>
                <a href="/evaluate" class="btn btn-primary">Commencer à évaluer</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Apprenant</th>
                            <th>Défi</th>
                            <th>Parcours</th>
                            <th>Note</th>
                            <th>Commentaire</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evaluationsGiven as $e): ?>
                        <tr>
                            <td><i class="fas fa-user-circle"></i> <?= e($e['user_name'] ?: 'Anonyme') ?></td>
                            <td><?= e($e['challenge_title']) ?></td>
                            <td><?= e($e['course_name']) ?></td>
                            <td>
                                <div class="score-display">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="color:<?= $i <= $e['score'] ? '#f59e0b' : '#4a5568' ?>;font-size:12px;"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td><?= e($e['comment']) ?: '<em style="color:#64748b;">Pas de commentaire</em>' ?></td>
                            <td><?= e(date('d/m/Y', strtotime($e['created_at']))) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
