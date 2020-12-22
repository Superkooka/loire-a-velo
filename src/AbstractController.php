<?php

namespace ColdBolt;

use ColdBolt\Http\Request;
use ColdBolt\Configuration;
use ColdBolt\Http\Response;
use ColdBolt\Logger\Logger;
use ColdBolt\Routing\Route;
use ColdBolt\Template\Flashbag;
use ColdBolt\Template\Template;
use ColdBolt\Routing\Exception\AttributNotDefinedException;

abstract class AbstractController
{
    protected Request $request;
    protected Response $response;
    protected Route $route;
    protected Configuration $configuration;
    protected Logger $logger;
    protected Flashbag $flashbag;
    protected Template $template;

    public function __construct(Request $request, Response $response, Route $route, Configuration $configuration, Logger $logger, Flashbag $flashbag, Template $template)
    {
        $this->request = $request;
        $this->response = $response;
        $this->route = $route;
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->flashbag = $flashbag;
        $this->template = $template;
    }


    public function getRouteAttr(string $attr_name): string
    {
        if (!isset($this->route->getDynPart()[$attr_name])) {
            throw new AttributNotDefinedException;
        }

        return $this->route->getDynPart()[$attr_name];
    }

    public function render(string $template_name, ?array $params = null)
    {
        $template_content = $this->template->setTemplate($template_name)->render($params);
        $this->response->write($template_content);
        $this->response->send();
        $this->logger->debug($this->request->getIP() . ': ' .  $template_name . ' was render, the response was send');
    }
}
