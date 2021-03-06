<?php

namespace Nevadskiy\Geonames\Parsers;

use Generator;
use Nevadskiy\Geonames\Support\FileReader\FileReader;

class CountryInfoParser implements Parser
{
    /**
     * The decorated parser instance.
     *
     * @var Parser
     */
    protected $parser;

    /**
     * Make a new alternate name parser instance.
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $this->setUpParser($parser);
    }

    /**
     * The alternate name parser fields.
     */
    protected function fields(): array
    {
        return [
            'ISO',
            'ISO3',
            'ISO-Numeric',
            'fips',
            'Country',
            'Capital',
            'Area(in sq km)',
            'Population',
            'Continent',
            'tld',
            'CurrencyCode',
            'CurrencyName',
            'Phone',
            'Postal Code Format',
            'Postal Code Regex',
            'Languages',
            'geonameid',
            'neighbours',
            'EquivalentFipsCode',
        ];
    }

    /**
     * Set up the original parser instance.
     */
    protected function setUpParser(Parser $parser): Parser
    {
        $parser->setFields($this->fields());

        return $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function all(string $path): array
    {
        return $this->parser->all($path);
    }

    /**
     * {@inheritdoc}
     */
    public function each(string $path): Generator
    {
        return $this->parser->each($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileReader(): FileReader
    {
        return $this->parser->getFileReader();
    }

    /**
     * {@inheritdoc}
     */
    public function setFields(array $fields): Parser
    {
        return $this->parser->setFields($fields);
    }
}
