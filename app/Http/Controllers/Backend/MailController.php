<?php

namespace App\Http\Controllers\Backend;

use App\Mail\QuotationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailController extends BackendController
{
    public function sendEmail(Request $request)
    {
        // Handle and normalize attachments
        // dd($request->all());
        $decoded = json_decode($request->attachments, true);
        $request->merge([
            'attachments' => is_array($decoded) ? $decoded : []
        ]);
        // if (!is_array($request->attachments)) {
            //     if (is_string($request->attachments)) {
        //     } else {
        //         $request->merge([
        //             'attachments' => []
        //         ]);
        //     }
        // }

        // Validate the input
        $request->validate([
            'to_email' => 'required|email',
            'subject' => 'required|string',
            'content' => 'required|string',
            'pdf_attachment' => 'nullable|string',
            'attachments' => 'nullable|array',
        ]);
        
        // Prepare data
        $toEmail = $request->to_email;
        $subject = $request->subject;
        $content = $request->content;
        $pdf = $request->pdf_attachment;
        $attachments = $request->attachments;
        // dd($attachments);

        try {
            // Send the email with attachments
            Mail::to($toEmail)->send(new QuotationMail($subject, $content,$attachments, $pdf));

            return response()->json(['success' => true, 'message' => 'Email sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
