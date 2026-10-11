<?php require 'db.php';header('Content-Type: application/json; charset=utf-8');
function fail($m){http_response_code(400);die(json_encode(['error'=>$m]));}
$d=json_decode(file_get_contents('php://input'),true);$oid=(int)($_SESSION['rv']??0);
if(!$oid)fail('Passez une commande pour laisser un avis.');
$r=(int)($d['rating']??0);if($r<1||$r>5)fail('Choisissez une note de 1 à 5 étoiles.');
$n=mb_substr(trim($d['name']??''),0,60)?:'Client';$c=mb_substr(trim($d['comment']??''),0,500);
try{$pdo->prepare('INSERT INTO reviews(order_id,name,rating,comment) VALUES(?,?,?,?)')->execute([$oid,$n,$r,$c]);}catch(Exception $e){fail('Un avis a déjà été publié pour cette commande.');}
unset($_SESSION['rv']);echo json_encode(['ok'=>1]);
