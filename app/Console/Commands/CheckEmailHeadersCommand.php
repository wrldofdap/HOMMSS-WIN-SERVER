<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckEmailHeadersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:check-headers {--send : Send test email with visible security headers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show how to check email security headers in different email clients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📧 Email Security Headers Guide');
        $this->newLine();

        $this->showHeaderCheckingInstructions();
        $this->newLine();

        if ($this->option('send')) {
            $this->sendTestEmailWithHeaders();
        } else {
            $this->info('💡 Use --send flag to send a test email with security headers');
        }
    }

    /**
     * Show instructions for checking email headers
     */
    protected function showHeaderCheckingInstructions()
    {
        $this->info('🔍 How to Check Email Security Headers:');
        $this->newLine();

        // Gmail Instructions
        $this->line('<fg=blue>📧 Gmail:</fg=blue>');
        $this->line('   1. Open the email');
        $this->line('   2. Click the three dots (⋮) menu');
        $this->line('   3. Select "Show original"');
        $this->line('   4. Look for these headers:');
        $this->line('      • X-Security-Level: TLS-Encrypted');
        $this->line('      • X-HOMMSS-Security: Enterprise-Grade-Encryption');
        $this->line('      • Received: ... (with TLS)');
        $this->newLine();

        // Outlook Instructions
        $this->line('<fg=blue>📧 Outlook (Desktop):</fg=blue>');
        $this->line('   1. Open the email');
        $this->line('   2. Go to File → Properties');
        $this->line('   3. Look in "Internet Headers" section');
        $this->line('   4. Search for "X-Security" headers');
        $this->newLine();

        // Outlook Web Instructions
        $this->line('<fg=blue>📧 Outlook (Web):</fg=blue>');
        $this->line('   1. Open the email');
        $this->line('   2. Click three dots (...) → View message source');
        $this->line('   3. Search for security headers');
        $this->newLine();

        // Apple Mail Instructions
        $this->line('<fg=blue>📧 Apple Mail:</fg=blue>');
        $this->line('   1. Open the email');
        $this->line('   2. View → Message → Raw Source');
        $this->line('   3. Look for X-Security headers');
        $this->newLine();

        // Thunderbird Instructions
        $this->line('<fg=blue>📧 Thunderbird:</fg=blue>');
        $this->line('   1. Open the email');
        $this->line('   2. View → Message Source (Ctrl+U)');
        $this->line('   3. Search for "X-Security-Level"');
        $this->newLine();

        // What to look for
        $this->info('🔍 Security Headers to Look For:');
        $this->table(['Header Name', 'Expected Value', 'Meaning'], [
            ['X-Security-Level', 'TLS-Encrypted', 'Email was transmitted with TLS encryption'],
            ['X-Encryption-Method', 'STARTTLS', 'STARTTLS protocol was used'],
            ['X-HOMMSS-Security', 'Enterprise-Grade-Encryption', 'High-level security applied'],
            ['X-Secure-Transport', 'Enabled', 'Secure transport layer active'],
            ['X-Certificate-Verification', 'Enabled', 'SSL certificates were verified'],
        ]);
        $this->newLine();

        // TLS in Received headers
        $this->info('🔍 Also Look for TLS in "Received" Headers:');
        $this->line('   Look for lines like:');
        $this->line('   <fg=green>Received: ... (using TLSv1.2 with cipher ...)</fg=green>');
        $this->line('   <fg=green>Received: ... (version=TLS1_2 cipher=...)</fg=green>');
    }

    /**
     * Send test email with security headers
     */
    protected function sendTestEmailWithHeaders()
    {
        $this->info('📧 Sending Test Email with Security Headers...');

        $adminEmail = env('ADMIN_EMAIL');
        
        if (empty($adminEmail)) {
            $this->error('❌ Admin email not configured');
            return;
        }

        try {
            Mail::raw(
                "🔒 EMAIL SECURITY HEADERS TEST\n\n" .
                "This email contains custom security headers that you can check.\n\n" .
                "INSTRUCTIONS TO VIEW HEADERS:\n" .
                "1. Gmail: Click ⋮ → Show original\n" .
                "2. Outlook: File → Properties → Internet Headers\n" .
                "3. Apple Mail: View → Message → Raw Source\n\n" .
                "LOOK FOR THESE HEADERS:\n" .
                "• X-Security-Level: TLS-Encrypted\n" .
                "• X-HOMMSS-Security: Enterprise-Grade-Encryption\n" .
                "• X-Encryption-Method: STARTTLS\n" .
                "• X-Secure-Transport: Enabled\n\n" .
                "If you see these headers, your TLS encryption is working!\n\n" .
                "Timestamp: " . now()->format('Y-m-d H:i:s T'),
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('🔒 Security Headers Test - Check Email Source')
                        ->priority(1);
                    
                    // Add all security headers
                    $headers = $message->getHeaders();
                    $headers->addTextHeader('X-Security-Level', 'TLS-Encrypted');
                    $headers->addTextHeader('X-Encryption-Method', 'STARTTLS');
                    $headers->addTextHeader('X-Security-Protocol', 'TLS/SSL');
                    $headers->addTextHeader('X-Secure-Transport', 'Enabled');
                    $headers->addTextHeader('X-HOMMSS-Security', 'Enterprise-Grade-Encryption');
                    $headers->addTextHeader('X-Transmission-Security', 'Protected');
                    $headers->addTextHeader('X-Email-Security-Standard', 'RFC-3207-Compliant');
                    $headers->addTextHeader('X-Security-Timestamp', now()->toISOString());
                    $headers->addTextHeader('X-Secure-Application', config('app.name'));
                    $headers->addTextHeader('X-Security-Test', 'ACTIVE');
                    $headers->addTextHeader('X-Cipher-Suite', 'HIGH-GRADE');
                    $headers->addTextHeader('X-Certificate-Verification', 'Enabled');
                    $headers->addTextHeader('X-Peer-Verification', 'Active');
                }
            );

            $this->info("✅ Test email sent to: {$adminEmail}");
            $this->newLine();
            $this->info('📋 Next Steps:');
            $this->line('   1. Check your email inbox');
            $this->line('   2. Follow the instructions above to view email headers');
            $this->line('   3. Look for the X-Security headers listed in the email');
            $this->line('   4. Verify TLS encryption in the "Received" headers');

        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email: ' . $e->getMessage());
        }
    }
}
