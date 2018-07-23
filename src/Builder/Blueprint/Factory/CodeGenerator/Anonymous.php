<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator;


use Nette\PhpGenerator\Closure;
use ReflectionClass;
use ReflectionMethod;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Complex;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Scalar;

final class Anonymous
{
    private const FILE_BEGIN_WITH = '<?php return %s';
    private const FUNCTION_PATTERN =
'function(array $data) use ($class): string {
    return %s;
}';

    public function __construct()
    {
        $this->closureGenerator = new Closure();
    }

    public function create(string $class): string
    {
        $reflection = new ReflectionClass($class);

        return sprintf(
            self::FILE_BEGIN_WITH,
            sprintf(
                self::FUNCTION_PATTERN,
                $this->getNode($reflection)
            )
        );
    }

    private function getNode(ReflectionClass $class): Node
    {
        $constructor = $class->getConstructor();

        if (null === $constructor) {
            return new Complex($class->getName());
        }

        return $this->getNodes($constructor);
    }

    private function getNodes(ReflectionMethod $method): Node
    {
        $node = new Complex($method->getDeclaringClass()->getName());

        foreach ($method->getParameters() as $parameter) {
            $class = $parameter->getClass();

            if (null === $class) {
                $node->add(new Scalar($parameter->getName()));
                continue;
            }

            $node->add($this->getNode($class));
        }

        return $node;
    }
}
