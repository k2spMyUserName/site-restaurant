<?php require 'db.php';header('Content-Type: application/json; charset=utf-8');
function fail($m){http_response_code(400);die(json_encode(['error'=>$m]));}
$d=json_decode(file_get_contents('php://input'),true);
$tbl=trim($d['table']??'');$L=$d['items']??[];$note=mb_substr(trim($d['note']??''),0,255);
$staff=in_array(role(),['admin','cuisinier','caissier']);$st=$_SESSION['tbl']??'';
if($staff&&$tbl!==''){}elseif($st!=='')$tbl=$st; // la table vient du QR code, pas du client
elseif(S('qr_required')!=='0')fail('Scannez le QR code posé sur votre table pour commander.');
if(!preg_match('/^[0-9A-Za-z\- ]{1,10}$/',$tbl))fail('Numéro de table invalide.');
if(time()-(int)($_SESSION['lo']??0)<20)fail('Patientez quelques secondes avant une nouvelle commande.');
$c=$pdo->prepare('SELECT COUNT(*) FROM orders WHERE tbl=? AND created_at>NOW()-INTERVAL 10 MINUTE');$c->execute([$tbl]);
if($c->fetchColumn()>=4)fail('Trop de commandes pour cette table. Appelez un serveur.');
$ph=preg_replace('/[^\d+]/','',(string)($d['phone']??''));$dg=strlen(preg_replace('/\D/','',$ph));
if($dg<8||$dg>15||substr_count($ph,'+')>1||(strpos($ph,'+')!==false&&$ph[0]!=='+'))fail('Numéro de téléphone obligatoire et valide.');
if(!is_array($L)||!$L||count($L)>40)fail('Panier vide.');
$ids=array_map('intval',array_keys($L));
$q=$pdo->prepare('SELECT id,name,price FROM items WHERE id IN ('.implode(',',array_fill(0,count($ids),'?')).')');$q->execute($ids);
$rows=[];$tot=0;
foreach($q as $r){$n=min(20,max(1,(int)$L[$r['id']]));$rows[]=[$r['name'],(int)$r['price'],$n];$tot+=$r['price']*$n;}
if(!$rows)fail('Articles introuvables.');
$pdo->beginTransaction();
$pdo->prepare('INSERT INTO orders(tbl,phone,total,note) VALUES(?,?,?,?)')->execute([$tbl,$ph,$tot,$note?:null]);
$id=(int)$pdo->lastInsertId();
$ins=$pdo->prepare('INSERT INTO order_items(order_id,name,price,qty) VALUES(?,?,?,?)');
foreach($rows as $r)$ins->execute([$id,$r[0],$r[1],$r[2]]);
foreach(['cuisine','caisse'] as $s)$pdo->prepare('INSERT INTO print_jobs(order_id,station) VALUES(?,?)')->execute([$id,$s]);
$pdo->commit();
$_SESSION['rv']=$id;$_SESSION['lo']=time(); // autorise un seul avis pour cette commande
echo json_encode(['n'=>$id]);
