<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $attachments;
    public $pdf;

    public function __construct($subject, $content, $attachments=[], $pdf = null)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->attachments = $attachments;
        $this->pdf = $pdf;


        // dd($attachments);
    }

    public function build()
    {
        $email = $this->subject($this->subject)
            ->view('backend.emails.quotation', ['content' => $this->content]);
        // dd($email);
        // foreach ($this->attachments as $file) {
        //     $storagePath = storage_path('app/public/' . ltrim($file, '/'));
        //     if (file_exists($storagePath)) {
        //         $email->attach($storagePath);
        //         Log::info("📎 Attached file: $storagePath");
        //     } else {
        //         Log::warning("⚠️ File not found: $storagePath");
        //     }
        // }

        // if (!empty($this->pdf)) {
        //     $parsedUrl = parse_url($this->pdf, PHP_URL_PATH);
        //     $relativePath = 'files/pdfs/' . basename($parsedUrl);
        //     $filePath = public_path($relativePath);

        //     if (file_exists($filePath)) {
        //         $email->attach($filePath);
        //         Log::info("📄 Attached PDF: {$filePath}");
        //     } else {
        //         Log::error("❌ PDF file not found: {$filePath}");
        //     }
        // }
        // if (!empty($this->pdf) && is_string($this->pdf)) {
        //     $parsedUrl = parse_url($this->pdf, PHP_URL_PATH);
        //     $relativePath = 'files/pdfs/' . basename($parsedUrl);
        //     $filePath = public_path($relativePath);

        //     if (file_exists($filePath)) {
        //         $email->attach($filePath);
        //         Log::info("📄 Attached PDF: {$filePath}");
        //     } else {
        //         Log::error("❌ PDF file not found: {$filePath}");
        //     }
        // } elseif (!empty($this->pdf)) {
        //     Log::error("❌ PDF path is not a string: " . print_r($this->pdf, true));
        // }

        return $email;
    }
}
