<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\RabbitMq;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send_email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //echo 'tes send mail';

        $RabbitMq = new RabbitMq('101.50.2.41','5672','admin','firdausalaili8894','/');

        $RabbitMq->ListenJob('emailku');
    }
}
