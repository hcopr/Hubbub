<div style="width: 800px;">
  <div class="balloonhelp"><?= l10n('ab.balloon') ?></div>
  <br/>
  <h2 style="padding-bottom: 8px;"><?= l10n('ab') ?></h2>
  
  <div id="mystatus">
    <img src="themes/default/ajax-loader.gif" align="absmiddle"/>
    <?= l10n('ab.lookingyouup') ?>
  </div>
  
</div>  
<script>
  
  setTimeout(function(){
    $('#mystatus').load('<?= actionUrl('ajax_abstatus', $this->name, array('r' => time())) ?>'); }, 500);
  
</script>