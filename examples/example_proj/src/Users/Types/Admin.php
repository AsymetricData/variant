<?php

namespace CedricCourteau\Variant\Users\Types;

class Admin implements UserKind
{
    public function __construct(public readonly string $name)
    {
    }
}
