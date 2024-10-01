<?php

use CedricCourteau\Variant\Parser;
use CedricCourteau\Variant\Tokens\Option;
use CedricCourteau\Variant\Tokens\Record;
use CedricCourteau\Variant\Tokens\Type;
use CedricCourteau\Variant\Tokens\Result;

it('parses a simple type correctly', function () {
    $toParse = "type MyType {
        Case
    }";
    $parser = new Parser($toParse);
    $parser->parse();
    $tokens = $parser->getTokens();

    // Expecting the token to be a Type
    expect($tokens[0])->toBeInstanceOf(Type::class);

    // Check that the name of the type is correct
    $type = $tokens[0];
    expect($type->name)->toBe("MyType");

    // Check that the records (fields) are parsed correctly
    $myTypeChildren = $type->children;
    expect($myTypeChildren)->toHaveCount(1);

    $child = $myTypeChildren[0];
    expect($child->getParamName(0))->toBe(null);
    expect($child->getParamType(0))->toBe('');
});

it('parses a simple type on one line correctly', function () {
    $toParse = "type MyType {Case Bis Twice Bruh}";
    $parser = new Parser($toParse);
    $parser->parse();
    $tokens = $parser->getTokens();

    // Expecting the token to be a Type
    expect($tokens[0])->toBeInstanceOf(Type::class);

    // Check that the name of the type is correct
    $type = $tokens[0];
    expect($type->name)->toBe("MyType");

    // Check that the records (fields) are parsed correctly
    $myTypeChildren = $type->children;
    expect($myTypeChildren)->toHaveCount(4);
});

it('parses a simple type on one line with args correctly', function () {
    $toParse = "type MyType {Case(int a) Bis(int a)}";
    $parser = new Parser($toParse);
    $parser->parse();
    $tokens = $parser->getTokens();

    // Expecting the token to be a Type
    expect($tokens[0])->toBeInstanceOf(Type::class);

    // Check that the name of the type is correct
    $type = $tokens[0];
    expect($type->name)->toBe("MyType");

    // Check that the records (fields) are parsed correctly
    $myTypeChildren = $type->children;
    expect($myTypeChildren)->toHaveCount(2);

    foreach ($myTypeChildren as /** @var Record */$child) {
        expect($child->getParamType(0))->toBe('int');
        expect($child->getParamName(0))->toBe('a');
    }
});

it('parses a simple malformed type with extra data on arguments', function () {
    $toParse = "type MyType {
        Case(int val lol)
    }";
    $parser = new Parser($toParse);
    $parser->parse();
    $tokens = $parser->getTokens();

    expect($tokens[0])->toBeInstanceOf(Type::class);

    // Check that the name of the type is correct
    $type = $tokens[0];
    expect($type->name)->toBe("MyType");

    // Check that the records (fields) are parsed correctly
    $myTypeChildren = $type->children;
    expect($myTypeChildren)->toHaveCount(1);

    $child = $myTypeChildren[0];
    expect($child->getParamName(0))->toBe('val');
    expect($child->getParamType(0))->toBe('int');
});

it('parses a result correctly', function () {
    $toParse = "result MyResult(string, null)";
    $parser = new Parser($toParse);
    $parser->parse();
    $tokens = $parser->getTokens();

    // Expecting the token to be a Result
    expect($tokens)->toHaveCount(1);
    expect($tokens[0])->toBeInstanceOf(Result::class);

    // Check that the result name and args are correct
    $result = $tokens[0];
    expect($result->getParamType(0))->toBe('string');
    expect($result->getParamType(1))->toBe('null');
});
it('parses an option correctly', function () {
    $toParse = "option UsersOption(string)";
    $parser = new Parser($toParse);
    $parser->parse();
    $tokens = $parser->getTokens();

    // Expecting the token to be a Result
    expect($tokens)->toHaveCount(1);
    expect($tokens[0])->toBeInstanceOf(Option::class);

    // Check that the result name and args are correct
    $result = $tokens[0];
    expect($result->getParamType(0))->toBe('string');
});

it('parses multiple records in a type', function () {
    $toParse = "type MyType {
        One(int fieldOne, string fieldTwo)
        Two
    }";
    $parser = new Parser($toParse);
    $parser->parse();
    $tokens = $parser->getTokens();

    // Expecting the token to be a Type
    expect($tokens)->toHaveCount(1);
    expect($tokens[0])->toBeInstanceOf(Type::class);

    // Check that the records (fields) are parsed correctly
    $fields = $tokens[0]->children;
    expect($fields)->toHaveCount(2);
});

it('skips comments correctly', function () {
    $toParse = "# A comment\ntype MyType {
        Case
    }";
    $parser = new Parser($toParse);
    $parser->parse();
    $tokens = $parser->getTokens();

    // Expecting the token to be a Type
    // expect($tokens)->toHaveCount(1);
    expect($tokens[0])->toBeInstanceOf(Type::class);

    // Check that the name of the type is correct
    $type = $tokens[0];
    expect($type->name)->toBe("MyType");

    // Check that the records (fields) are parsed correctly
    $myTypeChildren = $type->children;
    expect($myTypeChildren)->toHaveCount(1);

    $child = $myTypeChildren[0];
    expect($child->getParamName(0))->toBe(null);
    expect($child->getParamType(0))->toBe('');
});
