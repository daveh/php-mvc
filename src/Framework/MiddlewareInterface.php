<?php

namespace Framework;

interface MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next): Response;    
}