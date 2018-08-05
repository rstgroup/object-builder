<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\PhpDocParser;

use PhpParser\Node\Stmt;
use PhpParser\Parser;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser as PhpStanPhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use ReflectionClass;
use ReflectionParameter;
use RstGroup\ObjectBuilder\BuildingError;
use RstGroup\ObjectBuilder\PhpDocParser;

final class PhpStan implements PhpDocParser
{
    /** @var PhpStanPhpDocParser */
    private $phpDocParser;
    /** @var Parser */
    private $phpParser;

    public function __construct(
        PhpStanPhpDocParser $phpDocParser,
        Parser $phpParser
    ) {
        $this->phpDocParser = $phpDocParser;
        $this->phpParser = $phpParser;
    }

    public function isListOfObject(string $comment, string $parameterName): bool
    {
        $node = $this->phpDocParser->parse(new TokenIterator((new Lexer())->tokenize($comment)));

        foreach ($node->getParamTagValues() as $node) {
            if ('$' . $parameterName === $node->parameterName) {
                $typeName = $node->type->type->name;
                if ($this->isScalar($typeName)) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    public function getListType(string $comment, ReflectionParameter $parameter): string
    {
        $node = $this->phpDocParser->parse(new TokenIterator((new Lexer())->tokenize($comment)));

        foreach ($node->getParamTagValues() as $node) {
            if ($node->parameterName === '$' . $parameter->getName()) {
                $type = $node->type->type;

                /** @var ReflectionClass $class */
                $class = $parameter->getDeclaringClass();
                /** @var string $fileName */
                $fileName = $class->getFileName();
                /** @var string $phpFileContent */
                $phpFileContent = file_get_contents($fileName);
                /** @var Stmt[] $parsedFile */
                $parsedFile = $this->phpParser->parse($phpFileContent);

                $namespace = $this->getNamespaceStmt($parsedFile);
                $uses = $this->getUseStmts($namespace);
                $namespaces = $this->getUsesNamespaces($uses);

                return $this->getFullClassName($type->name, $namespaces, $class);
            }
        }

        throw new BuildingError();
    }

    private function isScalar(string $value): bool
    {
        $scalars = [
            'string',
            'bool',
            'int',
            'float',
            'double',
            'mixed',
        ];

        return in_array($value, $scalars, true);
    }

    /**
     * @param Stmt[] $nodes
     */
    private function getNamespaceStmt(array $nodes): Stmt\Namespace_
    {
        foreach ($nodes as $node) {
            if ($node instanceof Stmt\Namespace_) {
                return $node;
            }
        }

        return new Stmt\Namespace_();
    }

    /** @return Stmt\Use_[] */
    private function getUseStmts(Stmt\Namespace_ $node): array
    {
        return array_filter($node->stmts, function (Stmt $node): bool {
            return $node instanceof Stmt\Use_;
        });
    }

    /**
     * @param Stmt\Use_[] $uses
     * @return string[]
     */
    private function getUsesNamespaces(array $uses): array
    {
        return array_map(function (Stmt\Use_ $use): string {
            return $use->uses[0]->name->toString();
        }, $uses);
    }

    /** @param string[] $namespaces */
    private function getFullClassName(string $name, array $namespaces, ReflectionClass $class): string
    {
        if ('\\' === $name[0] || explode('\\', $name)[0] !== $name) {
            return $name;
        }

        if (0 === count($namespaces)) {
            return '\\' . $class->getNamespaceName() . '\\' . $name;
        }

        return '\\' . $this->getNamespaceForClass($name, $namespaces);
    }

    /**
     * @param string[] $namespaces
     * @throws BuildingError
     */
    private function getNamespaceForClass(string $className, array $namespaces): string
    {
        foreach ($namespaces as $namespace) {
            if ($this->endsWith($namespace, $className)) {
                return $namespace;
            }
        }

        throw new BuildingError('Can not resolve namespace for class ' . $className);
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        return 0 === $length || (substr($haystack, -$length) === $needle);
    }
}
