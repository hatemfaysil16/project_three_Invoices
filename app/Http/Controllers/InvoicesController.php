<?php

namespace App\Http\Controllers;

use App\Models\invoice_attachments;
use App\Models\invoices;
use App\Models\invoices_details;
use Illuminate\Http\Request;
use App\Models\sections;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Notifications\Notification;
use App\Notifications\AddInvoice;
use App\Exports\InvoicesExport;
use Maatwebsite\Excel\Facades\Excel;
class InvoicesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invoices = invoices::all();
        return view('invoices.invoices',compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sections = sections::all();

        return view('invoices.add_invoice',compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request;die;

        invoices::create([

          'invoice_number'=>$request->invoice_number, 
          'invoice_Date'=>$request->invoice_Date,  
          'due_date'=>$request->Due_date,  
          'product'=>$request->product,  
          'section_id'=>$request->Section,  
          'Amount_collection'=>$request->Amount_collection,  
          'Amount_commission'=>$request->Amount_Commission,  
          'Discount'=>$request->Discount,  
          'value_vat'=>$request->Value_VAT,  
          'rate_vat'=>$request->Rate_VAT,  
          'total'=>$request->Total,  
          'status'=>'غير مدفوعة',  
          'value_status'=>2,
          'note'=>$request->note,  
        ]);

        $invoices_id = invoices::latest()->first()->id;
        invoices_details::create([
            'id_invoice'=>$invoices_id,
            'invoice_number'=>$request->invoice_number,
            'product'=>$request->product,
            'section'=>$request->Section,
            'status'=>'غير مدفوعة',
            'value_status'=>2,
            'note'=>$request->note,
            'user'=>(Auth::user()->name),
        ]);

        if($request->hasFile('pic')){

            $invoices_id = invoices::latest()->first()->id;
            $image = $request->file('pic');
            $file_name = $image->getClientOriginalName();
            $invoices_number = $request->invoice_number;


            

            $attachments = new invoice_attachments();
            $attachments->file_name	 = $file_name;
            $attachments->invoice_number = $invoices_number;
            $attachments->Created_by= Auth::user()->name;
            $attachments->invoice_id= $invoices_id;
            $attachments->save();


            $imageName = $request->pic->getClientOriginalName();
            $request->pic->move(public_path('Attachment/'.$invoices_number),$imageName);

        }

        $user = User::get();
        $invoices = invoices::latest()->first();
        Notification::send($user, new \App\Notifications\Add_invoice_new($invoices));


        session()->flash('Add','تم اضافة الفاتورة بنجاح');
        return redirect()->back();
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $invoices = invoices::where('id', $id)->first();

        return view('invoices.status_update', compact('invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoices = invoices::where('id', $id)->first();

        $sections = sections::all();
        return view('invoices.edit_invoice', compact('sections', 'invoices'));
    }


    
    public function update(Request $request)
    {
        
        $invoices = invoices::findOrFail($request->invoice_id);
        $invoices->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'due_date' => $request->due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'value_vat' => $request->Value_VAT,
            'rate_vat' => $request->Rate_VAT,
            'total' => $request->Total,
            'note' => $request->note,
        ]);

        session()->flash('edit', 'تم تعديل الفاتورة بنجاح');
        return back();
    }

    public function Status_Update($id, Request $request)
    {


        $invoices = invoices::findOrFail($id);

        if ($request->Status === 'مدفوعة') {

            
            $invoices->update([
                'value_status' => 1,
                'status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            invoices_details::create([
                'id_invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'section' => $request->Section,
                'status' => $request->Status,
                'value_status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }

        else {
            $invoices->update([
                'value_status' => 3,
                'status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            invoices_details::create([
                'id_invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'section' => $request->Section,
                'status' => $request->Status,
                'value_status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invoices');

    }

    
    public function destroy(Request $request)
    {

        $id = $request->invoice_id;
        $invoices = invoices::where('id', $id)->first();
        $Details = invoice_attachments::where('invoice_id', $id)->first();

         $id_page =$request->id_page;


        if (!$id_page==2) {

        if (!empty($Details->invoice_number)) {

            Storage::disk('public_uploads')->deleteDirectory($Details->invoice_number);
        }

        $invoices->forceDelete();
        session()->flash('delete_invoice');
        return redirect('/invoices');

        }

        else {

            $invoices->delete();
            session()->flash('archive_invoice');
            return redirect('/Archive');
        }


    }


    public function getproducts($id)
    {
        $products = DB::table("products")->where("section_id", $id)->pluck("Product_name", "id");
        return json_encode($products);
    }
    
    public function Invoice_Paid()
    {
        $invoices = Invoices::where('Value_Status', 1)->get();
        return view('invoices.invoices_paid',compact('invoices'));
    }

    public function Invoice_unPaid()
    {
        $invoices = Invoices::where('Value_Status',2)->get();
        return view('invoices.invoices_unpaid',compact('invoices'));
    }

    public function Invoice_Partial()
    {
        $invoices = Invoices::where('Value_Status',3)->get();
        return view('invoices.invoices_Partial',compact('invoices'));
    }

    public function Print_invoice($id)
    {
        $invoices = invoices::where('id', $id)->first();
        return view('invoices.Print_invoice',compact('invoices'));
    }

    public function export() 
    {
        return Excel::download(new InvoicesExport, 'فواتير.xlsx');
    }


    
    public function MarkAsRead_all (Request $request)
    {
        $userUnreadNotification = auth()->user()->unreadNotifications;

        if($userUnreadNotification) {
            $userUnreadNotification->markAsRead();
            return back();
        }
    }


    public function unreadNotifications_count()
    {
        return auth()->user()->unreadNotifications->count();
    }


    public function unreadNotifications()
    {
        foreach (auth()->user()->unreadNotifications as $notification){
        return $notification->data['title'];
        }

    }

}

