<?php

namespace ColdBolt\Autoload;

use ReflectionClass;

class Container
{
    private array $registery = [];
    private array $factory = [];
    private array $instances = [];

    public function set(string $key, callable $resolver): self
    {
        $this->registery[$key] = $resolver;
        return $this;
    }

    public function setInstance($instance): self
    {
        $key = (new \ReflectionClass($instance))->getName();
        $this->instances[$key] = $instance;
        return $this;
    }

    public function setFactory(string $key, callable $resolver): self
    {
        $this->factory[$key] = $resolver;
        return $this;
    }

    public function get(string $key): Object
    {
        if (isset($this->factory[$key])) {
            return $this->factory[$key];
        }

        if (!isset($this->instances[$key])) {
            if (isset($this->registery[$key])) {
                $this->instances[$key] = $this->registery[$key]();
            } else {
                $obj = $this->resolve($key);
                if(!is_null($obj)) {
                    $this->instances[$key] = $obj;
                }
            }
        }

        return $this->instances[$key];
    }

    public function resolve(string $className): ?Object {

        $reflection = new \ReflectionClass($className);
        $parameters = $this->getConstructorParameters($reflection);
        if ($reflection->isInstantiable()) {
            return $reflection->newInstanceArgs($parameters);
        }

        return null;
    }

    private function getConstructorParameters(ReflectionClass $reflection): array
    {
        $constructor = $reflection->getConstructor();

        if(is_null($constructor)) {
            return [];
        }

        $parameters = $constructor->getParameters();
        $constructor_parameters = [];

        foreach ($parameters as $parameter) {
            $paramClass =  $parameter->getType() && !$parameter->getType()->isBuiltin() ? new \ReflectionClass($parameter->getType()->getName()): null;
            if ($paramClass && !$parameter->getType()->isBuiltin()) {
                $constructor_parameters[] = $this->get($paramClass->getName());
            } else {
                $constructor_parameters[] = $parameter->getDefaultValue();
            }
        }

        return $constructor_parameters;
    }
}
