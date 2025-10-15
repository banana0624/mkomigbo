<?php
declare(strict_types=1);

/**
 * project-root/private/functions/audio_functions.php
 * Audio helpers (table name + CRUD)
 */

if (!function_exists('audio_table')) {
    function audio_table(): string {
        // customize via .env if you like
        return $_ENV['AUDIO_TABLE'] ?? 'audios';
    }
}

/* --- EXAMPLE CRUD (keep whatever you already had; just use audio_table()) --- */

if (!function_exists('find_audio_by_id')) {
    function find_audio_by_id(int $id): ?array {
        $pdo = function_exists('db') ? db() : (function_exists('db_connect') ? db_connect() : null);
        if (!$pdo) return null;
        $sql = "SELECT * FROM " . audio_table() . " WHERE id = :id LIMIT 1";
        $st  = $pdo->prepare($sql);
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

/* add/update/delete helpers here, all using audio_table() */
