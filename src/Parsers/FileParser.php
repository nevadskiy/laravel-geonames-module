<?php

namespace Nevadskiy\Geonames\Parsers;

use Generator;
use Nevadskiy\Geonames\Support\FileReader\FileReader;

class FileParser implements Parser
{
    /**
     * The file reader instance.
     *
     * @var FileReader
     */
    protected $fileReader;

    /**
     * Indicates fields of the parsed file.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * CountryInfoParser constructor.
     */
    public function __construct(FileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileReader(): FileReader
    {
        return $this->fileReader;
    }

    /**
     * {@inheritdoc}
     */
    public function setFields(array $fields): Parser
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function each(string $path): Generator
    {
        foreach ($this->fileReader->forEachLine($path) as $index => $line) {
            if (empty($line) && $this->shouldSkipEmptyLines()) {
                continue;
            }

            if ($this->isCommentedLine($line) && $this->shouldSkipCommentedLines()) {
                continue;
            }

            $data = $this->parseLine($line);

            if ($this->isHeadingLine($data) && $this->shouldSkipHeadingLines()) {
                continue;
            }

            yield $data;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all(string $path): array
    {
        return iterator_to_array($this->each($path));
    }

    /**
     * Determine whether the parser should skip empty lines.
     */
    protected function shouldSkipEmptyLines(): bool
    {
        return true;
    }

    /**
     * Determine whether the parser should skip lines with leading hashes.
     */
    protected function shouldSkipCommentedLines(): bool
    {
        return true;
    }

    /**
     * Determine whether the parser should skip lines that contain headings.
     */
    protected function shouldSkipHeadingLines(): bool
    {
        return true;
    }

    /**
     * Determine whether the given line is the heading row.
     */
    protected function isHeadingLine(array $line): bool
    {
        if (! $this->fields) {
            return false;
        }

        return $this->fields[0] === array_values($line)[0];
    }

    /**
     * Determine whether the given line is commented.
     */
    protected function isCommentedLine(string $line): bool
    {
        return $line[0] === '#';
    }

    /**
     * Parse the given line.
     */
    protected function parseLine(string $line): array
    {
        $mappedLine = $this->mapLine($line);

        foreach ($mappedLine as $key => $value) {
            $mappedLine[$key] = $this->transformValue($value);
        }

        return $mappedLine;
    }

    /**
     * Map the given line into keyable array.
     */
    protected function mapLine(string $line): array
    {
        $values = explode("\t", $line);

        if (! $this->fields) {
            return $values;
        }

        $map = [];

        foreach ($values as $index => $value) {
            $map[$this->fields[$index] ?? $index] = $value;
        }

        return $map;
    }

    /**
     * Transform the given value into typed variable.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function transformValue($value)
    {
        return $value === '' ? null : $value;
    }
}
