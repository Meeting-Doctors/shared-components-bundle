<?php

declare(strict_types=1);

namespace SharedBundle\UI\Http\Rest\Request;

interface RequestInterface
{
    public function support(Input $input): bool;

    public function doWithRequest(Input $input): void;
}
