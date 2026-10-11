<?php
function act($a){}
function view(){global $R,$T;?>
<div class="bar"><label>Date<input type="date" id="d" value="<?=date('Y-m-d')?>"></label><span id="live" class="pill ok">● Temps réel</span></div>
<div class="stats"><div><small>Commandes</small><b id="s1">0</b></div><div><small>Encaissé</small><b id="s2">0 F</b></div><div><small>À encaisser</small><b id="s3">0 F</b></div></div>
<div class="card tw"><table><thead><tr><th>N°</th><th>Heure</th><th>Table</th><th>Téléphone</th><th>Plats</th><th>Total</th><th>Statut</th><th></th></tr></thead><tbody id="tb"></tbody></table></div>
<p class="hint">Cette liste se met à jour automatiquement pour tous les employés connectés.</p>
<script>
var ROLE='<?=e($R)?>',T='<?=e($T)?>',D=document.getElementById('d'),$=function(i){return document.getElementById(i)};
var fm=function(n){return Number(n).toLocaleString('fr-FR')+' F'},x=function(s){return String(s==null?'':s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})};
var LB={en_cours:['En cours','ko'],servie:['Servie','mid'],payee:['Payée','ok']},canS=ROLE!='caissier',canP=ROLE!='cuisinier',last='';
function draw(L){var e=0,a=0,h='';L.forEach(function(o){if(o.status=='payee')e+=+o.total;else a+=+o.total;
 h+='<tr><td><b>'+o.id+'</b></td><td>'+o.h+'</td><td>'+x(o.tbl)+'</td><td><a href="tel:'+x(o.phone)+'">'+x(o.phone)+'</a></td><td>'+x(o.its)+(o.note?'<br><small>📝 '+x(o.note)+'</small>':'')+'</td><td>'+fm(o.total)+'</td><td><span class="pill '+LB[o.status][1]+'">'+LB[o.status][0]+'</span></td><td class="act">'
 +(o.status=='en_cours'&&canS?'<button class="mini" data-a="serve" data-id="'+o.id+'">Servir</button>':'')
 +(o.status!='payee'&&canP?'<button class="mini ok" data-a="pay" data-id="'+o.id+'">Payer</button>':'')
 +'<button class="mini" data-a="reprint" data-id="'+o.id+'">🖨️</button></td></tr>'});
 if(!L.length)h='<tr><td colspan="8" style="text-align:center;color:#888">Aucune commande ce jour.</td></tr>';
 var k=JSON.stringify(L);if(k!==last){$('tb').innerHTML=h;last=k}$('s1').textContent=L.length;$('s2').textContent=fm(e);$('s3').textContent=fm(a)}
function go(body){var o={cache:'no-store'};if(body){body.t=T;body.d=D.value;o.method='POST';o.body=new URLSearchParams(body)}
 return fetch('orders_api.php'+(body?'':'?d='+D.value),o).then(function(r){if(r.status==403){location='index.php';throw 0}return r.json()}).then(function(j){draw(j.orders);$('live').className='pill ok';$('live').textContent='● Temps réel'}).catch(function(){$('live').className='pill ko';$('live').textContent='● Connexion perdue…'})}
$('tb').onclick=function(e){var b=e.target.closest('button');if(!b)return;b.disabled=true;go({a:b.dataset.a,id:b.dataset.id})};
D.onchange=function(){last='';go()};go();
setInterval(function(){if(!document.hidden)go()},3000);
</script>
<?php }
