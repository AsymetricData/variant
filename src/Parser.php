<?php

namespace CedricCourteau\Variant;

use CedricCourteau\Variant\Tokens\Option;
use CedricCourteau\Variant\Tokens\Record;
use CedricCourteau\Variant\Tokens\Result;
use CedricCourteau\Variant\Tokens\TokensInterface;
use CedricCourteau\Variant\Tokens\Type;
use TokenInterface;

final class Parser
{
    private int $cursor;

    private string $buffer;

    /**
    * @var array<int, TokensInterface>
    */
    private array $tokens;

    public function __construct(protected string $toParse)
    {
        $this->cursor = 0;
        $this->tokens = [];
        $this->buffer = "";
    }
    /**
     * @return Parser
     */
    public function parse(): Parser
    {
        $len = mb_strlen($this->toParse);

        while($this->cursor < $len) {
            $token = $this->getNextToken();
            if ($token !== null) {
                $this->tokens[] = $token;
            }
        }

        return $this;
    }

    /**
    * @return TokenInterface[]
    */
    public function getTokens(): array
    {
        return $this->tokens ?? $this->parse()->tokens;
    }
    private function getCurrentChar(): string
    {
        return $this->toParse[$this->cursor];
    }
    private function advance(int $n = 1): string
    {
        return $this->toParse[$this->cursor++];
    }

    protected function getNextToken(): ?TokensInterface
    {
        while(mb_strlen($this->toParse) > $this->cursor) {
            $char = $this->advance();
            $rest = substr($this->toParse, $this->cursor);
            if ($char === ' ' || $char === "\n") {
                $token = match(trim($this->buffer)) {
                    'type' => $this->parseType($rest),
                    'result' => $this->captureResult($rest),
                    'option' => $this->captureOption($rest),
                    'record' => $this->captureRecords($rest, true)[0],
                    '#' => $this->skipLine($rest),
                    default => null,
                };
                if ($token instanceof TokensInterface) {
                    $this->buffer = "";
                    return $token;
                }

                if ($token === null) {
                    $this->buffer = "";
                }
            }

            $this->buffer .= $char;
        }

        return null;
    }

    /**
    * @return Type
    */
    public function parseType(string $rest): Type
    {
        $end = strpos($rest, '}');
        if ($end === false) {
            throw new \Exception("`type` instruction must end with `}` in `" . $rest ."`.");
        }

        $part = mb_substr($rest, 0, $end + 1);
        $pattern = '/(\w+)\s*\{\s*((?:\w+\s*(?:\(\s*(?:[^\(\)]+)\s*\))?\s*)+)\}/';
        preg_match($pattern, $part, $matches);
        if (!array_key_exists(2, $matches)) {
            throw new \Exception("Invalid type defintion. You must provide records.\n\n{$rest}");
        }
        $records = $this->captureRecords($matches[2]);

        $this->cursor += $end;

        return new Type($matches[1], $records);
    }

    /**
    * @return Record[]
    */
    private function captureRecords(string $rest, bool $updateCursor = false): array
    {
        $end = strpos($rest, ')');

        preg_match_all('/(\w+)\s*(?:\(([^)]+)\))?/', $rest, $recordMatches);
        $records = [];
        for ($j = 0; $j < count($recordMatches[1]); $j++) {
            $recordName = $recordMatches[1][$j];
            $recordParams = isset($recordMatches[2][$j]) ? $recordMatches[2][$j] : null;
            $records[] = new Record(
                $recordName,
                $recordParams ? array_map('trim', explode(',', $recordParams)) : []
            );
        }

        if ($updateCursor === true) {
            $this->cursor += $end;
        }
        return $records;
    }

    private function captureResult(string $rest): Result
    {
        $record = $this->captureRecords($rest, true)[0];
        return new Result($record->name, $record->args);
    }

    private function captureOption(string $rest): Option
    {
        $option = $this->captureRecords($rest, true)[0];
        return new Option($option->name, $option->args);
    }

    private function skipLine(string $rest): void
    {
        $end = mb_strpos($rest, "\n");
        $this->cursor += $end;
    }
}
