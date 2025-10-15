<?php
declare(strict_types=1);

/**
 * Contributors CRUD (simplified).
 * Table: contributors(id, display_name, email, roles, status, created_at, updated_at)
 */

require_once __DIR__ . '/db_functions.php';

function contributors_table(): string { return $_ENV['CONTRIB_TABLE'] ?? 'contributors'; }

function find_contributor_by_id(int $id): ?array {
    $stmt = db()->prepare("SELECT * FROM ".contributors_table()." WHERE id=:id LIMIT 1");
    $stmt->bindValue(':id',$id,PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch() ?: null;
}
function find_contributor_by_email(string $email): ?array {
    $stmt = db()->prepare("SELECT * FROM ".contributors_table()." WHERE email=:e LIMIT 1");
    $stmt->execute([':e'=>$email]);
    return $stmt->fetch() ?: null;
}
function create_contributor(array $args): array {
    $name = trim((string)($args['display_name'] ?? ''));
    $email= trim((string)($args['email'] ?? ''));
    if ($name==='') return ['ok'=>false,'errors'=>['display_name'=>'Name required']];
    if ($email==='' || !filter_var($email,FILTER_VALIDATE_EMAIL)) return ['ok'=>false,'errors'=>['email'=>'Valid email required']];

    $now = date('Y-m-d H:i:s');
    $stmt = db()->prepare("INSERT INTO ".contributors_table()."(display_name,email,roles,status,created_at,updated_at)
                           VALUES(:n,:e,:r,:s,:c,:u)");
    $stmt->execute([
        ':n'=>$name, ':e'=>$email,
        ':r'=>json_encode($args['roles'] ?? []),
        ':s'=>$args['status'] ?? 'active',
        ':c'=>$now, ':u'=>$now
    ]);
    return ['ok'=>true,'id'=>(int)db()->lastInsertId()];
}
function update_contributor(int $id, array $args): array {
    if (!find_contributor_by_id($id)) return ['ok'=>false,'errors'=>['_not_found'=>'Not found']];
    $fields=[]; $params=[':id'=>$id, ':u'=>date('Y-m-d H:i:s')];
    foreach(['display_name','email','roles','status'] as $f){
        if(array_key_exists($f,$args)){
            $fields[]="$f=:$f";
            $params[":$f"]=$f==='roles' ? json_encode($args[$f]) : $args[$f];
        }
    }
    if(!$fields) return ['ok'=>false,'errors'=>['_noop'=>'No fields to update']];
    $sql="UPDATE ".contributors_table()." SET ".implode(', ',$fields).", updated_at=:u WHERE id=:id";
    return ['ok'=>db()->prepare($sql)->execute($params)];
}
function delete_contributor(int $id): bool {
    $stmt = db()->prepare("DELETE FROM ".contributors_table()." WHERE id=:id");
    $stmt->bindValue(':id',$id,PDO::PARAM_INT);
    return $stmt->execute();
}
