<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator;

use ReflectionClass;
use ReflectionMethod;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Complex;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\ObjectList;
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
'function(array $data) use ($class): object {
%s
    return %s;
}';

    /** @var PhpDocParser */
    private $phpDocParser;

    public function __construct()
    {
        $this->phpDocParser = new PhpDocParser();
    }

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
                    $this->createNode(
                        $parameter,
                        $method->getDocComment() ? $method->getDocComment() : ''
                    )
                );
                continue;
            }

            $node->add($this->getNode($class, $parameter->getName()));
        }

        return $node;
    }

    private function createNode(\ReflectionParameter $parameter, string $phpDoc): Node
    {
        $type = $parameter->getType()->getName();

        if ($type === 'array'
            && $this->phpDocParser->isListOfObject($phpDoc, $parameter)) {
            $class = $this->phpDocParser->getListType($phpDoc, $parameter);

            return new ObjectList(
                $parameter->getName(),
                $this->getNode(new ReflectionClass($class))
            );
        }

        return new Scalar(
            $type,
            $parameter->getName(),
            $parameter->isDefaultValueAvailable()
                ? $parameter->getDefaultValue()
                : null
        );
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

    /** @return mixed */
    private function getDefaultValues(Node $node)
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
            $values = $node->defaultValue();
        }

        return $values;
    }
}
