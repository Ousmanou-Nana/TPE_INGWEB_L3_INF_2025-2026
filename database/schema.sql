SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS othTime_db;
CREATE DATABASE othTime_db;

USE othTime_db;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher') NOT NULL DEFAULT 'teacher',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    couleur VARCHAR(7) DEFAULT '#4A90E2',
    heures_par_semaine INT DEFAULT 2
);

CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    effectif INT DEFAULT 30
);


CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    capacite INT DEFAULT 30
);


CREATE TABLE teacher_subject (
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    PRIMARY KEY (teacher_id, subject_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);


CREATE TABLE class_subject (
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    heures_par_semaine INT DEFAULT 2,
    PRIMARY KEY (class_id, subject_id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);


CREATE TABLE preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    jour TINYINT NOT NULL COMMENT '1=Lundi, 2=Mardi, 3=Mercredi, 4=Jeudi, 5=Vendredi',
    periode TINYINT NOT NULL COMMENT '1 à 6',
    score TINYINT NOT NULL DEFAULT 0,
    UNIQUE KEY unique_pref (teacher_id, jour, periode),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);


CREATE TABLE timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    generation_id INT NOT NULL COMMENT 'Identifiant de la génération',
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    room_id INT NOT NULL,
    jour TINYINT NOT NULL,
    periode TINYINT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);


CREATE TABLE timetable_generations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    score_total INT DEFAULT 0,
    nb_conflits INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT DEFAULT 0
);
SET FOREIGN_KEY_CHECKS = 1;