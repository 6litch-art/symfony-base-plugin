<?php

namespace Base\Composer\Cloner;

interface StubInterface
{
    public function getStubName(): string;
    public function getStubPath(): string;
    public function getStubInputNamespace(): string;
    public function getStubOutputNamespace(): string;

    public function generate();
}
