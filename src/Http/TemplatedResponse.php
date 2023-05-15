<?php
namespace Starbug\Templates\Http;

use GuzzleHttp\Psr7\Response;

class TemplatedResponse extends Response {
  protected $format;
  protected $pageTemplate;
  protected $template;
  protected $parameters;
  protected $options;
  protected $defaultOptions = ["scope" => "views"];
  public function __construct($template, $parameters = [], $options = [], $format = null) {
    parent::__construct();
    $this->format = $format;
    $this->pageTemplate = $format ? $format.".".$format : null;
    $this->template = $template;
    $this->parameters = $parameters;
    $this->options = $options + $this->defaultOptions;
  }
  public function getTemplate() {
    return $this->template;
  }
  public function withTemplate($path = "", $params = [], $options = []) {
    $new = clone $this;
    $options = $options + $this->defaultOptions;
    $new->template = $path;
    $new->parameters = $params;
    $new->options = $options;
    return $new;
  }
  public function getFormat() {
    return $this->format;
  }
  public function withFormat($format) {
    $new = clone $this;
    $new->format = $format;
    $new->pageTemplate = $format.".".$format;
    return $this;
  }
  public function getPageTemplate() {
    return $this->pageTemplate;
  }
  public function withPageTemplate($template) {
    $new = clone $this;
    $new->pageTemplate = $template;
    return $new;
  }
  public function getParameters() {
    return $this->parameters;
  }
  public function withParameter($key, $value) {
    $new = clone $this;
    $new->parameters[$key] = $value;
    return $new;
  }
  public function withParameters($parameters) {
    $new = clone $this;
    $new->parameters = $parameters;
    return $new;
  }
  public function getOptions() {
    return $this->options;
  }
  public function withOption($key, $value) {
    $new = clone $this;
    $new->options[$key] = $value;
    return $new;
  }
  public function withOptions($options) {
    $new = clone $this;
    $new->options = $options;
    return $new;
  }
  public function assign($key, $value = null) {
    $merge = is_array($key) ? $key : [$key => $value];
    return $this->withParameters($merge + $this->parameters);
  }
  public function render($path = "", $params = [], $options = []) {
    return $this->withTemplate($path, $params, $options);
  }
}
