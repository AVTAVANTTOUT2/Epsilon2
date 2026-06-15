<div class="courses-page">
    <h1 class="page-title">Parcours disponibles</h1>
    <p class="page-subtitle">Rejoignez un parcours et progressez à travers des défis</p>

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
            <a href="/courses/<?= $course['id'] ?>" class="course-card <?= $isJoined ? 'course-joined' : '' ?>">
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
                        <span class="badge badge-joined">Suivi</span>
                    <?php elseif ($canJoin): ?>
                        <span class="badge badge-available">Disponible</span>
                    <?php else: ?>
                        <span class="badge badge-locked">Verrouillé</span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
