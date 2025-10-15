<?php
// project-root/private/functions/blog_functions.php

declare(strict_types=1);

/**
 * Blog posts CRUD.
 * Table: blogs(id, author_id, title, slug, body, published, created_at, updated_at)
 */

require_once __DIR__ . '/db_functions.php';

function blogs_table(): string { return $_ENV['BLOGS_TABLE'] ?? 'blogs'; }

function find_blog_by_id(int $id): ?array {
    $stmt = db()->prepare("SELECT * FROM ".blogs_table()." WHERE id = :id LIMIT 1");
    $stmt->bindValue(':id',$id,PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch() ?: null;
}

function list_blogs(array $opts=[]): array {
    $publishedOnly = (bool)($opts['published_only'] ?? false);
    $where = $publishedOnly ? "WHERE published = 1" : "";
    $sql = "SELECT * FROM ".blogs_table()." $where ORDER BY created_at DESC";
    return db()->query($sql)->fetchAll();
}

function create_blog(array $args): array {
    $title = trim((string)($args['title'] ?? ''));
    $slug  = trim((string)($args['slug'] ?? ''));
    if ($title==='') return ['ok'=>false,'errors'=>['title'=>'Title required']];
    if ($slug==='' || !preg_match('/^[a-z0-9-]+$/',$slug)) return ['ok'=>false,'errors'=>['slug'=>'Valid slug required']];

    $now = date('Y-m-d H:i:s');
    $stmt = db()->prepare("INSERT INTO ".blogs_table()."(author_id,title,slug,body,published,created_at,updated_at)
                           VALUES(:a,:t,:s,:b,:p,:c,:u)");
    $stmt->execute([
        ':a'=>(int)($args['author_id'] ?? 0),
        ':t'=>$title, ':s'=>$slug, ':b'=>($args['body'] ?? ''),
        ':p'=>(int)($args['published'] ?? 1),
        ':c'=>$now, ':u'=>$now
    ]);
    return ['ok'=>true,'id'=>(int)db()->lastInsertId()];
}

function update_blog(int $id, array $args): array {
    if (!find_blog_by_id($id)) return ['ok'=>false,'errors'=>['_not_found'=>'Blog not found']];
    $fields=[]; $params=[':id'=>$id, ':u'=>date('Y-m-d H:i:s')];
    foreach(['author_id','title','slug','body','published'] as $f){
        if(array_key_exists($f,$args)){ $fields[]="$f=:$f"; $params[":$f"]=$f==='published'||$f==='author_id'?(int)$args[$f]:$args[$f]; }
    }
    if(!$fields) return ['ok'=>false,'errors'=>['_noop'=>'No fields to update']];
    $sql="UPDATE ".blogs_table()." SET ".implode(', ',$fields).", updated_at=:u WHERE id=:id";
    return ['ok'=>db()->prepare($sql)->execute($params)];
}

function delete_blog(int $id): bool {
    $stmt=db()->prepare("DELETE FROM ".blogs_table()." WHERE id=:id");
    $stmt->bindValue(':id',$id,PDO::PARAM_INT);
    return $stmt->execute();
}
