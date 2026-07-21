<?php

namespace Core;

interface ContainerInterface {
    public function bind(string $class, callable $fn): void;
    public function make(string $class): object;
}

class Container implements ContainerInterface {
    private array $bindings = [];

    public function bind(string $class, callable $fn): void {
        $this->bindings[$class] = $fn;
    }

    public function make(string $class): object {
        if (isset($this->bindings[$class])) {
            return $this->bindings[$class]($this);
        }

        return new $class;
    }
}
