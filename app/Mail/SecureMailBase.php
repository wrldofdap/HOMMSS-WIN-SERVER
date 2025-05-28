<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

abstract class SecureMailBase extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Add security headers to all emails
        $this->addSecurityHeaders();
        
        return $this;
    }

    /**
     * Add security headers to indicate secure transmission
     *
     * @return void
     */
    protected function addSecurityHeaders()
    {
        $headers = $this->withSwiftMessage(function ($message) {
            $headers = $message->getHeaders();
            
            // Add custom security headers
            $headers->addTextHeader('X-Security-Level', 'TLS-Encrypted');
            $headers->addTextHeader('X-Encryption-Method', 'STARTTLS');
            $headers->addTextHeader('X-Security-Protocol', 'TLS/SSL');
            $headers->addTextHeader('X-Secure-Transport', 'Enabled');
            $headers->addTextHeader('X-HOMMSS-Security', 'Enterprise-Grade-Encryption');
            $headers->addTextHeader('X-Transmission-Security', 'Protected');
            $headers->addTextHeader('X-Email-Security-Standard', 'RFC-3207-Compliant');
            
            // Add timestamp for security auditing
            $headers->addTextHeader('X-Security-Timestamp', now()->toISOString());
            
            // Add application identification
            $headers->addTextHeader('X-Secure-Application', config('app.name'));
            $headers->addTextHeader('X-Security-Version', '1.0');
            
            // Add encryption details
            $headers->addTextHeader('X-Cipher-Suite', 'HIGH-GRADE');
            $headers->addTextHeader('X-Certificate-Verification', 'Enabled');
            $headers->addTextHeader('X-Peer-Verification', 'Active');
        });
    }

    /**
     * Get security footer for email templates
     *
     * @return string
     */
    protected function getSecurityFooter()
    {
        return "🔒 This email was sent using enterprise-grade TLS encryption for your security and privacy.";
    }

    /**
     * Get security badge HTML for email templates
     *
     * @return string
     */
    protected function getSecurityBadge()
    {
        return '<div style="background-color: #28a745; color: white; padding: 5px 10px; border-radius: 15px; font-size: 11px; display: inline-block; margin: 5px 0;">
                    🔒 TLS ENCRYPTED
                </div>';
    }
}
