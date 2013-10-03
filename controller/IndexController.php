<?php
namespace com\appname;

class IndexController extends \com\BaseController {

    public function init() {
        $this->template->setLayout('default');
    }
    
    public function indexAction() {
        
        $this->log->debug("Inside " . __METHOD__ . "()...");

        // set title
        $this->template->title = SITE_TITLE;

        // include css
        $this->template->css = (array) 'global';
        
        // an internal request
        $this->template->adminResponse = \com\Request::factory('admin', 'jobs');

        $this->template
                ->setLayout('default')  // SET THE LAYOUT (OPTIONAL)
                ->show();               // THEN RENDER VIEW FILE

        $this->log->debug("Peak memory usage in " . __METHOD__ . " = " . (memory_get_peak_usage(TRUE) / 1024) . " KB");
    }

}

?>