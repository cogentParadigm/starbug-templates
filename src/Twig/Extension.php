<?php
namespace Starbug\Templates\Twig;

use Psr\Container\ContainerInterface;
use Starbug\Modules\Configuration;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Extension extends AbstractExtension {
  protected $container;
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }
  public function getFunctions() {
    return [
      new TwigFunction('helper', function ($name) {
        $helpers = $this->container->get("template.helpers");
        return $this->container->get($helpers[$name])->helper();
      }),
      new TwigFunction('publish', function (
        Environment $env,
        $context,
        $template,
        $variables = [],
        $withContext = true,
        $ignoreMissing = true,
        $sandboxed = false
      ) {
        $results = [];
        $modules = array_reverse($this->container->get(Configuration::class)->getEnabled(), true);
        foreach ($modules as $namespace => $module) {
          if ($namespace !== FilesystemLoader::MAIN_NAMESPACE) {
            $result = twig_include(
              $env,
              $context,
              "@".$namespace."/".$template,
              $variables,
              $withContext,
              $ignoreMissing,
              $sandboxed
            );
            if ($result) {
              array_unshift($results, $result);
            }
          }
        }
        return implode("\n", $results);
      }, ['needs_environment' => true, 'needs_context' => true, 'is_safe' => ['all']]),
      new TwigFunction("csv", function ($data) {
        $out = fopen("php://output", "w");
        foreach ($data as $row) {
          fputcsv($out, $row);
        }
        fclose($out);
      })
    ];
  }
}
