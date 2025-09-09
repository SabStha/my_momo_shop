<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ProductionSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:setup {--force : Force setup even if already configured}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the application for production deployment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->environment() !== 'production' && !$this->option('force')) {
            $this->error('This command should only be run in production environment!');
            $this->info('Use --force flag to override this check.');
            return 1;
        }

        $this->info('🚀 Setting up Momo Shop for production...');

        // Check if .env file exists
        if (!File::exists(base_path('.env'))) {
            $this->error('❌ .env file not found! Please create it from production.env.example');
            return 1;
        }

        // Generate application key if not set
        if (empty(config('app.key'))) {
            $this->info('🔑 Generating application key...');
            Artisan::call('key:generate', ['--force' => true]);
            $this->info('✅ Application key generated');
        }

        // Run database migrations
        $this->info('📊 Running database migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info('✅ Database migrations completed');

        // Clear and cache configurations
        $this->info('⚡ Optimizing application...');
        $this->optimizeApplication();

        // Set proper file permissions
        $this->info('🔒 Setting file permissions...');
        $this->setFilePermissions();

        // Create storage link
        $this->info('🔗 Creating storage link...');
        Artisan::call('storage:link');
        $this->info('✅ Storage link created');

        // Run production seeder (optional)
        if ($this->confirm('Do you want to run the production seeder to create an admin user?')) {
            Artisan::call('db:seed', ['--class' => 'ProductionSeeder', '--force' => true]);
            $this->info('✅ Production seeder completed');
        }

        $this->info('🎉 Production setup completed successfully!');
        $this->warn('⚠️  Remember to:');
        $this->warn('   1. Configure your .env file with production values');
        $this->warn('   2. Set up SSL certificate');
        $this->warn('   3. Configure your web server');
        $this->warn('   4. Set up monitoring and logging');

        return 0;
    }

    /**
     * Optimize the application for production
     */
    private function optimizeApplication()
    {
        $commands = [
            'config:clear',
            'config:cache',
            'route:clear',
            'route:cache',
            'view:clear',
            'view:cache',
            'event:clear',
            'event:cache',
        ];

        foreach ($commands as $command) {
            Artisan::call($command);
        }

        $this->info('✅ Application optimized');
    }

    /**
     * Set proper file permissions
     */
    private function setFilePermissions()
    {
        $paths = [
            'storage' => 775,
            'bootstrap/cache' => 775,
            '.env' => 644,
        ];

        foreach ($paths as $path => $permission) {
            $fullPath = base_path($path);
            if (File::exists($fullPath)) {
                chmod($fullPath, octdec($permission));
            }
        }

        $this->info('✅ File permissions set');
    }
}
