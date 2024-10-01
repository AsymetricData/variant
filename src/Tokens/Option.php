<?php

namespace CedricCourteau\Variant\Tokens;

final class Option implements TokensInterface
{
    /**
     * @param array<int,string> $args
     */
    public function __construct(
        public readonly string $name,
        public readonly array $args,
    ) {
    }
    public function getType(): Tokens
    {
        return Tokens::RESULT;
    }

    public function getParamCount(): int
    {
        return count($this->args);
    }
    public function getParamType(int $n): ?string
    {
        if ($n > $this->getParamCount()) {
            return null;
        }
        return explode(" ", $this->args[$n])[0] ?? null;
    }

    /**
    * Result doesn't have named params, only types.
    */
    public function getParamName(int $n): ?string
    {
        return null;
    }

    public function getSomeType(): string
    {
        return $this->getParamType(0) ?? '@underfined';
    }

    public function generateContent(string $namespace = '%NAMESPACE%', ?string $extends = null, ?string $implement = null, ?string $errorTypes = null): string
    {
        $stub = 'class %CLASS_NAME%
{
    private ?%SOME_TYPE% $value;

    private function __construct(?%SOME_TYPE% $value)
    {
        $this->value = $value;
    }

    /**
     * @param %SOME_TYPE% $value
     * @return self
     */
    public static function some(%SOME_TYPE% $value): self
    {
        return new self($value);
    }

    public function isSome(): bool
    {
        return $this->value !== null;
    }

    public function isNone(): bool
    {
        return $this->value === null;
    }

    /**
     * Unwrap the value or return a default if it\'s a None
     *
     * @param ?%SOME_TYPE% $default
     * @return %SOME_TYPE%
     */
    public function unwrap(?%SOMe_TYPE% $default = null): %SOME_TYPE%
    {
        return $this->value !== null ? $this->value : $default;
    }
}';

        $some_type = explode(" ", $this->args[0])[0] ?? '@underfined';

        $output = $stub;
        $output = str_replace("%NAMESPACE%", $namespace, $output);
        $output = str_replace("%CLASS_NAME%", $this->name, $output);
        $output = str_replace("%SOME_TYPE%", $some_type, $output);
        return $output;
    }
}
