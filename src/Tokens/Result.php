<?php

namespace CedricCourteau\Variant\Tokens;

final class Result implements TokensInterface
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

    public function getOkType(): string
    {
        return $this->getParamType(0) ?? '@underfined';
    }
    public function getErrorTypes(): string
    {
        return $this->getParamType(1) ?? '@underfined';
    }

    public function generateContent(string $namespace = '%NAMESPACE%', ?string $extends = null, ?string $implement = null, ?string $errorTypes = null): string
    {
        $stub = 'class %CLASS_NAME%
{
    private ?%OK_TYPE% $value;
    private null|%ERROR_TYPES% $error;

    private function __construct(?%OK_TYPE% $value, null|%ERROR_INTERFACE% $error)
    {
        $this->value = $value;
        $this->error = $error;
    }

    /**
     * @param %OK_TYPE% $value
     * @return self
     */
    public static function ok(%OK_TYPE% $value): self
    {
        return new self($value, null);
    }

    /**
     * @param %ERROR_INTERFACE% $error
     * @return self
     */
    public static function error(%ERROR_INTERFACE% $error): self
    {
        return new self(null, $error);
    }

    public function isOk(): bool
    {
        return $this->error === null;
    }

    /**
     * @return %ERROR_TYPES%
     */
    public function getError(): %ERROR_TYPES%
    {
        if($this->error === null) {
            throw new \Exception("You cannot call getError() on non-error result.");
        }
        return $this->error;
    }

    /**
     * Unwrap the value or return a default if it\'s an error
     *
     * @param ?%OK_TYPE% $default
     * @return %OK_TYPE%
     */
    public function unwrap(?%OK_TYPE% $default = null): %OK_TYPE%
    {
        return $this->value !== null ? $this->value : $default;
    }
}';

        $ok_type = explode(" ", $this->args[0])[0] ?? '@underfined';

        $output = $stub;
        $output = str_replace("%NAMESPACE%", $namespace, $output);
        $output = str_replace("%CLASS_NAME%", $this->name, $output);
        $output = str_replace("%OK_TYPE%", $ok_type, $output);
        $output = str_replace("%ERROR_TYPES%", $errorTypes ?? '@underfined', $output);
        $output = str_replace("%ERROR_INTERFACE%", $implement ?? '@underfined', $output);
        return $output;
    }
}
