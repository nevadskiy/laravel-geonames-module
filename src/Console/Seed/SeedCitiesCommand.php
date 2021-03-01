<?php

namespace Nevadskiy\Geonames\Console\Seed;

use Generator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Seeders\CitySeeder;
use RuntimeException;

class SeedCitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:seed:cities {--source=} {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed cities into the database.';

    /**
     * Execute the console command.
     */
    public function handle(Dispatcher $dispatcher, CitySeeder $seeder, GeonamesParser $parser): void
    {
        $this->info('Start seeding cities. It may take some time.');

        $dispatcher->dispatch(new GeonamesCommandReady());

        $this->truncate();

        $this->setUpProgressBar($parser);

        foreach ($this->cities($parser) as $id => $city) {
            $seeder->seed($city, $id);
        }

        $this->info('Cities have been successfully seeded.');
    }

    /**
     * Truncate a table if option is specified.
     */
    private function truncate(): void
    {
        // TODO: add production warning

        if ($this->option('truncate')) {
            City::query()->truncate();
            $this->info('Cities table has been truncated.');
        }
    }

    /**
     * Set up the progress bar.
     */
    private function setUpProgressBar(GeonamesParser $geonamesParser, int $step = 1000): void
    {
        $progress = $this->output->createProgressBar();

        $geonamesParser->enableCountingLines()
            ->onReady(static function (int $linesCount) use ($progress) {
                $progress->start($linesCount);
            })
            ->onEach(static function () use ($progress, $step) {
                $progress->advance($step);
            }, $step)
            ->onFinish(function () use ($progress) {
                $progress->finish();
                $this->output->newLine();
            });
    }

    /**
     * Get cities for seeding.
     */
    private function cities(GeonamesParser $parser): Generator
    {
        return $parser->forEach($this->getCitiesSourcePath());
    }

    /**
     * Get cities source path.
     */
    protected function getCitiesSourcePath(): string
    {
        if ($this->hasOptionSourcePath()) {
            return $this->getOptionSourcePath();
        }

        return config('geonames.directory') . DIRECTORY_SEPARATOR . config('geonames.files.all_countries');
    }

    /**
     * Determine whether the command has given source option.
     *
     * @return bool
     */
    protected function hasOptionSourcePath(): bool
    {
        return (bool) $this->option('source');
    }

    /**
     * Get source path from the command option.
     *
     * @return string
     */
    public function getOptionSourcePath(): string
    {
        $path = base_path($this->option('source'));

        if (! file_exists($path)) {
            throw new RuntimeException("File does not exist {$path}.");
        }

        return $path;
    }
}