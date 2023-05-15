<?php
namespace Starbug\Templates\Twig;

use Psr\Container\ContainerInterface;
use Starbug\Modules\Configuration;
use Starbug\Templates\Twig\Extension;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extension\StringLoaderExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class Factory {
  protected $container;
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }
  public function createEnvironment(?LoaderInterface $loader = null): Environment {
    if (!is_object($loader)) {
      $loader = $this->createLoader();
    }
    $twig = new Environment($loader, ['debug' => true, 'html_errors' => true, 'autoescape' => "name"]);
    $twig->addExtension(new StringLoaderExtension());
    $twig->addExtension(new DebugExtension());
    $twig->addExtension(new Extension($this->container));
  }
  public function createLoader(): LoaderInterface {
    $modules = array_reverse($this->container->get(Configuration::class)->getEnabled(), true);
    $loader = new FilesystemLoader();
    foreach ($modules as $name => $module) {
      $dir = $module["path"];
      if (file_exists($dir."/templates")) {
        $loader->addPath($dir."/templates");
        $loader->addPath($dir."/templates", $name);
      }
      if (file_exists($dir."/layouts")) {
        $loader->addPath($dir."/layouts", "layouts");
      }
      if (file_exists($dir."/views")) {
        $loader->addPath($dir."/views", "views");
      }
    }
    return $loader;
  }
}
