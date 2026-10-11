<?php // Page d'accueil : $G (galerie), $err, $_SESSION['t'] ?>
<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Bienvenue — Dakéré Plus</title>
<style>
*{box-sizing:border-box;margin:0}html,body{height:100%}body{font:16px/1.6 Poppins,system-ui,sans-serif;color:#fff;overflow:hidden;background:linear-gradient(135deg,#0b6b3a,#084d2a 60%,#5c1410)}
.sl{position:fixed;inset:0;background-size:cover;background-position:center;opacity:0;transition:opacity 2.5s ease}.sl.on{opacity:1;animation:z 14s linear forwards}
@keyframes z{from{transform:scale(1)}to{transform:scale(1.12)}}
.shade{position:fixed;inset:0;background:linear-gradient(rgba(8,40,22,.72),rgba(60,12,8,.78))}
.mid{position:relative;z-index:2;min-height:100%;display:flex;align-items:center;justify-content:center;text-align:center;padding:24px}
.box{max-width:560px;width:100%}.tag{color:#f2b705;letter-spacing:3px;font-size:13px;text-transform:uppercase}
h1{font:800 clamp(44px,11vw,84px)/1.05 Georgia,serif;margin:10px 0}h1 span{color:#f2b705}
.box>p{opacity:.92;margin:0 auto 28px;max-width:460px}
.btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.b{border:0;border-radius:50px;padding:15px 38px;font:700 17px inherit;cursor:pointer;min-width:170px;color:#fff;background:#c8281e;box-shadow:0 8px 24px rgba(0,0,0,.35);text-decoration:none;display:inline-block}.b.y{background:#f2b705;color:#2a1a10}
.b:hover{transform:translateY(-2px)}
#emp{margin:26px auto 0;max-width:340px;background:rgba(255,250,240,.96);color:#2a1a10;border-radius:20px;padding:22px;display:none;gap:10px;text-align:left}
#emp.show{display:grid}#emp input{width:100%;padding:12px;border:1px solid #eadfc9;border-radius:12px;font:inherit}#emp .err{color:#c8281e;font-size:14px}
#emp .b{width:100%;background:#0b6b3a;box-shadow:none}#emp a{text-align:center;color:#6b5a4c;font-size:14px;cursor:pointer}
</style></head><body>
<div id="bg"></div><div class="shade"></div>
<div class="mid"><div class="box"><span class="tag">Teranga · Douala</span><h1>Dakéré <span>Plus</span></h1>
<p>Bienvenue chez nous ! Installez-vous, régalez-vous : thieboudienne, yassa, mafé… la chaleur de la teranga sénégalaise à votre table.</p>
<div class="btns" id="ch"><a class="b y" href="?as=client">Client</a><button class="b" id="be" type="button">Employé</button></div>
<form id="emp" method="post" class="<?=$err?'show':''?>"><input type="hidden" name="a" value="emp"><input type="hidden" name="t" value="<?=e($_SESSION['t'])?>">
<b style="font-family:Georgia,serif;font-size:19px">Espace employé</b><?php if($err):?><div class="err"><?=e($err)?></div><?php endif;?>
<input name="u" placeholder="Identifiant" autocomplete="username" autocapitalize="none" required><input name="p" type="password" placeholder="Mot de passe" autocomplete="current-password" required><button class="b">Se connecter</button><a id="bk">← Retour</a></form></div></div>
<script>
var P=<?=json_encode(array_map(function($g){return 'uploads/'.$g['photo'];},$G))?>,bg=document.getElementById('bg'),i=-1,els=[];
P.forEach(function(u){var d=document.createElement('div');d.className='sl';d.dataset.u=u;bg.appendChild(d);els.push(d)});
function nx(){if(!els.length)return;var o=els[i];i=(i+1)%els.length;var c=els[i];if(!c.style.backgroundImage)c.style.backgroundImage='url("'+c.dataset.u+'")';
 c.classList.remove('on');void c.offsetWidth;c.classList.add('on');if(o&&o!==c)o.classList.remove('on')}
nx();if(els.length>1)setInterval(nx,8000);
var f=document.getElementById('emp'),c=document.getElementById('ch');
function sh(v){f.classList.toggle('show',v);c.style.display=v?'none':'flex';if(v)f.u.focus()}
document.getElementById('be').onclick=function(){sh(true)};document.getElementById('bk').onclick=function(){sh(false)};
if(f.classList.contains('show'))c.style.display='none';
</script></body></html>
