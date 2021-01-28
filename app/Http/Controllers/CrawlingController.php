<?php

namespace App\Http\Controllers;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CrawlingController extends Controller
{
    //run python script of crawling data to insert into system
    public function crawler()
    {
        $process = new Process(['python', 'sub_category_crawler.py']);

        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();
    }
}
