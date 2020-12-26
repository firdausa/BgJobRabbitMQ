<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Helpers\RabbitMq; //mengenalkan library rabbitmq


class job_register_send_mail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:job_register';

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
        $reg_job = new RabbitMq('101.50.2.41','5672','admin','firdausalaili8894','/');

        $reg_job->SendingJob('emailku','\App\Helpers\Get','show',0,['tes'=>'abc123']);
    }
}
