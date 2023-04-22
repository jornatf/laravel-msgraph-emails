<?php

namespace Jornatf\MsGraphMailer\Commands;

use Illuminate\Console\Command;

class MsGraphMailerCommand extends Command
{
    public $signature = 'msgraph-mailer';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
