<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ProcessGiveawaysJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected $commands = [
      //\App\Console\Commands\ProcessGiveaways::class,
    ];

    protected function schedule(Schedule $schedule) {
      $schedule->job(new ProcessGiveawaysJob)->everyMinute()->withoutOverlapping()->onOneServer();

      //$schedule->command('giveaways:process')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {

        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}