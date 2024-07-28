<?php

declare(strict_types=1);

namespace SharedBundle\UI\Http\Rest\EventListener;

use SharedBundle\UI\Http\Rest\Exception\ExceptionMessageTrait;
use SharedBundle\UI\Http\Rest\Exception\ExceptionToHttpStatusCodeMapping;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final readonly class ExceptionEventListener
{
    use ExceptionMessageTrait;

    public function __construct(
        private ExceptionToHttpStatusCodeMapping $exceptionHttpStatusCodeMapping
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isJsonAcceptable($request)) {
            return;
        }

        $exception = $event->getThrowable();

        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/vnd.api+json');

        $statusCode = $this->exceptionHttpStatusCodeMapping->handle($exception);

        $response->setStatusCode($statusCode);
        $response->setData($this->errorMessage($exception));

        $event->setResponse($response);
    }

    private function isJsonAcceptable(Request $request): bool
    {
        return \in_array('application/json', $request->getAcceptableContentTypes())
            || \in_array('application/vnd.api+json', $request->getAcceptableContentTypes());
    }

    private function errorMessage(\Throwable $exception): array
    {
        $error = $this->error($exception);

        return [...$error, ...$this->metadata($exception)];
    }
}
