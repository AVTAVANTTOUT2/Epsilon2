<div class="submissions-list-page">
    <h1 class="page-title"><i class="fas fa-folder-open"></i> Mes Soumissions</h1>

    <?php if (empty($submissions)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Vous n'avez encore rien soumis.</p>
            <a href="/courses" class="btn btn-primary">Voir les parcours</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Défi</th>
                        <th>Parcours</th>
                        <th>Fichier</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $s): ?>
                    <tr>
                        <td><strong><?= e($s['challenge_title']) ?></strong></td>
                        <td style="color:<?= e($s['course_color']) ?>;"><i class="fas fa-circle" style="color:<?= e($s['course_color']) ?>;font-size:8px;"></i> <?= e($s['course_name']) ?></td>
                        <td><i class="fas fa-file"></i> <?= e($s['original_filename']) ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($s['created_at']))) ?></td>
                        <td>
                            <?php if ($s['status'] === 'pending'): ?>
                                <span class="badge badge-pending">En attente</span>
                            <?php else: ?>
                                <span class="badge badge-reviewed">Évalué</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/uploads/<?= e($s['file_path']) ?>" class="btn btn-outline btn-xs" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
