<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternGenerator;

use ReflectionClass;
use ReflectionMethod;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternGenerator;
use RstGroup\ObjectBuilder\PhpDocParser;

final class Anonymous implements PatternGenerator
{
    private const DEFAULT_VALUES_PATTERN =
    '    $default = %s;'
    . "\n"
    . '    $data = array_merge($default, $data);'
    . "\n";

    private const FUNCTION_PATTERN =
    'return function(array $data) use ($class): object {
%s
    return %s;
};';

    /** @var PhpDocParser */
    private $phpDocParser;
    /** @var Node\Serializer */
    private $serializer;

    /** @codeCoverageIgnore */
    public function __construct(
        PhpDocParser $parser,
        Node\Serializer $serializer
    ) {
        $this->phpDocParser = $parser;
        $this->serializer = $serializer;
    }

    public function create(string $class): string
    {
        $reflection = new ReflectionClass($class);

        $node = $this->getNode($reflection);

        return sprintf(
            self::FUNCTION_PATTERN,
            $this->getDefaultSection($node),
            $this->serializer->serialize($node)
        );
    }

    private function getNode(ReflectionClass $class, string $name = ''): Node
    {
        $constructor = $class->getConstructor();

        if (null === $constructor) {
            return new Node\Complex($class->getName(), $name);
        }

        return $this->getNodes($constructor, $name);
    }

    private function getNodes(ReflectionMethod $method, string $name = ''): Node
    {
        $node = new Node\Complex($method->getDeclaringClass()->getName(), $name);

        foreach ($method->getParameters() as $parameter) {
            $class = $parameter->getClass();

            if (null === $class) {
                $comment = (bool) $method->getDocComment()
                    ? (string) $method->getDocComment()
                    : '';
                $node->add($this->createNode($parameter, $comment));

                continue;
            }

            $node->add($this->getNode($class, $parameter->getName()));
        }

        return $node;
    }

    private function createNode(\ReflectionParameter $parameter, string $phpDoc): Node
    {
        $type = $parameter->getType()->getName();

        if ('array' === $type
            && $this->phpDocParser->isListOfObject($phpDoc, $parameter->getName())) {

            $class = $this->phpDocParser->getListType($phpDoc, $parameter);

            return new Node\ObjectList(
                $parameter->getName(),
                $this->getNode(new ReflectionClass($class))
            );
        }

        return new Node\Scalar(
            $type,
            $parameter->getName(),
            $parameter->allowsNull(),
            $parameter->isDefaultValueAvailable()
                ? $parameter->getDefaultValue()
                : null
        );
    }

    private function getDefaultSection(Node $node): string
    {
        $defaultSection = '';
        $defaultValues = $this->getDefaultValues($node);
        if ([] !== $defaultValues) {
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

        if ($node instanceof Node\Complex) {
            foreach ($node->innerNodes() as $innerNode) {
                $innerNodeDefaultValues = $this->getDefaultValues($innerNode);
                if ([] === $innerNodeDefaultValues) {
                    continue;
                }

                $values[$innerNode->name()] = $innerNodeDefaultValues;
            }
        }

        if ($node->withDefaultValue()) {
            return $node->defaultValue();
        }

        if ($node->nullable()) {
            return null;
        }

        return $values;
    }
}
