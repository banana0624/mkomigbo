<?php
declare(strict_types=1);

/**
 * project-root/private/functions/db_functions.php
 *
 * Helper layer on top of the core PDO connector defined in:
 *   private/assets/database.php  (function db(): PDO)
 *
 * This file does NOT re-declare db(). It only adds small helpers and
 * back-compat shims, guarded with function_exists().
 */

// --- Back-compat alias -------------------------------------------------------
if (!function_exists('db_connect')) {
    /**
     * Legacy alias used in old code.
     */
    function db_connect(): PDO {
        if (function_exists('db')) { return db(); }
        throw new RuntimeException('db() is not available; ensure assets/database.php is loaded in initialize.php.');
    }
}

// --- Transactions ------------------------------------------------------------
if (!function_exists('db_begin')) {
    function db_begin(): void {
        db()->beginTransaction();
    }
}
if (!function_exists('db_commit')) {
    function db_commit(): void {
        db()->commit();
    }
}
if (!function_exists('db_rollback')) {
    function db_rollback(): void {
        if (db()->inTransaction()) db()->rollBack();
    }
}

// --- Low-level helpers -------------------------------------------------------
if (!function_exists('db_query')) {
    /**
     * Prepare/execute and return the PDOStatement.
     * @param string $sql
     * @param array<string, mixed> $params
     */
    function db_query(string $sql, array $params = []): PDOStatement {
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st;
    }
}

if (!function_exists('db_exec')) {
    /**
     * Execute a statement and return affected rows.
     */
    function db_exec(string $sql, array $params = []): int {
        return db_query($sql, $params)->rowCount();
    }
}

if (!function_exists('db_fetch_all')) {
    /**
     * Fetch all rows as associative arrays.
     * @return array<int, array<string, mixed>>
     */
    function db_fetch_all(string $sql, array $params = []): array {
        $st = db_query($sql, $params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('db_fetch_one')) {
    /**
     * Fetch a single row or null.
     * @return array<string, mixed>|null
     */
    function db_fetch_one(string $sql, array $params = []): ?array {
        $st = db_query($sql, $params);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return ($row === false) ? null : $row;
    }
}

if (!function_exists('db_insert')) {
    /**
     * Execute an INSERT and return lastInsertId as int (0 if unavailable).
     */
    function db_insert(string $sql, array $params = []): int {
        db_query($sql, $params);
        $id = db()->lastInsertId();
        return $id !== false ? (int)$id : 0;
    }
}

if (!function_exists('db_exists')) {
    /**
     * Return true if query returns at least one row.
     */
    function db_exists(string $sql, array $params = []): bool {
        $st = db_query($sql, $params);
        return (bool)$st->fetch();
    }
}

// --- Schema helpers (MySQL) --------------------------------------------------
if (!function_exists('db_table_exists')) {
    function db_table_exists(string $table): bool {
        try {
            // Fast path: SHOW TABLES LIKE
            $st = db_query("SHOW TABLES LIKE :t", [':t' => $table]);
            return (bool)$st->fetch(PDO::FETCH_NUM);
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('db_column_exists')) {
    function db_column_exists(string $table, string $column): bool {
        try {
            $st = db_query("SHOW COLUMNS FROM `{$table}` LIKE :c", [':c' => $column]);
            return (bool)$st->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return false;
        }
    }
}

// --- Utility -----------------------------------------------------------------
if (!function_exists('db_try')) {
    /**
     * Run a callable inside try/catch and return ['ok'=>bool,'result'=>mixed,'error'=>string|null]
     * Useful in controllers to surface clean error messages.
     * @param callable $fn
     * @return array{ok:bool,result:mixed,error:?string}
     */
    function db_try(callable $fn): array {
        try {
            return ['ok' => true, 'result' => $fn(db()), 'error' => null];
        } catch (Throwable $e) {
            return ['ok' => false, 'result' => null, 'error' => $e->getMessage()];
        }
    }
}

/* No closing PHP tag */
