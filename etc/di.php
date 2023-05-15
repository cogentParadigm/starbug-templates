<?php
namespace Starbug\Templates;

use function DI\add;
use function DI\autowire;
use Starbug\Templates\Twig\Factory;
use Twig\Environment;

return [
  "template.helpers" => add([]),
  TemplateInterface::class => autowire(TemplateRenderer::class),
  Environment::class => function (Factory $factory) {
    return $factory->createEnvironment();
  }
];
