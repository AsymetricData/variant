<?php

namespace CedricCourteau\Variant\Tokens;

class Record implements TokensInterface
{
    /**
     * @param array<int,mixed> $args
     */
    public function __construct(public readonly string $name, public readonly array $args)
    {
    }

    public function getType(): Tokens
    {
        return Tokens::RECORD;
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
        return explode(" ", $this->args[$n] ?? "")[0] ?? null;
    }

    public function getParamName(int $n): ?string
    {
        if ($n > $this->getParamCount()) {
            return null;
        }
        return explode(" ", $this->args[$n] ?? "")[1] ?? null;
    }

    public function generateContent(string $namespace = '%NAMESPACE%', ?string $extends = null, ?string $implement = null, ?string $errorTypes = null): string
    {
        $className = $this->name;
        $constructorParams = "";

        $extends = match($extends !== null) {
            true => ' extends ' . $extends,
            false => '',
        };
        $implement = match($implement !== null) {
            true => ' implements ' . $implement,
            false => '',
        };

        $constructorParams = ''; // or some default handling
        if ($this->getParamCount() > 0) {
            foreach (range(0, $this->getParamCount() - 1) as $n) {
                $type = $this->getParamType($n);
                $propertyName = $this->getParamName($n);

                if (!empty($constructorParams) && !empty($type) && !empty($propertyName)) {
                    $constructorParams .= ", ";
                }
                $constructorParams .= "public readonly {$type} \${$propertyName}";
            }
        }

        // Generate the whole class content
        $classContent = "class {$className}{$extends}{$implement}\n";
        $classContent .= "{\n";
        $classContent .= "    public function __construct({$constructorParams})\n";
        $classContent .= "    {\n";
        $classContent .= "    }\n";
        $classContent .= "}\n";

        return $classContent;
    }
}
