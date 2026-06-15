PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL DEFAULT '',
    access_code VARCHAR(50) NOT NULL DEFAULT '0 0 0 0',
    email_verified_at DATETIME DEFAULT NULL,
    email_verify_token VARCHAR(64) DEFAULT NULL,
    password_reset_token VARCHAR(64) DEFAULT NULL,
    password_reset_expires_at DATETIME DEFAULT NULL,
    remember_token VARCHAR(64) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS courses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_index INTEGER NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL DEFAULT '',
    icon VARCHAR(50) NOT NULL DEFAULT 'fa-book',
    color VARCHAR(7) NOT NULL DEFAULT '#6366f1',
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS challenges (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_id INTEGER NOT NULL,
    rank_level INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL DEFAULT '',
    challenge_order INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    challenge_id INTEGER NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL DEFAULT '',
    file_size INTEGER NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS evaluations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    submission_id INTEGER NOT NULL,
    evaluator_id INTEGER NOT NULL,
    score INTEGER NOT NULL CHECK(score >= 1 AND score <= 5),
    comment TEXT NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(submission_id, evaluator_id)
);

CREATE TABLE IF NOT EXISTS badges (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL DEFAULT '',
    icon VARCHAR(50) NOT NULL DEFAULT 'fa-circle',
    color VARCHAR(7) NOT NULL DEFAULT '#6366f1',
    badge_level INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_badges (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    badge_id INTEGER NOT NULL,
    earned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE(user_id, badge_id)
);

INSERT OR IGNORE INTO courses (id, course_index, name, description, icon, color) VALUES
(1, 0, 'Apprentissage et transmission', 'Decouvrez les fondamentaux de l''apprentissage par les pairs et maitrisez l''art de transmettre vos connaissances.', 'fa-seedling', '#10b981'),
(2, 1, 'Culture en pot', 'Explorez la culture numerique sous toutes ses formes : code, design, donnees et bien plus encore.', 'fa-flask', '#f59e0b'),
(3, 2, 'Art de l''ouvrage', 'Perfectionnez votre savoir-faire technique et produisez un travail d''excellence, digne d''un artisan du numerique.', 'fa-hammer', '#ef4444'),
(4, 3, 'Arts Associes', 'Elargissez votre horizon en combinant cybersecurite, developpement et competences transversales.', 'fa-shield-halved', '#8b5cf6');

INSERT OR IGNORE INTO badges (id, name, description, icon, color, badge_level) VALUES
(1, 'Apprenti', 'Premier badge obtenu apres avoir complete votre premier defi.', 'fa-graduation-cap', '#6366f1', 2),
(2, 'Compagnon', 'Vous avez prouve votre valeur en validant plusieurs defis.', 'fa-handshake', '#10b981', 3),
(3, 'Passeur', 'Vous transmettez votre savoir en evaluant les travaux des autres.', 'fa-hand-holding-heart', '#f59e0b', 4),
(4, 'Guide', 'Vous etes un pilier de la communaute, reconnu pour votre expertise.', 'fa-star', '#ef4444', 5);

INSERT OR IGNORE INTO challenges (id, course_id, rank_level, title, description, challenge_order) VALUES
(1, 1, 2, 'Presentez-vous', 'Creez une presentation qui explique qui vous etes, votre parcours et vos objectifs d''apprentissage.', 1),
(2, 1, 2, 'Votre methode d''apprentissage', 'Decrivez et illustrez votre methode personnelle pour apprendre efficacement.', 2),
(3, 1, 3, 'Transmission de savoir', 'Creez un tutoriel sur un sujet que vous maitrisez pour le partager avec la communaute.', 3),
(4, 1, 4, 'Mentorat actif', 'Documentez une session de mentorat ou vous avez aide un autre apprenant.', 4),
(5, 1, 5, 'Maitrise pedagogique', 'Produisez une ressource pedagogique complete (cours, serie de tutoriels) sur un sujet avance.', 5),
(6, 2, 2, 'Veille technologique', 'Realisez une synthese de veille technologique sur un sujet d''actualite numerique.', 1),
(7, 2, 2, 'Projet creatif', 'Proposez un projet creatif melant code et design.', 2),
(8, 2, 3, 'Analyse de code', 'Analysez un projet open-source et proposez des ameliorations.', 3),
(9, 2, 4, 'Contribution open-source', 'Contribuez a un projet open-source et documentez votre contribution.', 4),
(10, 3, 2, 'Code propre', 'Soumettez un projet respectant les principes du clean code avec documentation.', 1),
(11, 3, 2, 'Tests automatises', 'Implementez une suite de tests complete pour un projet existant.', 2),
(12, 3, 3, 'Architecture logicielle', 'Concevez et documentez l''architecture d''une application complexe.', 3),
(13, 3, 4, 'Revue de code par les pairs', 'Realisez une revue de code approfondie et proposez un rapport detaille.', 4),
(14, 4, 2, 'Audit de securite', 'Realisez un audit de securite basique d''une application web.', 1),
(15, 4, 2, 'Configuration serveur', 'Mettez en place et securisez un serveur web complet.', 2),
(16, 4, 3, 'CTF / Challenge securite', 'Participez a un challenge de cybersecurite et presentez votre solution.', 3),
(17, 4, 4, 'Rapport de pentest', 'Produisez un rapport de test d''intrusion professionnel.', 4),
(18, 4, 5, 'Projet cybersecurite avance', 'Concevez un outil ou une solution de securite innovante.', 5);
