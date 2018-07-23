<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator;


use ReflectionClass;
use ReflectionMethod;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Complex;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Scalar;

final class Anonymous
{
    private const FILE_BEGIN_WITH =
"<?php\n";
    private const DEFAULT_VALUES_PATTERN =
'    $default = %s;'
. "\n"
. '    $data = array_merge($default, $data);'
. "\n";
    private const FUNCTION_PATTERN =
'function(array $data) use ($class): string {
%s
    return %s;
}';

    public function create(string $class): string
    {
        $reflection = new ReflectionClass($class);

        $node = $this->getNode($reflection);

        return self::FILE_BEGIN_WITH
            . "\n"
            . 'return '
            . sprintf(
                self::FUNCTION_PATTERN,
                $this->getDefaultSection($node),
                $node
            )
        ;
    }

    private function getNode(ReflectionClass $class, string $name = ''): Node
    {
        $constructor = $class->getConstructor();

        if (null === $constructor) {
            return new Complex($class->getName(), $name);
        }

        return $this->getNodes($constructor, $name);
    }

    private function getNodes(ReflectionMethod $method, string $name = ''): Node
    {
        $node = new Complex($method->getDeclaringClass()->getName(), $name);

        foreach ($method->getParameters() as $parameter) {
            $class = $parameter->getClass();

            if (null === $class) {
                $node->add(
                    new Scalar(
                        $parameter->getName(),
                        $parameter->isDefaultValueAvailable()
                            ? $parameter->getDefaultValue()
                            : null
                    )
                );
                continue;
            }

            $node->add($this->getNode($class, $parameter->getName()));
        }

        return $node;
    }

    private function getDefaultSection(Node $node): string
    {
        $defaultSection = '';
        $defaultValues = $this->getDefaultValues($node);
        if (! empty($defaultValues)) {
            $defaultSection = sprintf(
                self::DEFAULT_VALUES_PATTERN,
                var_export($defaultValues, true)
            );
        }

        return $defaultSection;
    }

    private function getDefaultValues(Node $node): array
    {
        $values = [];

        if ($node instanceof Complex) {
            foreach ($node->innerNodes() as $innerNode) {
                $innerNodeDefaultValues = $this->getDefaultValues($innerNode);
                if(! empty($innerNodeDefaultValues)) {
                    $values[$innerNode->name()] = $innerNodeDefaultValues;
                }
            }
        }

        if ($node->withDefaultValue()) {
            $values[$node->name()] = $node->defaultValue();
        }

        return $values;
    }
}
