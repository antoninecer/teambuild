<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

final class GameRepository
{
    public function all(): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->query(
            'SELECT id, name, slug, status, starts_at, ends_at, registration_enabled
             FROM games
             ORDER BY starts_at ASC, id ASC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM games
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $game = $stmt->fetch();

        return $game ?: null;
    }

    public function existsBySlug(string $slug): bool
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT id
             FROM games
             WHERE slug = :slug
             LIMIT 1'
        );

        $stmt->execute(['slug' => $slug]);

        return (bool) $stmt->fetch();
    }

    public function findBySlug(string $slug): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM games
             WHERE slug = :slug
             LIMIT 1'
        );

        $stmt->execute(['slug' => $slug]);
        $game = $stmt->fetch();

        return $game ?: null;
    }

    public function create(array $data): int
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'INSERT INTO games (
                name,
                slug,
                description,
                intro_text,
                starts_at,
                ends_at,
                registration_enabled,
                status,
                map_center_lat,
                map_center_lon,
                map_default_zoom,
                session_cookie_days,
                created_by
            ) VALUES (
                :name,
                :slug,
                :description,
                :intro_text,
                :starts_at,
                :ends_at,
                :registration_enabled,
                :status,
                :map_center_lat,
                :map_center_lon,
                :map_default_zoom,
                :session_cookie_days,
                :created_by
            )'
        );

        $stmt->execute([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'intro_text' => $data['intro_text'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'registration_enabled' => $data['registration_enabled'],
            'status' => $data['status'],
            'map_center_lat' => $data['map_center_lat'],
            'map_center_lon' => $data['map_center_lon'],
            'map_default_zoom' => $data['map_default_zoom'],
            'session_cookie_days' => $data['session_cookie_days'],
            'created_by' => $data['created_by'],
        ]);

        return (int) $pdo->lastInsertId();
    }
}
