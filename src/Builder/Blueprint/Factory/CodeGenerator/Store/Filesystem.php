<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Store;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Store;

final class Filesystem implements Store
{
    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function get(string $class): ?callable
    {
        $fileFullPath = $this->path . $class;
        if (file_exists($fileFullPath)) {
            return require $fileFullPath;
        }

        return null;
    }

    public function save(string $class, string $blueprint): void
    {
        file_put_contents($this->path . $class, $blueprint);
    }
}
