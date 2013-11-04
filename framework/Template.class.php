<?php
namespace com;

class Template {
    
    private $vars = array();

    /** @var Registry */
    private $registry;
    
    /** @var string */
    private $layout;

    /** @var string */
    private $viewFile;

    /** @var string */
    private $content;

    /**
     *
     * @param Registry $registry to get the controller and action values
     */
    function __construct(Registry &$registry) {
        $this->layout = 'default';
        $this->registry = $registry;
    }

    public function __set($index, $value) {
        $this->vars[$index] = $value;
    }
    
    public function setLayout($layout) {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     *
     * @throws Exception
     */
    function show() {
        require_once 'commonFunctions.inc.php';

        $this->viewFile = VIEW_SCRIPTS.'/'.strtolower($this->registry->controller).'/'.$this->registry->action.'.phtml';

        if (file_exists($this->viewFile) == false)
            throw new \Exception('View File not found in '. $this->viewFile);

        // Load variables
        foreach ($this->vars as $key => $value) {
            $$key = $value;
        }

        ob_start();
        include $this->viewFile;
        $this->content = ob_get_contents();
        ob_clean();
        ob_end_clean();

        if(!is_null($this->layout))
            include LAYOUTS.'/'.$this->layout.'.phtml';
        else
            echo $this->content;
    }
}