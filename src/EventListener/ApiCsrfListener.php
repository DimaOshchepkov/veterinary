<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[AsEventListener(event: KernelEvents::REQUEST)]
final class ApiCsrfListener
{
    public function __construct(private CsrfTokenManagerInterface $csrf) {}

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()
            || $request->isMethodSafe()
            || !str_starts_with($request->getPathInfo(), '/api/')
        ) {
            return;
        }

        $value = $request->headers->get('csrf-token', 'csrf-token');

        if (!$this->csrf->isTokenValid(new CsrfToken('submit', $value))) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }
    }
}
