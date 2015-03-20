<?php

namespace PHPixie\Template;

class Helpers
{
    protected $configData;
    protected $externalExtensions;
    
    protected $extensions;
    protected $methods;
    protected $aliases;
    
    protected $extensionNames = array('php');
    protected $classMap = array(
        'php' => '\PHPixie\Template\Extensions\PHP'
    );
    
    public function __construct($configData, $externalExtensions)
    {
        $this->configData = $configData;
        $this->externalExtensions = $externalExtensions;
    }
    
    public function extensions()
    {
        $this->requireMappedExtensions();
        return $this->extensions;
    }

    public function methods()
    {
        $this->requireMappedExtensions();
        return $this->methods;
    }
    
    public function aliases()
    {
        $this->requireMappedExtensions();
        return $this->aliases;
    }
    
    protected function requireMappedExtensions()
    {
        if($this->extensions === null) {
            $this->mapExtensions();
        }
    }
    
    protected function mapExtensions()
    {
        $extensions = array();
        
        foreach($this->extensionNames as $name) {
            $extension = $this->buildExtension($name);
            $extensions[$extension->name()] = $extension;
        }
        
        foreach($this->externalExtensions as $extension) {
            $extensions[$extension->name()] = $extension;
        }
        
        $methods = array();
        $aliases = array();
        foreach($extensions as $extension) {
            foreach($extension->methods() as $method) {
                $methods[$method] = array($extension, $method);
            }
            
            foreach($extension->aliases() as $alias => $method) {
                $aliases[$alias] = array($extension, $method);
            }
        }
        
        $configAliases = $this->configData->get('aliases', array());
        
        foreach($configAliases as $alias => $config) {
            $aliases[$alias] = array(
                $extensions[$config['extension']],
                $config['method']
            );
        }
        
        $this->extensions = $extensions;
        $this->methods    = $methods;
        $this->aliases    = $aliases;
    }
    
    protected function buildExtension($name)
    {
        $class = $this->classMap[$name];
        return new $class;
    }
}