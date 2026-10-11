<?php
function act($a){global $pdo;
 if($a==='save'){$id=(int)($_POST['id']??0);$c=trim($_POST['cat']);$n=trim($_POST['name']);
  if($c===''||$n==='')done('Catégorie et nom obligatoires.');
  $d=trim($_POST['descr']);$p=max(0,(int)$_POST['price']);$b=isset($_POST['best'])?1:0;$ph=up($_FILES['photo']??[]);
  if($id){if($ph){$o=$pdo->prepare('SELECT photo FROM items WHERE id=?');$o->execute([$id]);rm($o->fetchColumn());}
   $pdo->prepare('UPDATE items SET cat=?,name=?,descr=?,price=?,best=?,photo=COALESCE(?,photo) WHERE id=?')->execute([$c,$n,$d,$p,$b,$ph,$id]);}
  else $pdo->prepare('INSERT INTO items(cat,name,descr,price,best,photo) VALUES(?,?,?,?,?,?)')->execute([$c,$n,$d,$p,$b,$ph]);
  done('Menu mis à jour.'.(!$ph&&!empty($_FILES['photo']['name'])?' (photo refusée : JPG/PNG/WEBP, 5 Mo max)':''));}
 if($a==='del'){$o=$pdo->prepare('SELECT photo FROM items WHERE id=?');$o->execute([(int)$_POST['id']]);rm($o->fetchColumn());$pdo->prepare('DELETE FROM items WHERE id=?')->execute([(int)$_POST['id']]);done('Élément supprimé.');}
 if($a==='gadd'){$ph=up($_FILES['photo']??[]);if(!$ph)done('Photo refusée : JPG/PNG/WEBP, 5 Mo max.');$pdo->prepare('INSERT INTO gallery(photo,caption) VALUES(?,?)')->execute([$ph,trim($_POST['caption'])]);done('Photo ajoutée à la galerie.');}
 if($a==='gdel'){$o=$pdo->prepare('SELECT photo FROM gallery WHERE id=?');$o->execute([(int)$_POST['id']]);rm($o->fetchColumn());$pdo->prepare('DELETE FROM gallery WHERE id=?')->execute([(int)$_POST['id']]);done('Photo supprimée.');}
 if($a==='rdel'){$pdo->prepare('DELETE FROM reviews WHERE id=?')->execute([(int)$_POST['id']]);done('Avis supprimé.');}
 if($a==='set'){$s=$pdo->prepare('REPLACE INTO settings VALUES(?,?)');
  foreach(['phone_show','hours','addr','printer_cuisine','printer_caisse'] as $k){$v=trim($_POST[$k]??'');if($v!=='')$s->execute([$k,$v]);}done('Informations enregistrées.');}
 if($a==='pw'){$u=$_POST['u']??'';if(!in_array($u,['cuisinier','caissier','admin']))done('Compte inconnu.');
  if(!auth('admin',$_POST['old']??''))done('Votre mot de passe administrateur est incorrect.');
  if(strlen($_POST['new'])<6||$_POST['new']!==$_POST['new2'])done('Nouveau mot de passe : 6 caractères minimum, identique dans les deux champs.');
  $pdo->prepare('UPDATE users SET pass_hash=? WHERE login=?')->execute([password_hash($_POST['new'],PASSWORD_DEFAULT),$u]);done('Mot de passe du compte « '.$u.' » modifié.');}}
