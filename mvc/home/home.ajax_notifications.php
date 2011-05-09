<?

  $eventList = array();
  
  foreach($this->user->getNotifications() as $nds)
  {
    $text = '';
    $link = '';
    $event_type = $nds['n_type'].'/'.$nds['n_tag'];
    switch($event_type)
    {
      case('fpost/new'): {
        $link = HubbubMessage::link($nds['n_msgkey']);
        $text = h2_render_text(l10n('n.'.$event_type), array($nds));
        break; 
      }
      case('friend/added'): 
      case('friend/request'): {
        $link = HubbubEntity::link($nds, true);
        $text = h2_render_text(l10n('n.'.$event_type), array($nds));
        break; 
      }      
    }
    if($text != '') $eventList[] = array('link' => $link, 'text' => $text, 'status' => $nds['n_status'], 'time' => ageToString($nds['n_time']));
  }  
  
  print(json_encode(array(
    'events' => $eventList,
    )));

?>