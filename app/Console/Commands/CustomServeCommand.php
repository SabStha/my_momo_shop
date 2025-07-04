<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class CustomServeCommand extends Command
{
    protected $signature = 'serve:custom {--host=127.0.0.1} {--port=8000}';
    protected $description = 'Serve the application on the PHP development server (Windows compatible)';

    public function handle()
    {
        $host = $this->option('host');
        $port = $this->option('port');
        
        $server = file_exists(base_path('server.php'))
            ? base_path('server.php')
            : __DIR__.'/../../../resources/server.php';

        $command = [
            (new PhpExecutableFinder)->find(false),
            '-S',
            $host.':'.$port,
            $server,
        ];

        $this->info("Starting development server on http://{$host}:{$port}");
        $this->info('Press Ctrl+C to stop the server');
        $this->newLine();

        $process = new Process($command);
        $process->setTty(true);
        $process->run();
    }
} 