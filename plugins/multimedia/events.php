<?

function multimedia_publish_attachments_register(&$attachment_types)
{
  l10n_load('plugins/multimedia/l10n');
  $attachment_types[] = array('caption' => l10n('mm.attach.pic'), 'editor' => 'multimedia/picture.php');
  $attachment_types[] = array('caption' => l10n('mm.attach.link'), 'editor' => 'multimedia/link.php');
  $attachment_types[] = array('caption' => l10n('mm.attach.text'), 'editor' => 'multimedia/text.php');
}

?>