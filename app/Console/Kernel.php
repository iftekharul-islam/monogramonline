<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 * @var array
	 */
	protected $commands = [
		\App\Console\Commands\Inspire::class,
		\App\Console\Commands\CallRoute::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule $schedule
	 *
	 * @return void
	 */
	protected function schedule (Schedule $schedule)
	{
		$schedule->command('inspire')
				 ->hourly();

		$schedule->command('sort:csvfiles')
				 ->everyFiveMinutes();

		$schedule->command('sort:imagefiles')
				->everyThirtyMinutes();
	}
}
