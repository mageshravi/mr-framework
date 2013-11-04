<?php
namespace com;

/**
 * @copyright (c) 2011-13, Magesh Ravi
 */
class Router {

    /** @var Log $log */
    private $log;
    
    private $path;

    private $args = array();

    public $file;

    public $controller;

    public $action;

    /**
     * 
     * @param string $path the controller directory path
     * @throws Exception
     */
    function setPath($path) {
        // CHECK IF PATH IS DIRECTORY
        if (is_dir($path) == false) {
            throw new Exception ('Invalid controller path: `' . $path . '`');
        }

        // SET PATH
        $this->path = $path;
    }

    /**
     *
     * @param Registry $registry to store the controller and action values
     */
    public function loader(Registry &$registry) {
        
        $this->log = Log::getInstance();
        $internalRequest = FALSE;

        if($registry->getRequestType() === 'internal')
            $internalRequest = TRUE;
        
        if($internalRequest)
            $this->resolveFilePath();
        else
            $this->getControllerAndAction(); // for web requests
        
        // is controller file readable?
        if (is_readable($this->file) == false) {
            $this->file = $this->path.'/Error404.php';
            $this->controller = 'Error404';
        }

        // include the controller file
        include_once $this->file;

        // new instance of controller
        $class = $this->controller . 'Controller';
        $fullyQualifiedClassName = APP_NAMESPACE.'\\controller\\'.$class;
        $controller = new $fullyQualifiedClassName($registry);

        $action = $this->action.'Action';
        
        // what if the action is not callable?
        if (is_callable(array($controller, $action)) === false) {
            $action = 'indexAction';
            $this->action = 'index';
        }
        
        // store in registry, for use in template
        $registry->controller = $this->controller;
        $registry->action = $this->action;

        // init, then run
        $controller->init();
        $controller->$action();
    }

    private function getControllerAndAction() {
        // GET ROUTE FROM URL
        $route = filter_input(INPUT_GET, 'rt');
        $this->log->debug("Route: $route");

        if (!is_null($route)) {
            // GET PARTS OF ROUTE
            $parts = explode('/', $route);

            if(isset($parts[0])) {
                // CONTROLLER
                $this->controller = ucfirst($parts[0]);
            }

            if(isset( $parts[1])) {
                // ACTION
                $this->action = str_replace('-','',$parts[1]);  /* Ignore hyphens in url */
            }
        }

        $this->resolveFilePath();
    }

    private function resolveFilePath() {
        if (empty($this->controller)) {
            $this->controller = 'Home';
        }

        if (empty($this->action)) {
            $this->action = 'index';
        }
        
        $this->log->debug("Controller: ".$this->controller);
        $this->log->debug("Action: ".$this->action);

        $this->file = $this->path .'/'. $this->controller . 'Controller.class.php';
    }
}