<?php
namespace com\appname\controller;

class Error404Controller extends \com\BaseController {

    public function init() {
        $this->template->setLayout('default');
    }
    
    public function indexAction() {

        log_debug("Inside " . __METHOD__ . "()");

        $this->template->title = SITE_TITLE.' - Page not found!';

        $this->template->show();
    }

}