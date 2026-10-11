<?php require 'db.php';if(role()!=='admin'){header('Location: index.php');exit;}
if(empty($_SESSION['t']))$_SESSION['t']=bin2hex(random_bytes(16));$T=$_SESSION['t'];$m='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals($T,$_POST['t']??''))die('Session expirée, rechargez la page.');
 $a=$_POST['a']??'';$st=$pdo->prepare('REPLACE INTO settings(k,v) VALUES(?,?)');
 if($a==='save'){$st->execute(['tables_count',max(1,min(100,(int)$_POST['n']))]);$st->execute(['qr_required',isset($_POST['req'])?'1':'0']);$m='Réglages enregistrés.';}
 if($a==='regen'){$st->execute(['table_secret',bin2hex(random_bytes(16))]);$m='Nouveaux codes générés : réimprimez-les, les anciens ne fonctionnent plus.';}
}
$n=max(1,min(100,(int)(S('tables_count')?:20)));$req=S('qr_required')!=='0';?>
<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>QR des tables — Dakéré Plus</title>
<style>body{font:16px/1.5 system-ui,sans-serif;margin:0;padding:20px;background:#fffaf0;color:#2a1a10}h1{font-family:Georgia,serif}.box{background:#fff;border:1px solid #eadfc9;border-radius:14px;padding:16px;margin:14px 0;display:flex;gap:14px;flex-wrap:wrap;align-items:center}
input[type=number]{width:80px;padding:8px;border:1px solid #ccc;border-radius:8px}button{background:#0b6b3a;color:#fff;border:0;border-radius:30px;padding:10px 20px;font:inherit;cursor:pointer}button.r{background:#c8281e}
.msg{background:#f2b70540;padding:10px 14px;border-radius:10px}.g{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px}.cd{background:#fff;border:1px solid #ddd;border-radius:14px;padding:14px;text-align:center;break-inside:avoid}
.cd h2{margin:0 0 8px;font-family:Georgia,serif;color:#0b6b3a}.qr{display:flex;justify-content:center;min-height:170px}.cd small{display:block;word-break:break-all;font-size:9px;color:#888;margin-top:6px}@media print{.no{display:none}body{background:#fff}}</style></head><body>
<div class="no"><p><a href="admin.php">← Dashboard</a></p><h1>📱 QR codes des tables</h1><?php if($m):?><p class="msg"><?=e($m)?></p><?php endif;?>
<p>Imprimez une carte par table. Le client scanne, arrive directement sur le menu avec sa table déjà choisie, et peut commander. Sans ce QR code, la commande est refusée.</p>
<form method="post" class="box"><input type="hidden" name="t" value="<?=e($T)?>"><input type="hidden" name="a" value="save"><label>Nombre de tables <input type="number" name="n" min="1" max="100" value="<?=$n?>"></label>
<label><input type="checkbox" name="req" <?=$req?'checked':''?>> Exiger le QR code pour commander (recommandé)</label><button>Enregistrer</button></form>
<form method="post" class="box" onsubmit="return confirm('Les QR codes déjà imprimés cesseront de fonctionner. Continuer ?')"><input type="hidden" name="t" value="<?=e($T)?>"><input type="hidden" name="a" value="regen"><span>Un QR a fuité ou une personne en abuse ?</span><button class="r">Régénérer tous les codes</button></form>
<button onclick="print()">🖨️ Imprimer les cartes</button></div>
<div class="g"><?php for($i=1;$i<=$n;$i++):?><div class="cd"><h2>Table <?=$i?></h2><div class="qr" data-t="<?=$i?>" data-k="<?=e(tok($i))?>"></div><small></small><p style="margin:6px 0 0">Scannez pour commander</p></div><?php endfor;?></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>var B=location.href.replace(/qr\.php.*$/,'index.php');
document.querySelectorAll('.qr').forEach(function(d){var u=B+'?t='+d.dataset.t+'&k='+d.dataset.k;d.nextElementSibling.textContent=u;if(window.QRCode)new QRCode(d,{text:u,width:170,height:170})});</script></body></html>
