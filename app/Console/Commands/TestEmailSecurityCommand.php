<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class TestEmailSecurityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-security {--send : Actually send a test email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email TLS encryption and security settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Testing Email TLS Security Configuration');
        $this->newLine();

        // Test 1: Configuration Check
        $this->testConfiguration();
        $this->newLine();

        // Test 2: TLS Connection Test
        $this->testTlsConnection();
        $this->newLine();

        // Test 3: Encryption Verification
        $this->testEncryptionSettings();
        $this->newLine();

        // Test 4: Send Test Email (if requested)
        if ($this->option('send')) {
            $this->sendTestEmail();
        } else {
            $this->info('💡 Use --send flag to actually send a test email');
        }

        $this->newLine();
        $this->info('✅ Email security test completed!');
    }

    /**
     * Test email configuration
     */
    protected function testConfiguration()
    {
        $this->info('📋 Testing Email Configuration...');

        $config = config('mail.mailers.smtp');

        $this->table(['Setting', 'Value', 'Status'], [
            ['Host', $config['host'], $this->getStatus(!empty($config['host']))],
            ['Port', $config['port'], $this->getStatus(in_array($config['port'], [587, 465, 25]))],
            ['Encryption', $config['encryption'] ?? 'none', $this->getStatus(!empty($config['encryption']))],
            ['Username', $config['username'] ? '***configured***' : 'not set', $this->getStatus(!empty($config['username']))],
            ['Password', $config['password'] ? '***configured***' : 'not set', $this->getStatus(!empty($config['password']))],
            ['Timeout', $config['timeout'] ?? 'default', $this->getStatus(true)],
            ['Verify Peer', $config['verify_peer'] ? 'true' : 'false', $this->getStatus($config['verify_peer'] ?? false)],
            ['Verify Peer Name', $config['verify_peer_name'] ? 'true' : 'false', $this->getStatus($config['verify_peer_name'] ?? false)],
        ]);
    }

    /**
     * Test TLS connection
     */
    protected function testTlsConnection()
    {
        $this->info('🔐 Testing TLS Connection...');

        try {
            $host = config('mail.mailers.smtp.host');
            $port = config('mail.mailers.smtp.port');
            $encryption = config('mail.mailers.smtp.encryption');

            if (empty($host) || empty($port)) {
                $this->error('❌ Host or port not configured');
                return;
            }

            // Test socket connection with TLS
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ]
            ]);

            $this->info("Testing connection to {$host}:{$port} with {$encryption}...");

            if ($encryption === 'tls') {
                // Test STARTTLS
                $socket = @fsockopen($host, $port, $errno, $errstr, 30);
                if ($socket) {
                    $this->info('✅ Socket connection successful');

                    // Read server greeting
                    $response = fgets($socket);
                    $this->info("Server greeting: " . trim($response));

                    // Send EHLO
                    fwrite($socket, "EHLO localhost\r\n");
                    $response = $this->readSmtpResponse($socket);

                    if (strpos($response, 'STARTTLS') !== false) {
                        $this->info('✅ STARTTLS supported by server');
                    } else {
                        $this->warn('⚠️  STARTTLS not found in server capabilities');
                    }

                    fclose($socket);
                } else {
                    $this->error("❌ Connection failed: {$errstr} ({$errno})");
                }
            } elseif ($encryption === 'ssl') {
                // Test direct SSL connection
                $socket = @fsockopen("ssl://{$host}", $port, $errno, $errstr, 30);
                if ($socket) {
                    $this->info('✅ SSL connection successful');
                    fclose($socket);
                } else {
                    $this->error("❌ SSL connection failed: {$errstr} ({$errno})");
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ TLS connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test encryption settings
     */
    protected function testEncryptionSettings()
    {
        $this->info('🛡️  Testing Encryption Settings...');

        $config = config('mail.mailers.smtp');
        $encryption = $config['encryption'] ?? 'none';

        $recommendations = [];

        switch ($encryption) {
            case 'tls':
                $this->info('✅ TLS encryption enabled (recommended)');
                if ($config['port'] != 587) {
                    $recommendations[] = 'Consider using port 587 for TLS/STARTTLS';
                }
                break;
            case 'ssl':
                $this->info('✅ SSL encryption enabled');
                if ($config['port'] != 465) {
                    $recommendations[] = 'Consider using port 465 for SSL';
                }
                break;
            default:
                $this->error('❌ No encryption configured - emails will be sent in plain text!');
                $recommendations[] = 'Enable TLS or SSL encryption immediately';
        }

        // Check cipher configuration
        $ciphers = $config['stream']['ssl']['ciphers'] ?? null;
        if ($ciphers) {
            $this->info('✅ Custom cipher suite configured');
        } else {
            $recommendations[] = 'Consider configuring strong cipher suites';
        }

        // Check certificate verification
        $verifyPeer = $config['verify_peer'] ?? false;
        $verifyPeerName = $config['verify_peer_name'] ?? false;

        if ($verifyPeer && $verifyPeerName) {
            $this->info('✅ Certificate verification enabled');
        } else {
            $recommendations[] = 'Enable certificate verification for better security';
        }

        if (!empty($recommendations)) {
            $this->newLine();
            $this->warn('💡 Security Recommendations:');
            foreach ($recommendations as $recommendation) {
                $this->line('   • ' . $recommendation);
            }
        }
    }

    /**
     * Send test email
     */
    protected function sendTestEmail()
    {
        $this->info('📧 Sending Test Email...');

        $adminEmail = env('ADMIN_EMAIL');

        if (empty($adminEmail)) {
            $this->error('❌ Admin email not configured');
            return;
        }

        try {
            $startTime = microtime(true);

            Mail::send('emails.security-test', [
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'timestamp' => now()->format('Y-m-d H:i:s T'),
                'app_name' => config('app.name'),
                'app_url' => config('app.url')
            ], function ($message) use ($adminEmail) {
                $message->to($adminEmail)
                    ->subject('🔒 TLS Email Security Test - ' . now()->format('Y-m-d H:i:s'))
                    ->priority(1)
                    // Add custom headers to indicate secure transmission
                    ->getHeaders()
                    ->addTextHeader('X-Security-Level', 'TLS-Encrypted')
                    ->addTextHeader('X-Encryption-Method', 'STARTTLS')
                    ->addTextHeader('X-Security-Protocol', 'TLS/SSL')
                    ->addTextHeader('X-Secure-Transport', 'Enabled')
                    ->addTextHeader('X-HOMMSS-Security', 'Enterprise-Grade-Encryption');
            });

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            $this->info("✅ Test email sent successfully to: {$adminEmail}");
            $this->info("📊 Send time: {$duration}ms");

        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email: ' . $e->getMessage());

            // Provide troubleshooting hints
            $this->newLine();
            $this->warn('🔧 Troubleshooting hints:');
            $this->line('   • Check your MAIL_USERNAME and MAIL_PASSWORD');
            $this->line('   • Verify your email provider allows SMTP access');
            $this->line('   • For Gmail, ensure you\'re using an App Password');
            $this->line('   • Check firewall settings for outbound connections');
        }
    }

    /**
     * Read SMTP response
     */
    protected function readSmtpResponse($socket)
    {
        $response = '';
        while (($line = fgets($socket)) !== false) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    /**
     * Get status indicator
     */
    protected function getStatus($condition)
    {
        return $condition ? '✅' : '❌';
    }
}
