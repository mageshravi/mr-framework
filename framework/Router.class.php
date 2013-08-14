<?php

namespace com;

/**
 * @copyright (c) 2011-13, Magesh Ravi
 */
class Router {
    
    private $path;

    private $args = array();

    public $file;

    public $controller;

    public $action; 

    /**
    *
    * @set controller directory path
    *
    * @param string $path
    *
    * @return void
    *
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
        // GET ROUTE
        $this->getController();

        // IF CONTROLLER FILE READABLE
        if (is_readable($this->file) == false) {
            $this->file = $this->path.'/Error404.php';
            $this->controller = 'Error404';
        }

        // INCLUDE CONTROLLER FILE
        include $this->file;

        // NEW INSTANCE OF CONTROLLER
        $class = $this->controller . 'Controller';
        Log::debug("Controller class: ".$class);
        $fullyQualifiedClassName = APP_NAMESPACE.'\\'.$class;
        $controller = new $fullyQualifiedClassName($registry);
        
        // IS ACTION CALLABLE
        if (is_callable(array($controller, $this->action)) == false) {
            $action = 'index';
            $this->action = 'index';
        } else {
            $action = $this->action;
        }
        
        // STORE IN REGISTRY, FOR USE IN TEMPLATE
        $registry->controller = $this->controller;
        $registry->action = $this->action;
        
        // RUN ACTION
        $controller->$action();
    }

    private function getController() {

        // GET ROUTE FROM URL
        $route = (empty($_GET['rt'])) ? '' : $_GET['rt'];

        if (empty($route)) {
            $route = 'Index';
        } else {
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

            $partsCount = count($parts);
            
            for($i=2; $i < $partsCount; $i=$i+2) {
                // GET PARAMETERS
                if(array_key_exists($i+1, $parts))
                    $_GET[$parts[$i]] = $parts[($i+1)];
                else
                    $_GET[$parts[$i]] = "";
                
                Log::debug("GET Parameter : {$parts[$i]} = {$_GET[$parts[$i]]}");
            }
        }

        Log::debug("Route: ".$route);

        if (empty($this->controller)) {
            $this->controller = 'Index';
        }

        Log::debug("Controller: ".$this->controller);

        if (empty($this->action)) {
            $this->action = 'index';
        }

        Log::debug("Action: ".$this->action);

        // SET FILE PATH
        $this->file = $this->path .'/'. $this->controller . 'Controller.php';
    }
}

?>
