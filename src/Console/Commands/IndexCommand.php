<?php

declare(strict_types=1);

namespace Onelegstudios\Index\Console\Commands;

use Illuminate\Console\Command;

class IndexCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'index:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package index.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Index placeholder command executed.');

        return self::SUCCESS;
    }
}