function view(){global $pdo,$tk;$E=null;if(isset($_GET['edit'])){$q=$pdo->prepare('SELECT * FROM items WHERE id=?');$q->execute([(int)$_GET['edit']]);$E=$q->fetch()?:null;}
 $hid=function($a,$id=null)use($tk){return $tk.'<input type="hidden" name="a" value="'.$a.'">'.($id!==null?'<input type="hidden" name="id" value="'.(int)$id.'">':'');};?>
<h2>Menu du restaurant</h2><div class="card tw"><table><?php foreach($pdo->query('SELECT * FROM items ORDER BY cat,id') as $i):?>
<tr><td><?php if($i['photo']):?><img class="th" src="uploads/<?=e($i['photo'])?>"><?php endif;?><b><?=e($i['name'])?></b> <small><?=e($i['cat'])?></small></td><td><?=number_format($i['price'],0,',',' ')?> FCFA</td>
<td class="act"><a class="mini" href="?p=site&edit=<?=$i['id']?>#f">Modifier</a><form method="post" onsubmit="return confirm('Supprimer cet élément ?')"><?=$hid('del',$i['id'])?><button class="mini r" style="color:#fff!important">Supprimer</button></form></td></tr><?php endforeach;?></table></div>
<form method="post" enctype="multipart/form-data" class="card" id="f"><?=$hid('save',$E?$E['id']:0)?><h3><?=$E?'Modifier : '.e($E['name']):'Ajouter un plat / une boisson'?></h3><br>
<div class="grid2"><input name="cat" list="cl" placeholder="Catégorie (existante ou nouvelle)" required value="<?=e($E['cat']??'')?>"><datalist id="cl"><?php foreach($pdo->query('SELECT DISTINCT cat FROM items') as $c):?><option value="<?=e($c['cat'])?>"><?php endforeach;?></datalist>
<input name="name" placeholder="Nom" required value="<?=e($E['name']??'')?>"><input name="price" type="number" min="0" placeholder="Prix en FCFA" required value="<?=e($E['price']??'')?>"></div><br>
<input name="descr" placeholder="Description" value="<?=e($E['descr']??'')?>"><br><br>
<label>📷 Photo <?=$E&&$E['photo']?'(laisser vide pour garder l\'actuelle)':''?><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label><br>
<label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="best" style="width:auto" <?=!empty($E['best'])?'checked':''?>> Mettre en vedette</label><br>
<button class="btn"><?=$E?'Enregistrer':'Ajouter'?></button> <?php if($E):?><a href="?p=site">Annuler</a><?php endif;?></form>
<h2>Galerie photo</h2><div class="card"><?php foreach($pdo->query('SELECT * FROM gallery ORDER BY id DESC') as $g):?><div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--line)"><span><img class="th" src="uploads/<?=e($g['photo'])?>"><?=e($g['caption'])?></span>
<form method="post" onsubmit="return confirm('Supprimer cette photo ?')"><?=$hid('gdel',$g['id'])?><button class="mini">Supprimer</button></form></div><?php endforeach;?>
<br><form method="post" enctype="multipart/form-data" class="grid2"><?=$hid('gadd')?><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required><input name="caption" placeholder="Légende (facultatif)"><button class="btn">Ajouter à la galerie</button></form></div>
<h2>Avis des clients</h2><div class="card"><?php foreach($pdo->query('SELECT * FROM reviews ORDER BY id DESC LIMIT 50') as $v):?><div style="display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--line)"><span><b><?=e($v['name'])?></b> <?=str_repeat('★',$v['rating'])?> <small><?=e($v['created_at'])?></small><br><?=e($v['comment'])?></span>
<form method="post" onsubmit="return confirm('Supprimer cet avis ?')"><?=$hid('rdel',$v['id'])?><button class="mini">Supprimer</button></form></div><?php endforeach;?><p class="hint">Les avis sont publiés automatiquement ; supprimez ici ceux qui sont inappropriés.</p></div>
<h2>Informations et imprimantes</h2><form method="post" class="card"><?=$hid('set')?><div class="grid2">
<label>Téléphone affiché<input name="phone_show" value="<?=e(S('phone_show'))?>"></label><label>Adresse<input name="addr" value="<?=e(S('addr'))?>"></label>
<label>Nom imprimante cuisine<input name="printer_cuisine" value="<?=e(S('printer_cuisine'))?>"></label><label>Nom imprimante caisse<input name="printer_caisse" value="<?=e(S('printer_caisse'))?>"></label></div><br>
<label>Horaires<textarea name="hours" rows="2"><?=e(S('hours'))?></textarea></label><br><button class="btn">Enregistrer</button></form>
<h2>Mots de passe des comptes</h2><form method="post" class="card grid2"><?=$hid('pw')?><select name="u"><option value="cuisinier">Compte cuisinier</option><option value="caissier">Compte caissier</option><option value="admin">Compte admin</option></select><input type="password" name="old" placeholder="Votre mot de passe admin actuel" required><input type="password" name="new" placeholder="Nouveau mot de passe (6 min.)" required><input type="password" name="new2" placeholder="Confirmer le nouveau" required><button class="btn">Modifier</button></form>
<?php }
