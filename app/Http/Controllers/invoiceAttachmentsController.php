<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\invoice_attachments;

use Illuminate\Support\Facades\Auth;

class invoiceAttachmentsController extends Controller
{



    public function store(Request $request)
    {

        
        $this->validate($request, [

        'file_name' => 'mimes:pdf,jpeg,png,jpg',

        ], [
            'file_name.mimes' => 'صيغة المرفق يجب ان تكون   pdf, jpeg , png , jpg',
        ]);
        
        $image = $request->file('file_name');
        $file_name = $image->getClientOriginalName();

        $attachments =  new invoice_attachments();
        $attachments->file_name = $file_name;
        $attachments->invoice_number = $request->invoice_number;
        $attachments->invoice_id = $request->invoice_id;
        $attachments->Created_by = Auth::user()->name;
        $attachments->save();
           
        // move pic
        $imageName = $request->file_name->getClientOriginalName();
        $request->file_name->move(public_path('Attachment/'. $request->invoice_number), $imageName);
        
        session()->flash('Add', 'تم اضافة المرفق بنجاح');
        return back();

    }




}
