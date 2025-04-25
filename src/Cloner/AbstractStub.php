<?php

namespace Base\Composer\Cloner;

abstract class AbstractStub
{
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    // Abstract method that will be implemented in the concrete stub classes
    abstract public function generate();

    // Getter for the name (could be useful for debugging or logging)
    public function getName(): string
    {
        return $this->name;
    }
}