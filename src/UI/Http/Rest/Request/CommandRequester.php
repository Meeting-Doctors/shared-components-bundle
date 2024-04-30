<?php

declare(strict_types=1);

namespace SharedBundle\UI\Http\Rest\Request;

final readonly class CommandRequester
{
    /** @var RequestInterface[] */
    private array $requests;

    public function __construct(RequestInterface ...$requests)
    {
        $this->requests = $requests;
    }

    public function request(Input $input): void
    {
        foreach ($this->requests as $request) {
            if ($request->support($input)) {
                $request->doWithRequest($input);
            }
        }
    }
}
