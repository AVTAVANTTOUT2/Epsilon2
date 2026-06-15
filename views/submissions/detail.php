<div class="submission-detail-page">
    <a href="/submissions" class="back-link"><i class="fas fa-arrow-left"></i> Retour</a>
    <h1 class="page-title">Détail de la soumission</h1>

    <div class="submission-full">
        <div class="submission-info-card">
            <h3><?= e($submission['challenge_title']) ?></h3>
            <div class="submission-meta-grid">
                <div><strong>Parcours :</strong> <?= e($submission['course_name']) ?></div>
                <div><strong>Par :</strong> <?= e($submission['user_name'] ?: $submission['user_email']) ?></div>
                <div><strong>Fichier :</strong> <?= e($submission['original_filename']) ?></div>
                <div><strong>Date :</strong> <?= e(date('d/m/Y H:i', strtotime($submission['created_at']))) ?></div>
                <div><strong>Niveau :</strong> <?= e(\App\Models\Course::rankName((int)$submission['rank_level'])) ?></div>
            </div>
            <a href="/uploads/<?= e($submission['file_path']) ?>" class="btn btn-primary" target="_blank">
                <i class="fas fa-download"></i> Télécharger le fichier
            </a>
        </div>
    </div>
</div>
