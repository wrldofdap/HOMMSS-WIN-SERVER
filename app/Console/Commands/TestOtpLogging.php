<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestOtpLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-otp-logging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test OTP logging functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing OTP logging functionality...');
        
        // Test log entry
        Log::info('DEMO OTP Generated', [
            'email' => 'admin@demo.com',
            'otp' => '123456',
            'message' => 'Test OTP for demo login',
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
        
        $this->info('Test OTP logged successfully!');
        $this->info('Check storage/logs/laravel.log for the entry');
        $this->info('Or run: tail -f storage/logs/laravel.log | grep "OTP"');
        
        return Command::SUCCESS;
    }
}
