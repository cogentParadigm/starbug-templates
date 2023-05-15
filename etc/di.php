<?php
namespace Starbug\Templates;

use function DI\autowire;
use function DI\factory;
use Starbug\Templates\Twig\Factory;
use Twig\Environment;

return [
  "template.helpers" => [],
  TemplateInterface::class => autowire(TemplateRenderer::class),
  Environment::class => factory([Factory::class, "createEnvironment"])
];
