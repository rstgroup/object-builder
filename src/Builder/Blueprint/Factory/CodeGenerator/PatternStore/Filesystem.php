<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternStore;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternStore;

final class Filesystem implements PatternStore
{
    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function get(string $class): ?string
    {
        $fileFullPath = $this->path . $class;
        if (file_exists($fileFullPath)) {
            /** @var string $content */
            $content = file_get_contents($fileFullPath);

            return $content;
        }

        return null;
    }

    public function save(string $class, string $blueprint): void
    {
        file_put_contents($this->path . $class, $blueprint);
    }
}
