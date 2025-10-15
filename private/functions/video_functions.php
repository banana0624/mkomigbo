<?php
declare(strict_types=1);

/**
 * Videos metadata CRUD (DB-only; actual media storage handled elsewhere).
 * Table: videos(id, owner_id, title, slug, description, duration_sec, source_url, kind, published, created_at, updated_at)
 * kind: 'reel' | 'vlog' | 'other'
 */

require_once __DIR__ . '/db_functions.php';

function videos_table(): string { return $_ENV['VIDEOS_TABLE'] ?? 'videos'; }

function find_video_by_id(int $id): ?array {
    $stmt = db()->prepare("SELECT * FROM ".videos_table()." WHERE id=:id LIMIT 1");
    $stmt->bindValue(':id',$id,PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch() ?: null;
}
function list_videos(array $opts=[]): array {
    $kind = $opts['kind'] ?? null;
    $where = []; $params=[];
    if ($kind) { $where[]='kind = :k'; $params[':k']=$kind; }
    $whereSql = $where ? 'WHERE '.implode(' AND ',$where) : '';
    $sql = "SELECT * FROM ".videos_table()." $whereSql ORDER BY created_at DESC";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
function create_video(array $args): array {
    $title = trim((string)($args['title'] ?? ''));
    $slug  = trim((string)($args['slug'] ?? ''));
    if ($title==='') return ['ok'=>false,'errors'=>['title'=>'Title required']];
    if ($slug==='' || !preg_match('/^[a-z0-9-]+$/',$slug)) return ['ok'=>false,'errors'=>['slug'=>'Valid slug required']];

    $now = date('Y-m-d H:i:s');
    $stmt = db()->prepare("INSERT INTO ".videos_table()."(owner_id,title,slug,description,duration_sec,source_url,kind,published,created_at,updated_at)
                           VALUES(:o,:t,:s,:d,:dur,:u,:k,:p,:c,:u2)");
    $stmt->execute([
        ':o'=>(int)($args['owner_id'] ?? 0),
        ':t'=>$title, ':s'=>$slug, ':d'=>($args['description'] ?? ''),
        ':dur'=>(int)($args['duration_sec'] ?? 0),
        ':u'=>($args['source_url'] ?? ''),
        ':k'=>($args['kind'] ?? 'other'),
        ':p'=>(int)($args['published'] ?? 1),
        ':c'=>$now, ':u2'=>$now
    ]);
    return ['ok'=>true,'id'=>(int)db()->lastInsertId()];
}
function update_video(int $id, array $args): array {
    if (!find_video_by_id($id)) return ['ok'=>false,'errors'=>['_not_found'=>'Video not found']];
    $fields=[]; $params=[':id'=>$id, ':u'=>date('Y-m-d H:i:s')];
    foreach(['owner_id','title','slug','description','duration_sec','source_url','kind','published'] as $f){
        if(array_key_exists($f,$args)){ $fields[]="$f=:$f"; $params[":$f"]=in_array($f,['owner_id','duration_sec','published'],true)?(int)$args[$f]:$args[$f]; }
    }
    if(!$fields) return ['ok'=>false,'errors'=>['_noop'=>'No fields to update']];
    $sql="UPDATE ".videos_table()." SET ".implode(', ',$fields).", updated_at=:u WHERE id=:id";
    return ['ok'=>db()->prepare($sql)->execute($params)];
}
function delete_video(int $id): bool {
    $stmt = db()->prepare("DELETE FROM ".videos_table()." WHERE id=:id");
    $stmt->bindValue(':id',$id,PDO::PARAM_INT);
    return $stmt->execute();
}
