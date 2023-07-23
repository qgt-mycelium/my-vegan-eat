<?php

$finder = (new PhpCsFixer\Finder())
    ->in(dirname(__DIR__, 3))
    ->exclude('var')
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
    ])
    ->setFinder($finder)
    ->setUsingCache(false)
;
