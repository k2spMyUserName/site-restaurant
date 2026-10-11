<?php require 'db.php';
$s=($_GET['s']??'')==='caisse'?'caisse':'cuisine';$ro=role();
if(!($ro==='admin'||$ro===($s==='caisse'?'caissier':'cuisinier'))){header('Location: index.php');exit;}$pr=S('printer_'.$s);?>
<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title>Poste <?=e($s)?> — Dakéré Plus</title><link rel="stylesheet" href="admin.css">
<style>#ticket{display:none}@media print{body>*:not(#ticket){display:none!important}#ticket{display:block;width:72mm;font:14px/1.35 monospace;color:#000}#ticket h1{font:bold 20px monospace;text-align:center;margin:0 0 6px}#ticket hr{border:0;border-top:1px dashed #000;margin:6px 0}#ticket .l{display:flex;justify-content:space-between;gap:6px}#ticket .big{font-size:18px;font-weight:bold}@page{margin:3mm}}</style></head>
<body data-s="<?=e($s)?>"><div class="stn"><h1><?=$s==='cuisine'?'👨‍🍳 Poste Cuisine':'💰 Poste Caisse'?></h1>
<p>Imprimante : <b><?=e($pr)?></b></p><div id="st" class="pill ok">● En attente de commandes…</div>
<p>Tickets imprimés : <b id="cnt">0</b></p><p class="hint">Gardez cette page ouverte. Chaque nouvelle commande s'imprime automatiquement (voir LISEZMOI.txt pour l'impression sans fenêtre).</p>
<p><a href="admin.php">← Administration</a></p></div><div id="ticket"></div>
<script>
var S=document.body.dataset.s,busy=false,n=0,$=function(i){return document.getElementById(i)};
function x(t){return String(t).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
function f(v){return Number(v).toLocaleString('fr-FR')+' FCFA'}
function tk(j){var h='<h1>DAKÉRÉ PLUS</h1><div style="text-align:center">'+(S==='cuisine'?'— CUISINE —':'— CAISSE : À ENCAISSER —')+'</div><hr><div class="big">COMMANDE N° '+j.oid+'</div><div class="big">TABLE : '+x(j.tbl)+'</div>'+(S==='caisse'?'<div>Tél : '+x(j.phone)+'</div>':'')+'<div>'+x(j.created_at)+'</div><hr>';
j.lines.forEach(function(l){h+=S==='cuisine'?'<div class="big">'+l.qty+' x '+x(l.name)+'</div>':'<div class="l"><span>'+l.qty+' x '+x(l.name)+'</span><span>'+f(l.qty*l.price)+'</span></div>'});
if(j.note)h+='<hr><div>Note : '+x(j.note)+'</div>';
if(S==='caisse')h+='<hr><div class="l big"><span>TOTAL</span><span>'+f(j.total)+'</span></div>';
return h+'<hr><div style="text-align:center">Merci !</div>'}
async function poll(){if(busy)return;busy=true;
try{var r=await fetch('jobs.php?s='+S,{cache:'no-store'});if(r.status==403){location='index.php';return}
var L=await r.json();
for(var i=0;i<L.length;i++){$('st').textContent='● Impression commande N° '+L[i].oid+'…';$('ticket').innerHTML=tk(L[i]);
await new Promise(function(o){setTimeout(o,250)});window.print();
await fetch('jobs.php?s='+S,{method:'POST',body:new URLSearchParams({id:L[i].id})});$('cnt').textContent=++n}
$('st').className='pill ok';$('st').textContent='● En attente de commandes…'}
catch(e){$('st').className='pill ko';$('st').textContent='● Connexion perdue, nouvel essai…'}
busy=false}
setInterval(poll,4000);poll();
</script></body></html>
