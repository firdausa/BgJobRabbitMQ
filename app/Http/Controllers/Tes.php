<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\RabbitMq;

class Tes extends Controller
{
    public function tes_func()
    {
    	$reg_job = new RabbitMq('101.50.2.41','5672','admin','firdausalaili8894','/');

        $reg_job->SendingJob('emailku','\App\Helpers\Get','show',0,['tes'=>'abc123']);
    }
}
