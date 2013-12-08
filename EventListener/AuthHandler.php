<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * AuthHandler.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class AuthHandler implements
    AuthenticationSuccessHandlerInterface,
    AuthenticationFailureHandlerInterface
{
    protected $crsfProvider;
    protected $translator;

    /**
     * @param CsrfProviderInterface $crsfProvider
     * @param Translator            $translator
     */
    public function __construct(
        CsrfProviderInterface $crsfProvider,
        Translator $translator
    ) {
        $this->crsfProvider = $crsfProvider;
        $this->translator = $translator;
    }

    /**
     * @param BaseRequest    $request
     * @param TokenInterface $token
     * @return RedirectResponse|Response
     */
    public function onAuthenticationSuccess(BaseRequest $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            return new Response(json_encode([
                'isError' => false,
                'message' => '',
            ]));
        }
        return new RedirectResponse('/');
    }

    /**
     * @param BaseRequest             $request
     * @param AuthenticationException $exception
     * @return RedirectResponse|Response
     */
    public function onAuthenticationFailure(
        BaseRequest $request,
        AuthenticationException $exception
    ) {
        if ($request->isXmlHttpRequest()) {
            return new Response(json_encode([
                'crsf'    => $this->crsfProvider->generateCsrfToken('authenticate'),
                'isError' => true,
                'message' => $this->translator->trans(
                        $exception->getMessage(), [],
                        'FOSUserBundle'
                    ),
            ]));
        }
        return new RedirectResponse('/');
    }
}
