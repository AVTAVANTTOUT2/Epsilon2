<div class="submission-page">
    <div class="page-header">
        <a href="/courses/<?= $course['id'] ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour au parcours
        </a>
        <h1><?= e($challenge['title']) ?></h1>
        <div class="submission-meta">
            <span class="badge" style="background:<?= e($course['color']) ?>20;color:<?= e($course['color']) ?>;border:1px solid <?= e($course['color']) ?>40;">
                <i class="fas <?= e($course['icon']) ?>"></i> <?= e($course['name']) ?>
            </span>
            <span class="badge">
                <i class="fas <?= e(\App\Models\Course::rankIcon((int)$challenge['rank_level'])) ?>"></i>
                <?= e(\App\Models\Course::rankName((int)$challenge['rank_level'])) ?>
            </span>
        </div>
    </div>

    <div class="challenge-description-card">
        <h3><i class="fas fa-info-circle"></i> Description du défi</h3>
        <p><?= e($challenge['description']) ?></p>
    </div>

    <!-- Upload -->
    <div class="upload-section">
        <h2>
            <?php if ($existingSubmission): ?>
                <i class="fas fa-check-circle" style="color:#10b981;"></i> Travail soumis
            <?php else: ?>
                <i class="fas fa-cloud-upload-alt"></i> Soumettre votre travail
            <?php endif; ?>
        </h2>

        <?php if ($existingSubmission): ?>
            <div class="existing-submission">
                <div class="file-info">
                    <i class="fas fa-file file-icon"></i>
                    <div>
                        <strong><?= e($existingSubmission['original_filename']) ?></strong>
                        <small><?= e(date('d/m/Y H:i', strtotime($existingSubmission['created_at']))) ?></small>
                    </div>
                    <a href="/uploads/<?= e($existingSubmission['file_path']) ?>" class="btn btn-outline btn-sm" target="_blank">
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                </div>
                <p class="resubmit-note">Vous pouvez soumettre une nouvelle version si nécessaire.</p>
            </div>
        <?php endif; ?>

        <form method="POST" action="/courses/<?= $course['id'] ?>/challenges/<?= $challenge['id'] ?>/upload"
              enctype="multipart/form-data" class="upload-form">
            <?= csrf_field() ?>
            <div class="upload-dropzone" id="dropzone">
                <input type="file" name="file" id="file-input" required accept=".jpg,.jpeg,.png,.gif,.pdf,.ppt,.pptx,.zip,.doc,.docx,.txt,.mp4,.webm">
                <label for="file-input" class="upload-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Glissez-déposez votre fichier ici</span>
                    <small>ou cliquez pour parcourir</small>
                    <small class="upload-types">JPG, PNG, PDF, PPT, ZIP, DOC, TXT, MP4 (max 5 Mo)</small>
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" id="upload-btn" disabled>
                <i class="fas fa-upload"></i> Envoyer
            </button>
        </form>
    </div>

    <!-- Autres soumissions -->
    <?php if (!empty($allSubmissions)): ?>
    <div class="other-submissions">
        <h2><i class="fas fa-users"></i> Soumissions des autres apprenants (<?= count($allSubmissions) ?>)</h2>
        <div class="submissions-list">
            <?php foreach (array_slice($allSubmissions, 0, 10) as $s): ?>
                <div class="submission-mini">
                    <i class="fas fa-user-circle"></i>
                    <span><?= e($s['user_name'] ?? $s['user_email']) ?></span>
                    <span class="file-name"><i class="fas fa-file"></i> <?= e($s['original_filename']) ?></span>
                    <span class="submission-date"><?= e(date('d/m/Y', strtotime($s['created_at']))) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('file-input');
const uploadBtn = document.getElementById('upload-btn');

fileInput.addEventListener('change', function() {
    uploadBtn.disabled = !this.files.length;
    if (this.files.length) {
        dropzone.classList.add('has-file');
    }
});

dropzone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('dragover');
});

dropzone.addEventListener('dragleave', function() {
    this.classList.remove('dragover');
});

dropzone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        uploadBtn.disabled = false;
        this.classList.add('has-file');
    }
});
</script>
