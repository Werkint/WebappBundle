<?php
namespace Werkint\Bundle\WebappBundle\Router;

use Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher as BaseMatcher;
use Symfony\Component\Routing\Route;

class UrlMatcher extends BaseMatcher
{
    /** @var RequestContext */
    protected $context;

    protected function retStatus($ret)
    {
        return [
            !$ret ? self::REQUIREMENT_MISMATCH : self::REQUIREMENT_MATCH,
            null,
        ];
    }

    protected function checkHeader($name, $value)
    {
        return $this->context->getHeaders()->get($name) == $value;
    }

    protected function handleRouteRequirements($pathinfo, $name, Route $route)
    {
        $reqs = $route->getRequirements();
        if (isset($reqs['_x_requested']) && $this->checkHeader('X-Requested-With', $reqs['_x_requested'])) {
            return $this->retStatus(false);
        }

        return parent::handleRouteRequirements($pathinfo, $name, $route);
    }

}