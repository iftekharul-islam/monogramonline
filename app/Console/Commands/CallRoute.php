<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Http\Request;

class CallRoute extends Command
{
	// https://www.youtube.com/watch?v=mp-XZm7INl8
	private $save_to_path = '';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
     protected $name = 'route:call';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Script will send all delivery email.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
    	$request = Request::create($this->option('uri'), 'GET');
    	$this->info(app()['Illuminate\Contracts\Http\Kernel']->handle($request));
    }

    protected function getOptions()
    {
    	return [
    			['uri', null, InputOption::VALUE_REQUIRED, 'The path of the route to be called', null],
    	];
    }
}
