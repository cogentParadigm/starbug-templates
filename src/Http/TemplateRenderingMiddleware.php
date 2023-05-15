<?php
namespace Starbug\Templates\Http;

use GuzzleHttp\Psr7\MimeType;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Starbug\Templates\TemplateInterface;

class TemplateRenderingMiddleware implements MiddlewareInterface {
  public function __construct(TemplateInterface $templates) {
    $this->templates = $templates;
  }
  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    $response = $this->renderHeaders($request, $handler->handle($request));
    if ($response instanceof TemplatedResponse) {
      $route = $request->getAttribute("route");
      $this->templates->assign("request", $request);
      $this->templates->assign("response", $response);
      $this->templates->assign("route", $route);
      if (!$route->hasOption("format")) $route->setOption("format", "html");
      $format = $route->getOption("format");
      if (!$response->hasHeader("Content-Type")) {
        $response = $response->withHeader("Content-Type", MimeType::fromExtension($format));
      }
      $response = $this->renderResponse($response, $route->getOption("template") ?? $format.".".$format);
    }
    return $response;
  }
  protected function renderResponse(TemplatedResponse $response, $pageTemplate): ResponseInterface {
    $pageTemplate = $response->getPageTemplate() ?? $pageTemplate;
    $contentTemplate = $response->getTemplate();
    $params = $response->getParameters();
    $options = $response->getOptions();
    $content = $this->templates->capture($contentTemplate, $params, $options);
    if ($pageTemplate) {
      $bodyStream = Utils::streamFor($this->templates->capture($pageTemplate, ["content" => $content]));
    } else {
      $bodyStream = Utils::streamFor($content);
    }
    return $response->withBody($bodyStream);
  }
  protected function renderHeaders(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    $arguments = $request->getAttribute("route")->getOptions();
    foreach (["Location"] as $headerName) {
      if ($response->hasHeader($headerName)) {
        $headerValue = $this->templates->replace($response->getHeaderLine($headerName), $arguments);
        $response = $response->withHeader($headerName, $headerValue);
      }
    }
    return $response;
  }
}
