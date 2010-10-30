<div class="balloonhelp">
  <?= $this->l10n('url.balloon') ?>
</div>

<blockquote id="userurlchange"><?= $this->user->getUrl() ?> [<a 
  onclick="doChangeUrl();"><?= $this->l10n('change') ?></a>]</blockquote>

<script>
  function doChangeUrl()
  {
	  displayLoader();
	  $('#userurlchange').load('<?= actionUrl('ajax_changeurl', 'settings') ?>');
  }

  function displayLoader()
  {
	  $('#userurlchange').append('<div><img src="themes/default/ajax-loader.gif" align="absmiddle"/> loading...</div>');
  }
</script>

