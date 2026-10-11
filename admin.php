<?php require 'db.php';
$R=role();if(!in_array($R,['admin','cuisinier','caissier'])){header('Location: index.php');exit;}
if(empty($_SESSION['t']))$_SESSION['t']=bin2hex(random_bytes(16));
$T=$_SESSION['t'];$m=$_SESSION['m']??'';unset($_SESSION['m']);
$ALLOW=['admin'=>['commandes','site','stock'],'cuisinier'=>['commandes'],'caissier'=>['commandes']][$R];
$P=$_GET['p']??'commandes';if(!in_array($P,$ALLOW))$P='commandes';
function done($x){global $P;$_SESSION['m']=$x;header('Location: admin.php?p='.$P);exit;}
$tk='<input type="hidden" name="t" value="'.$T.'">';
require __DIR__.'/mod_'.$P.'.php';
if($_SERVER['REQUEST_METHOD']==='POST'){if(!hash_equals($T,$_POST['t']??''))die('Session expirée, rechargez la page.');act($_POST['a']??'');}
$nav=['commandes'=>'🧾 Commandes','site'=>'🛠️ Modification du site','stock'=>'📦 Gestion du stockage'];
$def=$R==='admin'&&$pdo->query("SELECT COUNT(*) FROM users WHERE pass_hash=''")->fetchColumn()>0;?>
<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Dashboard — Dakéré Plus</title><link rel="stylesheet" href="admin.css"></head><body>
<div class="app"><aside><div class="brand">Dakéré <b>Plus</b></div><small style="color:#cfe9d9;margin:0 12px 10px">Connecté : <?=e($R)?></small>
<?php foreach($nav as $k=>$v)if(in_array($k,$ALLOW)):?><a href="?p=<?=$k?>" class="<?=$k===$P?'on':''?>"><?=$v?></a><?php endif;?>
<hr><?php if($R!=='caissier'):?><a href="station.php?s=cuisine" target="_blank">👨‍🍳 Poste Cuisine</a><?php endif;?><?php if($R!=='cuisinier'):?><a href="station.php?s=caisse" target="_blank">💰 Poste Caisse</a><?php endif;?>
<?php if($R==='admin'):?><hr><a href="qr.php">📱 QR des tables</a><?php endif;?><hr><a href="index.php">🌐 Voir le site</a><a href="index.php?out=1">🚪 Déconnexion</a></aside>
<main><h1><?=$nav[$P]?></h1><?php if($m):?><p class="msg"><?=e($m)?></p><?php endif;?>
<?php if($def):?><p class="warn">⚠️ Des comptes utilisent encore leur mot de passe par défaut. Changez-les dans « Modification du site ».</p><?php endif;?>
<?php view();?></main></div></body></html>
