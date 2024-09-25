<?php

namespace CedricCourteau\Variant\Tokens;

use CedricCourteau\Variant\Tokens\Tokens;
use CedricCourteau\Variant\Tokens\TokensInterface;

class Type implements TokensInterface
{
    /**
     * @param Record[] $children
     * @param array<int,Record> $children
     */
    public function __construct(public readonly string $name, public readonly array $children)
    {
    }

    public function getType(): Tokens
    {
        return Tokens::TYPE;
    }

    public function getParamType(int $n): ?string
    {
        return null;
    }

    public function getParamName(int $n): ?string
    {
        return null;
    }

    public function getParamCount(): int
    {
        return 0;
    }

    public function getAllTypes(): array
    {
        return array_map(fn (Record $record) => $record->name, $this->children);
    }

    public function generateContent(string $namespace = '%NAMESPACE%', ?string $extends = null, ?string $implement = null, ?string $errorTypes = null): string
    {
        $return = "interface {$this->name} {}\n";

        return $return;
    }
}
