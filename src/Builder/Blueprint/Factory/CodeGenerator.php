<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory;

use Nette\PhpGenerator\Closure;
use ReflectionClass;
use ReflectionMethod;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory;

final class CodeGenerator implements Factory
{
    /** @var Closure */
    private $closureGenerator;

    public function __construct()
    {
        $this->closureGenerator = new Closure();
    }

    public function create(string $class): callable
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        $this->closureGenerator->addUse('class');
        $this->closureGenerator->addParameter('data');

        if (null === $constructor) {
            $this->closureGenerator->setBody('return new $class();');

            return eval('return ' . (string) $this->closureGenerator . ';');
        }

        return function(array $data) use ($class) { };
    }

    private function getConstructorParameter(ReflectionMethod $method): array
    {
        $parameters = $method->getParameters();

        if (0 === count($parameters)) {
            return [];
        }

        return [];
    }
}
