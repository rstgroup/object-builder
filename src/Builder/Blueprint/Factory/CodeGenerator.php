<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory;

final class CodeGenerator implements Factory
{
    /** @var Factory\CodeGenerator\Anonymous */
    private $generator;

    public function __construct()
    {
        $this->generator = new Factory\CodeGenerator\Anonymous();
    }

    public function create(string $class): callable
    {
        $blueprint = $this->generator->create($class);
        $prefix = '<?php';

        if (substr($blueprint, 0, strlen($prefix)) === $prefix) {
            $blueprint = substr($blueprint, strlen($prefix));
        }

        return eval($blueprint . ';');
    }
}
