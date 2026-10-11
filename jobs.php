<?php require 'db.php';header('Content-Type: application/json; charset=utf-8');
$s=$_GET['s']??'';if(!in_array($s,['cuisine','caisse']))die('[]');$ro=role();
if(!($ro==='admin'||$ro===($s==='caisse'?'caissier':'cuisinier'))){http_response_code(403);die('[]');}
if($_SERVER['REQUEST_METHOD']==='POST'){$pdo->prepare('UPDATE print_jobs SET printed=1,printed_at=NOW() WHERE id=? AND station=?')->execute([(int)$_POST['id'],$s]);die('{}');}
$out=[];$q=$pdo->prepare('SELECT name,price,qty FROM order_items WHERE order_id=?');
$j=$pdo->prepare('SELECT j.id,o.id oid,o.tbl,o.phone,o.total,o.note,o.created_at FROM print_jobs j JOIN orders o ON o.id=j.order_id WHERE j.printed=0 AND j.station=? ORDER BY j.id LIMIT 5');$j->execute([$s]);
foreach($j as $r){$q->execute([$r['oid']]);$r['lines']=$q->fetchAll();$out[]=$r;}
echo json_encode($out);
