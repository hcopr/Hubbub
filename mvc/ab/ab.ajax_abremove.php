<?

if($this->myEntry['result'] == 'OK' && $this->myEntry['error'] == '')
{
  ?><div class="banner win">
    <?= l10n('ab.entryremoved') ?>
  </div><? 
}
else
{
  ?><div class="banner fail">
    <?= l10n('ab.entryremovefailed') ?> ('<?= htmlspecialchars($this->myEntry['error']) ?>')
  </div><? 
}

?>