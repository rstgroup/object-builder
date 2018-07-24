<?php declare(strict_types=1);

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

                $parsedFile = $parser->parse(file_get_contents($parameter->getDeclaringClass()->getFileName()));
                $namespace = $this->getNamespaceStmt($parsedFile);
                $uses = $this->getUseStmts($namespace);
                $namespaces = $this->getUsesNamespaces($uses);

                return $this->getFullClassName($type->name, $namespaces, $parameter->getDeclaringClass());
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
        $uses = [];
        foreach ($node->stmts as $node) {
            if ($node instanceof Stmt\Use_) {
                $uses[]= $node;
            }
        }

        return $uses;
    }

    /**
     * @param Stmt\Use_[] $uses
     * @return string[]
     */
    private function getUsesNamespaces(array $uses): array
    {
        $names = [];
        foreach ($uses as $use) {
            $names[] = $use->uses[0]->name->toString();
        }

        return $names;
    }

    private function getFullClassName(string $name, array $namespaces, ReflectionClass $class): string
    {
        if ($name[0] === '\\') {
            return $name;
        }

        if (0 === count($namespaces)) {
            return $class->getNamespaceName() . '\\' . $name;
        }

        return $this->getNamespaceForClass($name, $namespaces);
    }
}
