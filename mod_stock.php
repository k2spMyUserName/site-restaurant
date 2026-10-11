<?php
function mv($i,$k,$q,$note,$date){global $pdo;$pdo->prepare('INSERT INTO stock_moves(item_id,item_name,type,unit,kind,qty,note,moved_at) VALUES(?,?,?,?,?,?,?,?)')->execute([$i['id'],$i['name'],$i['type'],$i['unit'],$k,$q,$note?:null,$date]);}
function it($id){global $pdo;$q=$pdo->prepare('SELECT * FROM stock_items WHERE id=?');$q->execute([$id]);return $q->fetch();}
function act($a){global $pdo;$id=(int)($_POST['id']??0);$dt=preg_match('/^\d{4}-\d\d-\d\d$/',$_POST['date']??'')?$_POST['date']:date('Y-m-d');
 if($a==='sadd'){$n=trim($_POST['name']);if($n==='')done('Nom obligatoire.');$qt=max(0,(float)$_POST['qty']);
  $pdo->prepare('INSERT INTO stock_items(name,type,unit,qty) VALUES(?,?,?,?)')->execute([$n,trim($_POST['type']),trim($_POST['unit']),$qt]);
  $i=it($pdo->lastInsertId());if($qt>0)mv($i,'entree',$qt,'Stock initial',date('Y-m-d'));done('Élément ajouté au stock.');}
 if($a==='sedit'){$i=it($id);if(!$i)done('Élément introuvable.');$n=trim($_POST['name']);if($n==='')done('Nom obligatoire.');$qt=max(0,(float)$_POST['qty']);
  $pdo->prepare('UPDATE stock_items SET name=?,type=?,unit=?,qty=? WHERE id=?')->execute([$n,trim($_POST['type']),trim($_POST['unit']),$qt,$id]);
  $pdo->prepare('UPDATE stock_moves SET item_name=?,type=?,unit=? WHERE item_id=?')->execute([$n,trim($_POST['type']),trim($_POST['unit']),$id]);
  if(abs($qt-$i['qty'])>0.001)mv(it($id),'ajust',$qt-$i['qty'],'Correction manuelle',date('Y-m-d'));done('Élément modifié.');}
 if($a==='sdel'){$pdo->prepare('UPDATE stock_moves SET item_id=NULL WHERE item_id=?')->execute([$id]);$pdo->prepare('DELETE FROM stock_items WHERE id=?')->execute([$id]);done('Élément supprimé (l\'historique est conservé).');}
 if($a==='smove'){$i=it($id);$k=$_POST['kind']==='entree'?'entree':'sortie';$qt=(float)$_POST['qty'];if(!$i||$qt<=0)done('Choisissez un élément et une quantité.');
  if($k==='sortie'&&$qt>$i['qty'])done('Stock insuffisant : il reste '.(float)$i['qty'].' '.$i['unit'].'.');
  $pdo->prepare('UPDATE stock_items SET qty=qty'.($k==='entree'?'+':'-').'? WHERE id=?')->execute([$qt,$id]);mv($i,$k,$qt,trim($_POST['note']),$dt);done(($k==='entree'?'Entrée':'Sortie').' enregistrée.');}}
