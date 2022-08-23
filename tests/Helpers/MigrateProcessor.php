<?php


namespace SingleTableInheritance\Tests\Helpers;


use Cruxinator\SingleTableInheritance\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateProcessor
{
    /**
     * The testCase instance.
     *
     * @var TestCase
     */
    protected $testCase;

    /**
     * The migrator options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Construct a new schema migrator.
     *
     * @param TestCase  $testCase
     * @param array  $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Run migration.
     *
     * @return $this
     */
    public function up()
    {
        $this->dispatch('migrate');

        return $this;
    }

    /**
     * Rollback migration.
     *
     * @return $this
     */
    public function rollback()
    {
        $this->dispatch('migrate:rollback');

        return $this;
    }

    /**
     * Dispatch artisan command.
     *
     * @param  string $command
     *
     * @return void
     */
    protected function dispatch(string $command): void
    {
        Artisan::call($command, $this->options);
    }
}