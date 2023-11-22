<?php

namespace Framework;

interface RequestHandlerInterface
{
    public function handle(Request $request): Response;
}