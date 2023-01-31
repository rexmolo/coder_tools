<?php

use ReflectionClass;
use Exception;

class Container
{

    private $bindings = [];

    public function set($abstract, callable $factory)
    {
        $this->bindings[$abstract] = $factory;
    }
    
    public  function get($abstract)
    {
       
        if (!empty($this->bindings) && isset($this->bindings[$abstract])) {
         
            return $this->bindings[$abstract]($this);
        }
        $reflextion = new ReflectionClass($abstract);
       
        $dependencies = $this->buildDependencies($reflextion);
       
        return $reflextion->newInstanceArgs($dependencies);
    }

    private function buildDependencies($reflextion)
    {
        if(!$constructor = $reflextion->getConstructor()) {
            return [];
        }
        $params = $constructor->getParameters();

        return array_map(function ($param) {
        
            if (!$type = $param->getType()) {
                throw new Exception();
            }
//  print_r($type);
//         exit();
            return $this->get($type->getName());
        }, $params);

    }
}
