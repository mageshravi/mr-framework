<?php
namespace com\appname;

class Error404Controller extends \com\BaseController {

    public function init() {
        $this->template->setLayout('default');
    }
    
    public function indexAction() {

        $this->log->debug("Inside " . __METHOD__ . "()...");

        $this->template->title = SITE_TITLE.' - Page not found!';

        $this->template->show();
    }

}
?>
