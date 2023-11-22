<?php

namespace Framework;

interface TemplateViewerInterface
{
    public function render(string $template, array $data = []): string;    
}