<?php

namespace App\EventListener;

use App\Exception\AppointmentException;
use App\Http\ViolationFormatter;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final readonly class ApiExceptionListener
{
    public function __construct(private ViolationFormatter $formatter) {}

    #[AsEventListener]
    public function __invoke(ExceptionEvent $event): void
    {
        if ($event->getRequest()->getPreferredFormat() !== 'json') {
            return;
        }

        $e = $event->getThrowable();

        $validation = $e instanceof ValidationFailedException ? $e : $e->getPrevious();

        if ($validation instanceof ValidationFailedException) {
            $event->setResponse(new JsonResponse(
                ['violations' => $this->formatter->format($validation->getViolations())],
                422,
            ));

            return;
        }

        if ($e instanceof AppointmentException) {
            $event->setResponse(new JsonResponse([
                'violations' => [[
                    'propertyPath' => $e->getPropertyPath(),
                    'title' => $e->getMessage(),
                ]],
            ], 422));
        }
    }
}
