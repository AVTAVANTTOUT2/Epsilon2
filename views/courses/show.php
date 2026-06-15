<div class="course-detail">
    <div class="course-detail-header" style="border-bottom: 3px solid <?= e($course['color']) ?>;">
        <a href="/courses" class="back-link"><i class="fas fa-arrow-left"></i> Tous les parcours</a>
        <h1>
            <i class="fas <?= e($course['icon']) ?>" style="color:<?= e($course['color']) ?>;"></i>
            <?= e($course['name']) ?>
        </h1>
        <p><?= e($course['description']) ?></p>

        <div class="course-rank-big">
            <?php $rankName = \App\Models\Course::rankName($currentLevel); ?>
            <?php $rankIcon = \App\Models\Course::rankIcon($currentLevel); ?>
            <?php $rankClass = \App\Models\Course::rankClass($currentLevel); ?>
            <i class="fas <?= e($rankIcon) ?> <?= e($rankClass) ?>"></i>
            <span class="<?= e($rankClass) ?>">Rang actuel : <?= e($rankName) ?></span>
        </div>

        <?php if ($canJoin && !$isJoined): ?>
            <form method="POST" action="/courses/<?= $course['id'] ?>/join" style="margin-top:1rem;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-play"></i> Rejoindre ce parcours
                </button>
            </form>
        <?php elseif (!$canJoin && !$isJoined): ?>
            <div class="locked-message">
                <i class="fas fa-lock"></i>
                Vous devez atteindre le rang <strong>Apprenti</strong> dans le parcours "Apprentissage et transmission" avant de pouvoir débloquer celui-ci.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($isJoined): ?>
    <div class="challenges-section">
        <h2>Défis du parcours</h2>

        <?php
        $challengesByRank = [];
        foreach ($challenges as $ch) {
            $challengesByRank[$ch['rank_level']][] = $ch;
        }

        $lastRank = 0;
        foreach ($challengesByRank as $rankLevel => $rankChallenges):
            // Vérifier quel niveau est le prochain à faire
            $isCurrentRank = ($rankLevel <= $currentLevel + 1);
        ?>
            <div class="rank-group <?= $rankLevel <= $currentLevel ? 'rank-completed' : ($rankLevel === $currentLevel + 1 ? 'rank-current' : 'rank-upcoming') ?>">
                <h3 class="rank-group-title">
                    <i class="fas <?= e(\App\Models\Course::rankIcon($rankLevel)) ?>"></i>
                    Niveau <?= e(\App\Models\Course::rankName($rankLevel)) ?>
                    <?php if ($rankLevel <= $currentLevel): ?>
                        <span class="badge badge-reviewed">Complété</span>
                    <?php elseif ($rankLevel === $currentLevel + 1): ?>
                        <span class="badge badge-pending">En cours</span>
                    <?php else: ?>
                        <span class="badge badge-locked">Verrouillé</span>
                    <?php endif; ?>
                </h3>

                <?php foreach ($rankChallenges as $challenge): ?>
                    <div class="challenge-item <?= $rankLevel <= $currentLevel ? 'challenge-done' : ($rankLevel === $currentLevel + 1 ? 'challenge-available' : 'challenge-locked') ?>">
                        <div class="challenge-info">
                            <span class="challenge-number">#<?= $challenge['challenge_order'] ?></span>
                            <div>
                                <strong><?= e($challenge['title']) ?></strong>
                                <p class="challenge-desc"><?= e($challenge['description']) ?></p>
                            </div>
                        </div>
                        <div>
                            <?php if ($rankLevel <= $currentLevel): ?>
                                <i class="fas fa-check-circle challenge-done-icon"></i>
                            <?php elseif ($rankLevel === $currentLevel + 1): ?>
                                <a href="/courses/<?= $course['id'] ?>/challenges/<?= $challenge['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-upload"></i> Relever
                                </a>
                            <?php else: ?>
                                <i class="fas fa-lock challenge-locked-icon"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
