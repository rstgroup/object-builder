<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternGenerator;

final class CodeGenerator implements Factory
{
    /** @var PatternGenerator */
    private $generator;

    public function __construct(PatternGenerator $generator)
    {
        $this->generator = $generator;
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
