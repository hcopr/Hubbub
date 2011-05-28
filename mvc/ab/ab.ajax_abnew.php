<?

if($this->myEntry['result'] == 'OK' && $this->myEntry['error'] == '')
{
  ?><div class="banner win">
    <?= l10n('ab.entrycreated') ?> ('<?= htmlspecialchars($this->myEntry['comment']) ?>')
  </div><? 
}
else
{
  ?><div class="banner fail">
    <?= l10n('ab.entryfailed') ?> ('<?= htmlspecialchars($this->myEntry['error']) ?>')
  </div><? 
}

?>