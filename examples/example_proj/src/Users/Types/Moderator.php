<?php

namespace CedricCourteau\Variant\Users\Types;

class Moderator implements UserKind
{
    public function __construct(public readonly string $name, public readonly int $companyId)
    {
    }
}
