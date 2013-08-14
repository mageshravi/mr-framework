<?php

class Error404Controller extends \com\BaseController {

public function index() {
    
    $this->log->debug("Inside " . __METHOD__ . "()...");
    
    $this->template->title = SITE_TITLE.' - Page not found!';
    
    $this->template->layout = 'default';

    $this->template->show();
}


}
?>
