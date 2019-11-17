<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern\Generator;
use RstGroup\ObjectBuilder\BuildingError;

final class CodeGenerator implements Factory
{
    /** @var Generator */
    private $generator;

    /** @codeCoverageIgnore */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function create(string $class): callable
    {
        $pattern = $this->generator->create($class);

        $blueprint = eval($pattern);
        if (! is_callable($blueprint)) {
            throw new BuildingError(
                sprintf(
                    'Generated blueprint is not valid %s',
                    (string) $blueprint
                )
            );
        }

        return $blueprint;
    }
}
