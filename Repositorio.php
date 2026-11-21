<?php
// Repositorio.php
declare(strict_types=1);
require_once __DIR__ . '/Conexion.php';

// 1. Clase Entidad: Club
class Club
{
    public function __construct(
        public ?int $club_id,
        public string $nombre,
        public string $deporte,
        public string $ubicacion,
        public string $fecha_fundacion
    ) {}
}

// 2. Clase Entidad: Miembro
class Miembro
{
    public function __construct(
        public ?int $miembro_id,
        public string $nombre,
        public string $apellido,
        public string $email,
        public string $telefono,
        public ?int $club_id = null
    ) {}
}

// 3. Clase Repositorio (Operaciones en Base de Datos con PDO)
class ClubDeportivoRepositorio
{
    public function __construct(private PDO $pdo) {}

    // Registrar un nuevo Club
    public function registrarClub(Club $c): int
    {
        $sql = "INSERT INTO Clubes (nombre, deporte, ubicacion, fecha_fundacion) 
                VALUES (:nombre, :deporte, :ubicacion, :fecha)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nombre'    => $c->nombre,
            ':deporte'   => $c->deporte,
            ':ubicacion' => $c->ubicacion,
            ':fecha'     => $c->fecha_fundacion,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    // Listar todos los Clubes
    public function listarClubes(): array
    {
        $sql = "SELECT club_id, nombre, deporte, ubicacion, fecha_fundacion 
                FROM Clubes ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll();
    }

    // Registrar un nuevo Miembro
    public function registrarMiembro(Miembro $m): int
    {
        $sql = "INSERT INTO Miembros (nombre, apellido, email, telefono, club_id) 
                VALUES (:nombre, :apellido, :email, :telefono, :club_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nombre'   => $m->nombre,
            ':apellido' => $m->apellido,
            ':email'    => $m->email,
            ':telefono' => $m->telefono,
            ':club_id'  => $m->club_id,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    // Listar todos los Miembros junto con el nombre de su Club
    public function listarMiembros(): array
    {
        $sql = "SELECT m.miembro_id, m.nombre, m.apellido, m.email, m.telefono, 
                       c.nombre AS nombre_club
                FROM Miembros m
                LEFT JOIN Clubes c ON m.club_id = c.club_id
                ORDER BY m.apellido, m.nombre";
        return $this->pdo->query($sql)->fetchAll();
    }
}