<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/test')
    ->exclude('vendor');

$config = new PhpCsFixer\Config();

return $config->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
