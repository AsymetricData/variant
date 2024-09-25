<?php

namespace CedricCourteau\Variant\Tokens;

use CedricCourteau\Variant\Tokens\Tokens;

interface TokensInterface
{
    public function getType(): Tokens;

    public function getParamType(int $n): ?string;
    public function getParamName(int $n): ?string;
    public function getParamCount(): int;
    
    public function generateContent(string $namespace = '%NAMESPACE%', ?string $extends = null, ?string $implement = null, ?string $errorTypes = null): string;
}
