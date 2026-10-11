<?php require 'db.php';
if(isset($_GET['out'])){$_SESSION=[];session_destroy();header('Location: index.php');exit;}
if(($_GET['as']??'')==='client'){$_SESSION['role']='client';header('Location: index.php');exit;}
if(isset($_GET['t'],$_GET['k'])){if(preg_match('/^\d{1,3}$/',(string)$_GET['t'])&&hash_equals(tok($_GET['t']),(string)$_GET['k'])){$_SESSION['tbl']=(string)(int)$_GET['t'];if(!role())$_SESSION['role']='client';}header('Location: index.php');exit;}
if(empty($_SESSION['t']))$_SESSION['t']=bin2hex(random_bytes(16));$err='';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['a']??'')==='emp'){
 if(hash_equals($_SESSION['t'],$_POST['t']??'')){$u=$_POST['u']??'';$w=lg_wait($u);
  if($w>0){$mn=(int)ceil($w/60);$err='Trop de tentatives : connexion bloquée. Réessayez dans '.$mn.' minute'.($mn>1?'s':'').'.';}
  else{$r=auth($u,$_POST['p']??'');
   if($r){lg_ok($u);session_regenerate_id(true);$_SESSION['role']=$r;header('Location: index.php');exit;}
   lg_fail($u);sleep(1);$err='Identifiant ou mot de passe incorrect.';}}else $err='Session expirée, réessayez.';}
