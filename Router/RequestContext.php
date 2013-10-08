<?php
namespace Werkint\Bundle\WebappBundle\Router;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext as BaseRequestContext;

class RequestContext extends BaseRequestContext
{
    /** @var ParameterBag */
    protected $headers;

    public function fromRequest(Request $request)
    {
        parent::fromRequest($request);
        $this->headers = $request->headers;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}