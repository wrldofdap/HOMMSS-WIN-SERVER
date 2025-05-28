<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class DeploymentReadinessTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deployment-test {--quick : Run quick tests only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test HOMMSS application readiness for deployment';

    protected $passedTests = 0;
    protected $totalTests = 0;
    protected $warnings = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 HOMMSS Deployment Readiness Test');
        $this->info('====================================');
        $this->newLine();

        $quick = $this->option('quick');

        // Essential tests
        $this->testEnvironmentConfiguration();
        $this->testDatabaseConnection();
        $this->testApplicationRoutes();
        $this->testModelsAndData();
        $this->testFilePermissions();
        $this->testEmailConfiguration();

        if (!$quick) {
            // Comprehensive tests
            $this->testSecurityFeatures();
            $this->testBackupSystem();
            $this->testFileUploadSecurity();
            $this->testPaymentConfiguration();
        }

        $this->displayResults();
    }

    protected function testEnvironmentConfiguration()
    {
        $this->info('🔧 Testing Environment Configuration...');
        $this->totalTests += 4;

        // Test APP_KEY
        if (config('app.key')) {
            $this->line('✅ APP_KEY is set');
            $this->passedTests++;
        } else {
            $this->error('❌ APP_KEY is not set');
        }

        // Test APP_ENV
        $env = config('app.env');
        if ($env === 'production' || $env === 'local') {
            $this->line("✅ APP_ENV is set to: {$env}");
            $this->passedTests++;
        } else {
            $this->warn("⚠️ APP_ENV is: {$env}");
            $this->warnings++;
        }

        // Test APP_DEBUG
        $debug = config('app.debug');
        if ($env === 'production' && $debug) {
            $this->error('❌ APP_DEBUG should be false in production');
        } else {
            $this->line("✅ APP_DEBUG is properly set: " . ($debug ? 'true' : 'false'));
            $this->passedTests++;
        }

        // Test APP_URL
        if (config('app.url')) {
            $this->line('✅ APP_URL is configured');
            $this->passedTests++;
        } else {
            $this->error('❌ APP_URL is not set');
        }

        $this->newLine();
    }

    protected function testDatabaseConnection()
    {
        $this->info('🗄️ Testing Database Connection...');
        $this->totalTests += 3;

        try {
            // Test connection
            DB::connection()->getPdo();
            $this->line('✅ Database connection successful');
            $this->passedTests++;

            // Test migrations
            $migrations = DB::table('migrations')->count();
            if ($migrations > 0) {
                $this->line("✅ Database migrations applied ({$migrations} migrations)");
                $this->passedTests++;
            } else {
                $this->error('❌ No migrations found in database');
            }

            // Test basic query
            $userCount = User::count();
            $this->line("✅ Database queries working (Users: {$userCount})");
            $this->passedTests++;

        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    protected function testApplicationRoutes()
    {
        $this->info('🛣️ Testing Application Routes...');
        $this->totalTests += 2;

        try {
            $routes = Route::getRoutes();
            $routeCount = count($routes);

            if ($routeCount > 0) {
                $this->line("✅ Routes loaded successfully ({$routeCount} routes)");
                $this->passedTests++;
            } else {
                $this->error('❌ No routes found');
            }

            // Test critical routes exist
            $criticalRoutes = ['home.index', 'login', 'shop.index', 'admin.index'];
            $foundRoutes = 0;

            foreach ($criticalRoutes as $routeName) {
                if (Route::has($routeName)) {
                    $foundRoutes++;
                }
            }

            if ($foundRoutes === count($criticalRoutes)) {
                $this->line('✅ All critical routes exist');
                $this->passedTests++;
            } else {
                $this->warn("⚠️ Some critical routes missing ({$foundRoutes}/" . count($criticalRoutes) . ")");
                $this->warnings++;
            }

        } catch (\Exception $e) {
            $this->error('❌ Route testing failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    protected function testModelsAndData()
    {
        $this->info('📊 Testing Models and Data...');
        $this->totalTests += 4;

        try {
            // Test User model
            $userCount = User::count();
            $this->line("✅ User model working (Count: {$userCount})");
            $this->passedTests++;

            // Test Product model
            $productCount = Product::count();
            $this->line("✅ Product model working (Count: {$productCount})");
            $this->passedTests++;

            // Test Category model
            $categoryCount = Category::count();
            $this->line("✅ Category model working (Count: {$categoryCount})");
            $this->passedTests++;

            // Test Brand model
            $brandCount = Brand::count();
            $this->line("✅ Brand model working (Count: {$brandCount})");
            $this->passedTests++;

        } catch (\Exception $e) {
            $this->error('❌ Model testing failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    protected function testFilePermissions()
    {
        $this->info('📁 Testing File Permissions...');
        $this->totalTests += 3;

        // Test storage directory
        if (is_writable(storage_path())) {
            $this->line('✅ Storage directory is writable');
            $this->passedTests++;
        } else {
            $this->error('❌ Storage directory is not writable');
        }

        // Test bootstrap/cache directory
        if (is_writable(base_path('bootstrap/cache'))) {
            $this->line('✅ Bootstrap cache directory is writable');
            $this->passedTests++;
        } else {
            $this->error('❌ Bootstrap cache directory is not writable');
        }

        // Test public/uploads directory
        $uploadsPath = public_path('uploads');
        if (File::exists($uploadsPath) && is_writable($uploadsPath)) {
            $this->line('✅ Uploads directory is writable');
            $this->passedTests++;
        } else {
            $this->warn('⚠️ Uploads directory may not be writable');
            $this->warnings++;
        }

        $this->newLine();
    }

    protected function testEmailConfiguration()
    {
        $this->info('📧 Testing Email Configuration...');
        $this->totalTests += 3;

        // Test email configuration
        if (config('mail.mailers.smtp.host')) {
            $this->line('✅ Email SMTP host configured');
            $this->passedTests++;
        } else {
            $this->error('❌ Email SMTP host not configured');
        }

        if (config('mail.from.address')) {
            $this->line('✅ Email from address configured');
            $this->passedTests++;
        } else {
            $this->error('❌ Email from address not configured');
        }

        $adminEmail = env('ADMIN_EMAIL');
        if ($adminEmail && !empty(trim($adminEmail))) {
            $this->line('✅ Admin email configured');
            $this->passedTests++;
        } else {
            $this->warn('⚠️ Admin email not configured');
            $this->warnings++;
        }

        $this->newLine();
    }

    protected function testSecurityFeatures()
    {
        $this->info('🔒 Testing Security Features...');
        $this->totalTests += 2;

        try {
            // Test SQL injection protection
            Artisan::call('security:test-sql-injection', ['--quiet' => true]);
            $this->line('✅ SQL injection protection test passed');
            $this->passedTests++;
        } catch (\Exception $e) {
            $this->error('❌ SQL injection protection test failed');
        }

        // Test session configuration
        if (config('session.encrypt')) {
            $this->line('✅ Session encryption enabled');
            $this->passedTests++;
        } else {
            $this->warn('⚠️ Session encryption not enabled');
            $this->warnings++;
        }

        $this->newLine();
    }

    protected function testBackupSystem()
    {
        $this->info('💾 Testing Backup System...');
        $this->totalTests += 2;

        try {
            // Test backup directory
            $backupDir = storage_path('app/backups');
            if (File::exists($backupDir) && is_writable($backupDir)) {
                $this->line('✅ Backup directory exists and is writable');
                $this->passedTests++;
            } else {
                $this->error('❌ Backup directory not accessible');
            }

            // Test backup command exists
            if (Artisan::all()['app:simple-backup-database'] ?? false) {
                $this->line('✅ Backup command available');
                $this->passedTests++;
            } else {
                $this->error('❌ Backup command not found');
            }

        } catch (\Exception $e) {
            $this->error('❌ Backup system test failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    protected function testFileUploadSecurity()
    {
        $this->info('📤 Testing File Upload Security...');
        $this->totalTests += 2;

        // Test upload configuration
        if (config('upload.max_file_size')) {
            $maxSize = config('upload.max_file_size');
            $this->line("✅ File upload size limit configured ({$maxSize} bytes)");
            $this->passedTests++;
        } else {
            $this->warn('⚠️ File upload size limit not configured');
            $this->warnings++;
        }

        // Test FileUploadHelper exists
        if (class_exists('App\Helpers\FileUploadHelper')) {
            $this->line('✅ File upload security helper available');
            $this->passedTests++;
        } else {
            $this->error('❌ File upload security helper not found');
        }

        $this->newLine();
    }

    protected function testPaymentConfiguration()
    {
        $this->info('💳 Testing Payment Configuration...');
        $this->totalTests += 2;

        // Test payment service
        if (class_exists('App\Services\PaymentService')) {
            $this->line('✅ Payment service available');
            $this->passedTests++;
        } else {
            $this->error('❌ Payment service not found');
        }

        // Test payment gateways configured
        $stripeConfigured = config('services.stripe.key') && config('services.stripe.secret');
        $paymongoConfigured = config('services.paymongo.public_key') && config('services.paymongo.secret_key');

        if ($stripeConfigured || $paymongoConfigured) {
            $this->line('✅ At least one payment gateway configured');
            $this->passedTests++;
        } else {
            $this->warn('⚠️ No payment gateways configured');
            $this->warnings++;
        }

        $this->newLine();
    }

    protected function displayResults()
    {
        $this->info('📊 Test Results Summary');
        $this->info('========================');

        $successRate = $this->totalTests > 0 ? round(($this->passedTests / $this->totalTests) * 100, 1) : 0;

        $this->line("✅ Passed: {$this->passedTests}/{$this->totalTests} ({$successRate}%)");

        if ($this->warnings > 0) {
            $this->line("⚠️ Warnings: {$this->warnings}");
        }

        $failed = $this->totalTests - $this->passedTests;
        if ($failed > 0) {
            $this->line("❌ Failed: {$failed}");
        }

        $this->newLine();

        if ($successRate >= 90) {
            $this->info('🎉 Application appears ready for deployment!');
        } elseif ($successRate >= 75) {
            $this->warn('⚠️ Application mostly ready, but address warnings before deployment.');
        } else {
            $this->error('❌ Application needs significant fixes before deployment.');
        }

        $this->newLine();
        $this->info('💡 Run with --quick flag for faster testing');
        $this->info('📖 See deployment-readiness-test.md for detailed testing guide');
    }
}
