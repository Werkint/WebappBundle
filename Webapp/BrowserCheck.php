<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

use Ikimea\Browser\Browser;
use Symfony\Component\HttpFoundation\Request;

/**
 * BrowserCheck.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class BrowserCheck
{
    const COOKIE = 'werkint_webapp_warn';

    protected $request;
    protected $config;

    /**
     * @param Request $request
     * @param array   $config
     */
    public function __construct(
        Request $request,
        array $config
    ) {
        $this->request = $request;
        $this->config = $config;
    }

    protected $browsers = [
        'msie'    => Browser::BROWSER_IE,
        'firefox' => Browser::BROWSER_FIREFOX,
        'opera'   => Browser::BROWSER_OPERA,
        'chrome'  => Browser::BROWSER_CHROME,
        'safari'  => Browser::BROWSER_SAFARI,
    ];

    /**
     * @return bool
     */
    public function isOld()
    {
        if (!$this->config['warn']) {
            return false;
        }

        if ($this->request->cookies->get($this->getCookieName())) {
            return false;
        }

        $agent = $this->getAgent();
        $browser = $this->getBrowserKey($agent);

        if (!empty($browser)) {
            $browser = $browser[0];
            if ($this->config['modern'][$browser] > $agent->getVersion()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Browser
     */
    protected function getAgent()
    {
        return new Browser(
            $this->request->headers->get('User-Agent')
        );
    }

    /**
     * @param Browser $agent
     * @return array
     */
    protected function getBrowserKey(Browser $agent)
    {
        return array_keys(
            $this->browsers,
            $agent->getBrowser()
        );
    }

    /**
     * @return string
     */
    public function getBrowserName()
    {
        $agent = $this->getAgent();
        return $agent->getBrowser() . ' ' . $agent->getVersion();
    }

    /**
     * @return string
     */
    public function getCookieName()
    {
        return static::COOKIE;
    }
} 