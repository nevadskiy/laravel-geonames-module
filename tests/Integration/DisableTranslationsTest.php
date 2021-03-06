<?php

namespace Nevadskiy\Geonames\Tests\Integration;

use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Tests\DatabaseTestCase;
use Nevadskiy\Translatable\Models\Translation;

class DisableTranslationsTest extends DatabaseTestCase
{
    /**
     * Default configurations.
     *
     * @var array
     */
    protected $config = [
        'geonames.translations' => false,
    ];

    /** @test */
    public function it_can_disable_translations_table(): void
    {
        self::assertFalse(
            Schema::hasTable((new Translation())->getTable())
        );
    }
}
