<?php
declare(strict_types=1);

/**
 * Forums: threads + replies (simplified).
 * Tables:
 *  forum_threads(id, author_id, title, slug, body, created_at, updated_at)
 *  forum_replies(id, thread_id, author_id, body, created_at)
 */

require_once __DIR__ . '/db_functions.php';

function forum_threads_table(): string { return $_ENV['FORUM_THREADS_TABLE'] ?? 'forum_threads'; }
function forum_replies_table(): string { return $_ENV['FORUM_REPLIES_TABLE'] ?? 'forum_replies'; }

function list_threads(): array {
    $sql = "SELECT * FROM ".forum_threads_table()." ORDER BY created_at DESC";
    return db()->query($sql)->fetchAll();
}
function find_thread_by_slug(string $slug): ?array {
    $stmt = db()->prepare("SELECT * FROM ".forum_threads_table()." WHERE slug = :s LIMIT 1");
    $stmt->execute([':s'=>$slug]);
    return $stmt->fetch() ?: null;
}
function create_thread(array $args): array {
    $title = trim((string)($args['title'] ?? ''));
    $slug  = trim((string)($args['slug'] ?? ''));
    if ($title==='') return ['ok'=>false,'errors'=>['title'=>'Title required']];
    if ($slug==='' || !preg_match('/^[a-z0-9-]+$/',$slug)) return ['ok'=>false,'errors'=>['slug'=>'Valid slug required']];

    $now = date('Y-m-d H:i:s');
    $stmt = db()->prepare("INSERT INTO ".forum_threads_table()."(author_id,title,slug,body,created_at,updated_at)
                           VALUES(:a,:t,:s,:b,:c,:u)");
    $stmt->execute([
        ':a'=>(int)($args['author_id'] ?? 0),
        ':t'=>$title, ':s'=>$slug, ':b'=>($args['body'] ?? ''),
        ':c'=>$now, ':u'=>$now
    ]);
    return ['ok'=>true,'id'=>(int)db()->lastInsertId()];
}
function list_replies(int $threadId): array {
    $stmt = db()->prepare("SELECT * FROM ".forum_replies_table()." WHERE thread_id = :tid ORDER BY created_at ASC");
    $stmt->execute([':tid'=>$threadId]);
    return $stmt->fetchAll();
}
function add_reply(int $threadId, array $args): array {
    if ($threadId<=0) return ['ok'=>false,'errors'=>['_bad'=>'Invalid thread']];
    $body = trim((string)($args['body'] ?? ''));
    if ($body==='') return ['ok'=>false,'errors'=>['body'=>'Reply required']];

    $stmt = db()->prepare("INSERT INTO ".forum_replies_table()."(thread_id,author_id,body,created_at)
                           VALUES(:tid,:aid,:b,:c)");
    $stmt->execute([
        ':tid'=>$threadId,
        ':aid'=>(int)($args['author_id'] ?? 0),
        ':b'=>$body,
        ':c'=>date('Y-m-d H:i:s')
    ]);
    return ['ok'=>true,'id'=>(int)db()->lastInsertId()];
}
