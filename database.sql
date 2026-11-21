CREATE DATABASE IF NOT EXISTS gestion_clubes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_clubes;

-- 1. Tabla Clubes
CREATE TABLE Clubes (
    club_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    deporte VARCHAR(50) NOT NULL,
    ubicacion VARCHAR(120) NOT NULL,
    fecha_fundacion DATE NOT NULL
);

-- 2. Tabla Miembros
CREATE TABLE Miembros (
    miembro_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    club_id INT NULL,
    CONSTRAINT fk_miembro_club FOREIGN KEY (club_id) REFERENCES Clubes(club_id) ON DELETE SET NULL
);

