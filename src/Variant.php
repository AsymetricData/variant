<?php

namespace CedricCourteau\Variant;

use CedricCourteau\Variant\Tokens\Result;
use CedricCourteau\Variant\Tokens\Type;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class Variant
{
    public function __construct(
        protected string $rootDirectory,
        protected bool $forcedRecreate = false,
        protected string $baseNamespace,
    ) {
    }

    public function run(): void
    {
        $this->processVariantFiles($this->rootDirectory);
    }

    /**
     * Scans the directory recursively for `.variant` files and processes them.
     */
    private function processVariantFiles(string $directory): void
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $fileInfo) {
            if ($this->isVariantFile($fileInfo)) {
                $this->processFile($fileInfo->getRealPath());
            }
        }
    }

    /**
     * Checks if the given file is a `.variant` file.
     */
    private function isVariantFile(\SplFileInfo $fileInfo): bool
    {
        return $fileInfo->getExtension() === 'variant';
    }

    /**
     * Processes the given variant file.
     */
    private function processFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            echo "Error: Unable to read file: $filePath\n";
            return;
        }

        $parser = new Parser($content);
        $parser->parse();

        $this->generateFileContents($parser, $filePath);
    }

    /**
     * Generates and writes the file contents based on the tokens from the parser.
     */
    private function generateFileContents(Parser $parser, string $filePath): void
    {
        $inferredNamespace = $this->inferNamespaceFromPath($filePath, $this->rootDirectory, $this->baseNamespace);

        foreach ($parser->getTokens() as $token) {
            if ($token instanceof Result) {
                $this->processResultToken($token, $parser, $filePath, $inferredNamespace);
            } elseif ($token instanceof Type) {
                $this->processTypeToken($token, $filePath, $inferredNamespace);
            } else {
                $this->writeToFile($filePath, $inferredNamespace, $token->generateContent($inferredNamespace), $token->name);
            }
        }
    }

    /**
     * Processes a Result token.
     */
    private function processResultToken(Result $token, Parser $parser, string $filePath, string $namespace): void
    {
        $baseClass = $this->findBaseClass($parser->getTokens(), $token->getErrorTypes());
        if ($baseClass) {
            $errorTypes = implode('|', $baseClass->getAllTypes());
            $content = $token->generateContent($namespace, implement: $baseClass->name, errorTypes: $errorTypes);
            $this->writeToFile($filePath, $namespace, $content, $token->name);
        }
    }

    /**
     * Processes a Type token and its child tokens.
     */
    private function processTypeToken(Type $token, string $filePath, string $namespace): void
    {
        $mainContent = $token->generateContent($namespace, implement: $token->name);
        $this->writeToFile($filePath, $namespace, $mainContent, $token->name);

        foreach ($token->children as $childType) {
            $childContent = $childType->generateContent($namespace, implement: $token->name);
            $this->writeToFile($filePath, $namespace, $childContent, $childType->name);
        }
    }

    /**
     * Finds the base class associated with the error types of a Result token.
     */
    private function findBaseClass(array $tokens, string $errorTypes): ?Type
    {
        foreach ($tokens as $token) {
            if ($token instanceof Type && str_contains($token->name, $errorTypes)) {
                return $token;
            }
        }
        return null;
    }

    /**
     * Writes content to a file, handling file existence and forced recreation logic.
     */
    private function writeToFile(string $filePath, string $namespace, string $content, ?string $fileName = null): void
    {
        $directory = dirname($filePath);
        $fileName = $fileName ?? basename($filePath, '.variant') . '.php';
        $file = "$directory/$fileName.php";

        if (!$this->shouldWriteFile($file)) {
            echo "Skipping file creation: $file\n";
            return;
        }

        file_put_contents($file, "<?php\n\nnamespace $namespace;\n\n$content");
        echo "File created: $file\n";
    }

    /**
     * Determines if a file should be written (checks if file exists and force flag).
     */
    private function shouldWriteFile(string $file): bool
    {
        return $this->forcedRecreate || !file_exists($file);
    }

    /**
     * Infers the namespace based on the file path and root directory.
     */
    private function inferNamespaceFromPath(string $filePath, string $rootDirectory, string $baseNamespace): string
    {
        $relativePath = str_replace(realpath($rootDirectory), '', realpath(dirname($filePath)));
        $relativeNamespace = str_replace(DIRECTORY_SEPARATOR, '\\', trim($relativePath, DIRECTORY_SEPARATOR));

        return $relativeNamespace ? "$baseNamespace\\$relativeNamespace" : $baseNamespace;
    }
}
