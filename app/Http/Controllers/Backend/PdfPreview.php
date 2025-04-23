<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\TransferTransaction;

use Illuminate\Http\Request;

class PdfPreview extends Controller
{
    public function pdfpreview(Request $request)
    {
        // dd($request->all());
        $id = $request->transaction_id;
        $msg = $request->narration ?? ''; 
        $trasan =  TransferTransaction::where('transaction_id', $id)->first();
        $order =  Order::where('id', $trasan->order_id)->first(); 
        $transaction =  TransferTransaction::where('transaction_id', $id) 
                        ->with([
                            'item', 
                            'transport',
                            'order',
                            'transaction'
                            ])->get();
        $transactiondata = $transaction;  
            return view('pdf.sale-bill', compact('transactiondata', 'order','msg')); 
    }
}
