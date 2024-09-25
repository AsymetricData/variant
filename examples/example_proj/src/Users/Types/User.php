<?php

namespace CedricCourteau\Variant\Users\Types;

class User implements UserKind
{
    public function __construct(public readonly string $name, public readonly int $companyId)
    {
    }
}
