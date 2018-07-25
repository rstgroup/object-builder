<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator;

use PhpParser\Node\Stmt;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser as PhpStanPhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionParameter;
use Roave\BetterReflection\BetterReflection;
use RstGroup\ObjectBuilder\BuildingError;

class PhpDocParser
{
    public function isListOfObject(string $comment, ReflectionParameter $parameter): bool
    {
        $parser = new PhpStanPhpDocParser(new TypeParser(), new ConstExprParser());
        $node = $parser->parse(new TokenIterator((new Lexer())->tokenize($comment)));

        foreach ($node->getParamTagValues() as $node) {
            if ($node->parameterName === '$' . $parameter->getName()) {
                return true;
            }
        }

        return false;
    }

    public function getListType(string $comment, ReflectionParameter $parameter): string
    {
        $parser = new PhpStanPhpDocParser(new TypeParser(), new ConstExprParser());
        $node = $parser->parse(new TokenIterator((new Lexer())->tokenize($comment)));

        foreach ($node->getParamTagValues() as $node) {
            if ($node->parameterName === '$' . $parameter->getName()) {
                $type = $node->type->type;
                $parser = (new BetterReflection())->phpParser();

                /** @var ReflectionClass $class */
                $class = $parameter->getDeclaringClass();
                /** @var string $fileName */
                $fileName = $class->getFileName();
                /** @var string $phpFileContent */
                $phpFileContent = file_get_contents($fileName);
                /** @var Stmt[] $parsedFile */
                $parsedFile = $parser->parse($phpFileContent);

                $namespace = $this->getNamespaceStmt($parsedFile);
                $uses = $this->getUseStmts($namespace);
                $namespaces = $this->getUsesNamespaces($uses);

                return $this->getFullClassName($type->name, $namespaces, $class);
            }
        }
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
        if ('\\' === $name[0]) {
            return $name;
        }

        if (0 === count($namespaces)) {
            return $class->getNamespaceName() . '\\' . $name;
        }

        return $this->getNamespaceForClass($name, $namespaces);
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
