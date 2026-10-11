<?php require 'db.php';header('Content-Type: application/json; charset=utf-8');
$R=role();if(!in_array($R,['admin','cuisinier','caissier'])){http_response_code(403);die('{}');}
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals($_SESSION['t']??'',$_POST['t']??'')){http_response_code(403);die('{}');}
 $id=(int)($_POST['id']??0);$a=$_POST['a']??'';
 if($a==='serve'&&in_array($R,['admin','cuisinier']))$pdo->prepare("UPDATE orders SET status='servie' WHERE id=? AND status='en_cours'")->execute([$id]);
 elseif($a==='pay'&&in_array($R,['admin','caissier']))$pdo->prepare("UPDATE orders SET status='payee',paid_at=NOW() WHERE id=? AND status<>'payee'")->execute([$id]);
 elseif($a==='reprint')foreach(['cuisine','caisse'] as $s)$pdo->prepare('INSERT INTO print_jobs(order_id,station) VALUES(?,?)')->execute([$id,$s]);}
$d=preg_match('/^\d{4}-\d\d-\d\d$/',$_REQUEST['d']??'')?$_REQUEST['d']:date('Y-m-d');
$q=$pdo->prepare('SELECT o.id,o.tbl,o.phone,o.total,o.note,o.status,TIME_FORMAT(o.created_at,"%H:%i") h,GROUP_CONCAT(CONCAT(i.qty,"× ",i.name) SEPARATOR ", ") its FROM orders o LEFT JOIN order_items i ON i.order_id=o.id WHERE DATE(o.created_at)=? GROUP BY o.id ORDER BY o.id DESC');$q->execute([$d]);
echo json_encode(['orders'=>$q->fetchAll(),'role'=>$R]);
