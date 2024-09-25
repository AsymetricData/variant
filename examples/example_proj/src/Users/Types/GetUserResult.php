<?php

namespace CedricCourteau\Variant\Users\Types;


class GetUserResult
{
    private ?UserKind $value;
    private null|NotFound|InvalidCredentials|Unauthorized|BusyDB $error;

    private function __construct(?string $value, null|GetUserError $error)
    {
        $this->value = $value;
        $this->error = $error;
    }

    /**
     * @param UserKind $value
     * @return self
     */
    public static function ok(UserKind $value): self
    {
        return new self($value, null);
    }

    /**
     * @param GetUserError $error
     * @return self
     */
    public static function error(GetUserError $error): self
    {
        return new self(null, $error);
    }

    public function isOk(): bool
    {
        return $this->error === null;
    }

    /**
     * @return NotFound|InvalidCredentials|Unauthorized|BusyDB
     */
    public function getError(): NotFound|InvalidCredentials|Unauthorized|BusyDB
    {
        if($this->error === null) {
            throw new \Exception("You cannot call getError() on non-error result.");
        }
        return $this->error;
    }

    /**
     * Unwrap the value or return a default if it's an error
     *
     * @param ?UserKind $default
     * @return UserKind
     */
    public function unwrap(?UserKind $default = null): UserKind
    {
        return $this->value !== null ? $this->value : $default;
    }
}