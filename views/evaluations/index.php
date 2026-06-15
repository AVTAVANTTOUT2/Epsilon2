<div class="evaluate-page">
    <h1 class="page-title"><i class="fas fa-star"></i> Évaluer des travaux</h1>
    <p class="page-subtitle">Aidez vos pairs en évaluant leurs soumissions. Notez de 1 à 5 étoiles et laissez un commentaire.</p>

    <?php if (empty($submissions)): ?>
        <div class="empty-state">
            <i class="fas fa-check-circle"></i>
            <p>Toutes les soumissions ont été évaluées pour le moment.</p>
            <p>Revenez plus tard quand de nouveaux travaux seront disponibles.</p>
        </div>
    <?php else: ?>
        <div class="evaluations-grid">
            <?php foreach ($submissions as $s): ?>
                <div class="eval-card">
                    <div class="eval-card-header" style="border-top:3px solid <?= e($s['course_color']) ?>;">
                        <span class="badge" style="background:<?= e($s['course_color']) ?>20;color:<?= e($s['course_color']) ?>;">
                            <i class="fas fa-circle" style="font-size:6px;"></i> <?= e($s['course_name']) ?>
                        </span>
                        <span class="eval-count"><i class="fas fa-star"></i> <?= $s['eval_count'] ?> éval.</span>
                    </div>
                    <h3><?= e($s['challenge_title']) ?></h3>
                    <div class="eval-author">
                        <i class="fas fa-user-circle"></i>
                        <?= e($s['user_name'] ?: 'Anonyme') ?>
                    </div>
                    <div class="eval-file">
                        <i class="fas fa-file"></i> <?= e($s['original_filename']) ?>
                    </div>
                    <a href="/uploads/<?= e($s['file_path']) ?>" class="btn btn-outline btn-sm" target="_blank">
                        <i class="fas fa-eye"></i> Voir le fichier
                    </a>

                    <form method="POST" action="/evaluate" class="eval-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="submission_id" value="<?= $s['id'] ?>">

                        <div class="star-rating">
                            <label>Note :</label>
                            <div class="stars">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?= $s['id'] ?>_<?= $i ?>" name="score" value="<?= $i ?>"
                                           <?= $i === 3 ? 'checked' : '' ?>>
                                    <label for="star<?= $s['id'] ?>_<?= $i ?>" title="<?= $i ?> étoiles">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comment_<?= $s['id'] ?>">Commentaire (optionnel) :</label>
                            <textarea id="comment_<?= $s['id'] ?>" name="comment" rows="2"
                                      placeholder="Un retour constructif pour aider l'apprenant..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-paper-plane"></i> Envoyer l'évaluation
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
