<div><?= l10n('url.change1') ?></div>

<div class="balloonhelp">
  <?= l10n('url.howtomake') ?>:<br/>
  <textarea style="width: 100%; height: 40px;"><?= htmlspecialchars('<!-- hubbub2:'.json_encode($this->myEntityRecord).' -->') ?></textarea>
</div>

<div style="padding-top: 16px">
<div><?= l10n('url.change2') ?></div>
<div class="balloonhelp">
  <?= l10n('url.howtochange') ?><br/>
	<input type="text" id="mynewurl" value="<?= $this->urlSuggestion ?>"/>
	<input type="button" value="<?= l10n('change') ?>" 
	  onclick="displayLoader(); $.post('<?= actionUrl('ajax_changeurl', 'settings') ?>', { 'newurl' : $('#mynewurl').val() }, function(data) {
	    $('#userurlchange').html(data);
	  });"/>
</div>

</div>

<div style="padding-top: 16px">
  <?php 
if($this->changeResult) {
	if($this->changeResult['result'] == 'OK')
	{
		?><div><?= l10n('url.changeok') ?></div>
		<a class="btn" onclick="displayLoader(); $.post('<?= actionUrl('ajax_commiturl', 'settings') ?>', { 'newurl' : $('#mynewurl').val() }, function(data) {
	      $('#userurlchange').html(data);
	    });"><?= l10n('url.changedo') ?></a><?php
	}
	else 
  {
    ?><div class="merde"><?= l10n('url.change.'.$this->changeResult['reason']) ?></div><?php
  }
}
?>
</div>

<script>
  $("button, input:submit, input:button, a.btn").button();
</script>