function view(){global $pdo,$tk;$g=$_GET;$ed=isset($g['edit'])?it((int)$g['edit']):null;$ST=$pdo->query('SELECT * FROM stock_items ORDER BY type,name')->fetchAll();
 [$w,$p]=mvq($g);$q=$pdo->prepare("SELECT * FROM stock_moves WHERE $w ORDER BY moved_at DESC,id DESC LIMIT 500");$q->execute($p);$M=$q->fetchAll();
 $q=$pdo->prepare("SELECT item_name,type,unit,SUM(IF(kind='entree',qty,0)) ent,SUM(IF(kind='sortie',qty,0)) sor FROM stock_moves WHERE $w GROUP BY item_name,type,unit ORDER BY item_name");$q->execute($p);$SY=$q->fetchAll();
 $types=$pdo->query('SELECT DISTINCT type FROM stock_items UNION SELECT DISTINCT type FROM stock_moves ORDER BY 1')->fetchAll(PDO::FETCH_COLUMN);$today=date('Y-m-d');
 $kl=['entree'=>'Entrée','sortie'=>'Sortie','ajust'=>'Correction'];
 $hid=function($a,$id=null)use($tk){return $tk.'<input type="hidden" name="a" value="'.$a.'">'.($id!==null?'<input type="hidden" name="id" value="'.(int)$id.'">':'');};
 $fv=function($k)use($g){return e($g[$k]??'');};?>
<h2>Stock actuel</h2><div class="card tw"><table><tr><th>Élément</th><th>Type</th><th>Quantité</th><th></th></tr>
<?php foreach($ST as $s):?><tr><td><b><?=e($s['name'])?></b></td><td><?=e($s['type'])?></td><td><?=(float)$s['qty']?> <?=e($s['unit'])?></td>
<td class="act"><a class="mini" href="?p=stock&edit=<?=$s['id']?>#fe">Modifier</a><form method="post" onsubmit="return confirm('Supprimer cet élément du stock ? L\'historique sera conservé.')"><?=$hid('sdel',$s['id'])?><button class="mini r" style="color:#fff!important">Supprimer</button></form></td></tr><?php endforeach;?>
<?php if(!$ST):?><tr><td colspan="4" style="text-align:center;color:#888">Stock vide.</td></tr><?php endif;?></table></div>
<?php if($ed):?><form method="post" class="card" id="fe"><?=$hid('sedit',$ed['id'])?><h3>Modifier : <?=e($ed['name'])?></h3><br><div class="grid2">
<label>Nom<input name="name" value="<?=e($ed['name'])?>" required></label><label>Type<input name="type" list="tl" value="<?=e($ed['type'])?>"></label><label>Unité<input name="unit" value="<?=e($ed['unit'])?>"></label><label>Quantité<input name="qty" type="number" step="0.01" min="0" value="<?=(float)$ed['qty']?>"></label></div><br><button class="btn">Enregistrer</button> <a href="?p=stock">Annuler</a></form><?php endif;?>
<div class="grid2" style="align-items:start">
<form method="post" class="card"><?=$hid('smove')?><h3>Entrée / sortie de stock</h3><br><div style="display:grid;gap:8px"><select name="id" required><option value="">— Élément —</option><?php foreach($ST as $s):?><option value="<?=$s['id']?>"><?=e($s['name'])?> (<?=(float)$s['qty']?> <?=e($s['unit'])?>)</option><?php endforeach;?></select>
<select name="kind"><option value="sortie">Sortie (utilisé / sorti du magasin)</option><option value="entree">Entrée (réapprovisionnement)</option></select>
<input name="qty" type="number" step="0.01" min="0.01" placeholder="Quantité" required><input name="date" type="date" value="<?=$today?>" max="<?=$today?>"><input name="note" maxlength="160" placeholder="Note (facultatif)"><button class="btn">Enregistrer</button></div></form>
<form method="post" class="card"><?=$hid('sadd')?><h3>Ajouter un élément</h3><br><div style="display:grid;gap:8px"><input name="name" placeholder="Nom (ex. Poulet)" required><input name="type" list="tl" placeholder="Type (ex. Viande, Boisson, Dessert…)"><input name="unit" placeholder="Unité (pièce, bouteille, kg…)"><input name="qty" type="number" step="0.01" min="0" placeholder="Quantité initiale" value="0"><button class="btn">Ajouter</button></div></form></div>
<datalist id="tl"><?php foreach($types as $t):?><option value="<?=e($t)?>"><?php endforeach;?></datalist>
<h2>Historique et recherche</h2><form class="card bar"><input type="hidden" name="p" value="stock">
<label>Élément<input name="q" value="<?=$fv('q')?>" placeholder="ex. poulet"></label><label>Type<select name="type"><option value="">Tous</option><?php foreach($types as $t):?><option <?=($g['type']??'')===$t?'selected':''?>><?=e($t)?></option><?php endforeach;?></select></label>
<label>Mouvement<select name="kind"><option value="">Tous</option><?php foreach($kl as $k=>$v):?><option value="<?=$k?>" <?=($g['kind']??'')===$k?'selected':''?>><?=$v?></option><?php endforeach;?></select></label>
<label>Du<input type="date" name="from" value="<?=$fv('from')?>"></label><label>Au<input type="date" name="to" value="<?=$fv('to')?>"></label><button class="btn">Filtrer</button></form>
<p class="hint">Raccourcis : <?php foreach(['Aujourd\'hui'=>[$today,$today],'Hier'=>[date('Y-m-d',strtotime('-1 day')),date('Y-m-d',strtotime('-1 day'))],'7 jours'=>[date('Y-m-d',strtotime('-6 days')),$today],'30 jours'=>[date('Y-m-d',strtotime('-29 days')),$today]] as $l=>$r):?><a href="?p=stock&from=<?=$r[0]?>&to=<?=$r[1]?>&q=<?=urlencode($g['q']??'')?>"><?=$l?></a> · <?php endforeach;?><a href="?p=stock">Tout</a></p>
<p style="margin:10px 0"><a class="btn" href="export.php?<?=e(http_build_query(array_intersect_key($g,array_flip(['q','type','kind','from','to']))))?>">⬇️ Télécharger en Excel</a></p>
<div class="card tw"><table><tr><th>Élément</th><th>Type</th><th>Total entrées</th><th>Total sorties</th></tr><?php foreach($SY as $s):?><tr><td><b><?=e($s['item_name'])?></b></td><td><?=e($s['type'])?></td><td><?=(float)$s['ent']?> <?=e($s['unit'])?></td><td><?=(float)$s['sor']?> <?=e($s['unit'])?></td></tr><?php endforeach;?><?php if(!$SY):?><tr><td colspan="4" style="text-align:center;color:#888">Aucun résultat.</td></tr><?php endif;?></table></div>
<div class="card tw"><table><tr><th>Date</th><th>Élément</th><th>Type</th><th>Mouvement</th><th>Quantité</th><th>Note</th></tr><?php foreach($M as $r):?><tr><td><?=e($r['moved_at'])?></td><td><?=e($r['item_name'])?></td><td><?=e($r['type'])?></td><td><span class="pill <?=$r['kind']==='entree'?'ok':($r['kind']==='sortie'?'ko':'mid')?>"><?=$kl[$r['kind']]?></span></td><td><?=(float)$r['qty']?> <?=e($r['unit'])?></td><td><?=e($r['note'])?></td></tr><?php endforeach;?></table></div>
<?php }