$R=role();$stf=in_array($R,['admin','cuisinier','caissier']);$TB=$_SESSION['tbl']??'';$REQ=S('qr_required')!=='0'&&!$stf&&$TB==='';
$G=$pdo->query('SELECT * FROM gallery ORDER BY id DESC')->fetchAll();
$set=$pdo->query('SELECT k,v FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
if(!$R){require 'landing.php';exit;}
$cats=[];foreach($pdo->query('SELECT * FROM items ORDER BY id') as $i)$cats[$i['cat']][]=$i;?>
<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dakéré Plus — Restaurant Sénégalais à Douala</title><link rel="stylesheet" href="style.css?v=<?=filemtime(__DIR__.'/style.css')?>"></head><body>
<nav><div class="w"><div class="logo">Dakéré <b>Plus</b></div><div class="nl"><a href="#menu">Menu</a><?php if($G):?><a href="#galerie">Galerie</a><?php endif;?><a href="#avis">Avis</a><a href="#contact">Contact</a></div><a class="btn g" href="#cart" style="padding:8px 18px;font-size:13px">🛒 Ma commande <span id="nb">0</span></a></div></nav>
<header class="hero"><div class="w"><span class="tag">Saveurs authentiques du Sénégal · Douala</span><h1>Dakéré <span>Plus</span></h1>
<p>Thieboudienne, yassa, mafé… Installez-vous, choisissez vos plats ici et passez commande directement depuis votre table.</p>
<div class="b"><a class="btn" href="#menu">Voir le menu</a></div></div></header>
<section id="menu" class="alt"><div class="w"><div class="t"><h2>Notre menu</h2><p>Touchez « Ajouter » pour composer votre commande</p></div>
<div class="tabs"><?php $f=1;foreach($cats as $c=>$_):?><button class="<?=$f?'on':''?>" data-c="<?=e($c)?>"><?=e($c)?></button><?php $f=0;endforeach;?></div>
<div class="grid"><?php foreach($cats as $c=>$L)foreach($L as $i):?>
<div class="c" data-c="<?=e($c)?>"><?php if($i['photo']):?><img loading="lazy" src="uploads/<?=e($i['photo'])?>" alt="<?=e($i['name'])?>"><?php else:?><div class="ph"><?=e(mb_substr($i['name'],0,1))?></div><?php endif;?>
<div class="bd"><div><h3><?=e($i['name'])?><?=$i['best']?'<span class="best">Best</span>':''?></h3><p><?=e($i['descr'])?></p></div>
<div class="p"><b><?=number_format($i['price'],0,',',' ')?> FCFA</b><button class="add" data-id="<?=$i['id']?>" data-n="<?=e($i['name'])?>" data-p="<?=(int)$i['price']?>">+ Ajouter</button></div></div></div><?php endforeach;?></div>
<div class="cart" id="cart"><h3>🛒 Ma commande</h3><div id="lines"><p style="color:var(--mut)">Votre panier est vide.</p></div><div class="tot"><span>Total</span><span id="tot">0 FCFA</span></div>
<div style="display:grid;gap:10px"><?php if($TB!==''):?><input id="tb" value="<?=e($TB)?>" readonly style="font-weight:700;background:rgba(11,107,58,.1)"><p style="color:var(--g);font-weight:600">✅ Table <?=e($TB)?></p><?php elseif($REQ):?><input id="tb" type="hidden"><p style="color:var(--r);font-weight:600">📱 Pour commander, scannez le QR code posé sur votre table.</p><?php else:?><input id="tb" placeholder="Numéro de la table (obligatoire)" maxlength="10" inputmode="numeric"><?php endif;?><input id="ph" type="tel" placeholder="Votre numéro de téléphone (obligatoire)" maxlength="20" autocomplete="tel"><input id="nt" placeholder="Précision pour la cuisine (facultatif)" maxlength="200"></div><br>
<button class="btn g" style="width:100%" id="send">Commander</button><p id="err" style="color:var(--r);margin-top:8px"></p><p style="color:var(--mut);font-size:13px;margin-top:8px">Le règlement se fait à la caisse après le repas.</p></div></div></section>
<?php if($G):?><section id="galerie"><div class="w"><div class="t"><h2>Galerie</h2></div><div class="gal"><?php foreach($G as $g):?><figure><a href="uploads/<?=e($g['photo'])?>" target="_blank"><img loading="lazy" src="uploads/<?=e($g['photo'])?>" alt="<?=e($g['caption'])?>"></a><?php if($g['caption']):?><figcaption><?=e($g['caption'])?></figcaption><?php endif;?></figure><?php endforeach;?></div></div></section><?php endif;?>
<?php $st=$pdo->query('SELECT COUNT(*) n,AVG(rating) a FROM reviews')->fetch();$n=(int)$st['n'];$mo=$n?round($st['a'],1):0;$AV=$pdo->query('SELECT name,rating,comment,created_at FROM reviews ORDER BY id DESC LIMIT 50')->fetchAll();?>
<section id="avis" class="alt"><div class="w"><div class="t"><div class="avh"><h2>Avis des clients</h2><?php if($n):?><div class="avg"><span class="av"><span class="b">★★★★★</span><span class="f" style="width:<?=$mo*20?>%">★★★★★</span></span><b><?=$mo?>/5</b><small>(<?=$n?> avis)</small></div><?php endif;?></div>
<?php if(!$n):?><p>Soyez le premier à donner votre avis après votre commande !</p><?php endif;?></div>
<div class="rvl"><?php foreach($AV as $v):?><div class="rvc"><div class="rh"><b><?=e($v['name'])?></b><span class="rs"><?php for($k=1;$k<=5;$k++):?><i class="<?=$k<=$v['rating']?'on':''?>">★</i><?php endfor;?></span></div><?php if(trim($v['comment'])!==''):?><p class="cm"><?=e($v['comment'])?></p><?php endif;?><small><?=e(substr($v['created_at'],0,10))?></small></div><?php endforeach;?></div></div></section>
<footer id="contact"><div class="w"><div class="grid"><div><h3>Dakéré Plus</h3><p>Restaurant sénégalais<br><?=e($set['addr'])?></p></div><div><h3>Horaires</h3><p><?=nl2br(e($set['hours']))?></p></div>
<div><h3>Contact</h3><p>📞 <a href="tel:+237<?=e(preg_replace('/\D/','',$set['phone_show']))?>"><?=e($set['phone_show'])?></a></p></div></div>
<p style="text-align:center;opacity:.6;margin-top:18px">© <?=date('Y')?> Dakéré Plus · <a href="?out=1" style="opacity:.6">Changer de profil</a><?php if($R==='admin'):?> · <a href="admin.php"><b>Admin</b></a><?php elseif($R!=='client'):?> · <a href="admin.php"><b>Dashboard</b></a><?php endif;?></p></div></footer>
<div id="ok" class="ov"><div class="md"><div style="font-size:48px">✅</div><h2>Commande envoyée !</h2><p>Votre numéro de commande</p><div class="num" id="no"></div><p>La cuisine prépare vos plats. Gardez ce numéro et présentez-le à la caisse pour régler.</p>
<div id="rv" style="width:100%;border-top:1px solid var(--line);padding-top:14px;display:grid;gap:8px"><b>Votre avis sur l'application ?</b><div class="rate" id="st"><?php for($k=1;$k<=5;$k++):?><button type="button" class="star" aria-pressed="false" aria-label="<?=$k?> étoile">★</button><?php endfor;?></div><p class="hint2">Touchez les étoiles pour noter (retouchez pour retirer)</p>
<input id="rn" placeholder="Votre prénom (facultatif)" maxlength="60"><textarea id="rc" rows="3" placeholder="Votre commentaire (facultatif)" maxlength="500" style="width:100%;padding:12px;border:1px solid var(--line);border-radius:12px;font:inherit"></textarea><p id="re" style="color:var(--r)"></p>
<button class="btn g" id="rs">Publier mon avis</button><button class="btn" style="background:none;color:var(--mut)" onclick="location.reload()">Plus tard</button></div></div></div>
<script>
var C={},$=function(i){return document.getElementById(i)},fm=function(n){return n.toLocaleString('fr-FR')+' FCFA'},esc=function(s){return s.replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})};
function tab(c){document.querySelectorAll('.tabs button').forEach(function(b){b.classList.toggle('on',b.dataset.c==c)});document.querySelectorAll('.c').forEach(function(x){x.style.display=x.dataset.c==c?'':'none'})}
document.querySelectorAll('.tabs button').forEach(function(b){b.onclick=function(){tab(b.dataset.c)}});var f=document.querySelector('.tabs button');if(f)tab(f.dataset.c);
document.querySelectorAll('.add').forEach(function(b){b.onclick=function(){var i=b.dataset.id;C[i]=C[i]||{n:b.dataset.n,p:+b.dataset.p,q:0};C[i].q++;rc()}});
$('lines').onclick=function(e){var i=e.target.dataset.id;if(!i)return;C[i].q+=+e.target.dataset.d;if(C[i].q<1)delete C[i];rc()};
function rc(){var t=0,q=0,h='';Object.keys(C).forEach(function(i){var c=C[i];t+=c.p*c.q;q+=c.q;h+='<div class="row"><span>'+esc(c.n)+'</span><span><button data-id="'+i+'" data-d="-1">−</button> '+c.q+' <button data-id="'+i+'" data-d="1">+</button></span><b>'+fm(c.p*c.q)+'</b></div>'});
$('lines').innerHTML=h||'<p style="color:var(--mut)">Votre panier est vide.</p>';$('tot').textContent=fm(t);$('nb').textContent=q}
$('send').onclick=function(){var b=$('send'),er=$('err');er.textContent='';
if(!Object.keys(C).length){er.textContent="Ajoutez d'abord un plat 😊";return}
if(!$('tb').value.trim()){er.textContent=<?=json_encode($REQ?'Scannez le QR code posé sur votre table pour commander.':'Indiquez le numéro de table.')?>;return}
var ph=$('ph').value.replace(/[^\d+]/g,'');if(ph.replace(/\D/g,'').length<8){er.textContent='Indiquez un numéro de téléphone valide (obligatoire).';return}
var it={};Object.keys(C).forEach(function(i){it[i]=C[i].q});b.disabled=true;b.textContent='Envoi en cours…';
fetch('order.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({table:$('tb').value.trim(),phone:ph,note:$('nt').value,items:it})})
.then(function(r){return r.json()}).then(function(d){if(d.n){$('no').textContent='N° '+d.n;$('ok').style.display='flex'}else{er.textContent=d.error||'Erreur, réessayez.';b.disabled=false;b.textContent='Commander'}})
.catch(function(){er.textContent='Connexion impossible, réessayez.';b.disabled=false;b.textContent='Commander'})};
</script><script>
(function(){var box=document.getElementById('st'),re=document.getElementById('re'),bt=document.getElementById('rs');
box.addEventListener('click',function(e){var b=e.target.closest('.star');if(!b)return;var on=!b.classList.contains('on');b.classList.toggle('on',on);b.setAttribute('aria-pressed',on)});
bt.addEventListener('click',function(){var r=box.querySelectorAll('.star.on').length;if(!r){re.textContent='Touchez les étoiles pour donner votre note.';return}
re.textContent='';bt.disabled=true;
fetch('review.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({rating:r,name:document.getElementById('rn').value,comment:document.getElementById('rc').value})})
.then(function(x){return x.json()}).then(function(d){if(d.ok){location.href='index.php#avis';location.reload()}else{re.textContent=d.error||'Erreur.';bt.disabled=false}})
.catch(function(){re.textContent='Connexion impossible.';bt.disabled=false})});})();
</script><script>
(function(){var L=document.querySelectorAll('.nl a'),S=[];L.forEach(function(a){var t=document.querySelector(a.getAttribute('href'));if(t)S.push([a,t])});
function f(){var y=window.scrollY+130,c=null;S.forEach(function(x){if(x[1].offsetTop<=y)c=x[0]});L.forEach(function(a){a.classList.toggle('on',a===c)})}
addEventListener('scroll',f,{passive:true});f()})();
</script>
</body></html>
