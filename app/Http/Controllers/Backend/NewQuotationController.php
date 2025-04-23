<?php

namespace App\Http\Controllers\Backend;

use App\Events\QuotationCreated;
use App\Helpers\DateUtility;
use App\Helpers\FileUtility;
use App\Models\Item;
use App\Models\NewQuotation;
use App\Models\NewQuotationAttachment;
use App\Models\NewQuotationFile;
use App\Models\NewQuotationItem;
use App\Models\Party;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
// use Defuse\Crypto\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


class NewQuotationController extends BackendController
{
    public String $routePrefix = "quotation";
    public $modelClass = NewQuotation::class;
    public function index()
    {
        $conditions = $this->_get_conditions(Route::currentRouteName());
        
        // dd($conditions);
        $records = $this->getPaginagteRecords($this->modelClass::where($conditions)->with([
            "quotationItem",
            "party"
        ]),Route::currentRouteName());
        // dd($records);

        $partyList = Party::getListCache();


        $this->setForView(compact("records", "partyList"));

        return $this->viewIndex(__FUNCTION__);
    }

    private function _get_conditions($cahe_key)
    {
        $conditions = $this->getConditions($cahe_key, [
            ['field' => 'is_email_sent', 'type' => 'int'],
            ['field' => 'is_new', 'type' => 'int'],
            ['field' => 'party_id', 'type' => ''],
            ['field' => 'status', 'type' => ''],
            ['field' => 'follow_up_user_id', 'type' => ''],
            ['field' => 'follow_up_date', 'type' => 'date'],
            ['field' => 'follow_up_type', 'type' => ''],
            ['field' => 'comments', 'type' => 'string'],
            ['field' => 'customer_name', 'type' => 'string'],
        ]);

        return $conditions;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new $this->modelClass();

        $form = [
            'url' => route($this->routePrefix . '.store'),
            'method' => 'POST',
        ];

        $model->date = Carbon::today()->format('d-M-Y');

        $this->_set_list_for_form($model);

        // $sourceList = Source::pluck('resources', 'id')->toArray();

        $this->setForView(compact("model", 'form'));

        return $this->view("form");
    }


    private function _set_list_for_form($model)
    {
        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->party_id) {
            $conditions["or_id"] = $model->party_id;
        }

        $partyList = Party::getList("id", "name", $conditions);

        $conditions = [
            "or_id" => []
        ];

        if ($model && $model->quotationItem) {
            foreach ($model->quotationItem as $quotationItem) {
                $conditions["or_id"][] = $quotationItem->item_id;
            }
        }

        $itemList = Item::getList("id", "name", $conditions);
        $userList = User::getList("id");


        $this->setForView(compact('partyList', 'itemList', 'userList'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validate_data = $request->validate([
            'date' => 'required|date',
            'is_new' => 'nullable|integer',
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'party_id' => 'nullable|exists:parties,id',
            'status' => 'required|string',
            'follow_up_user_id ' => 'nullable|integer',
            'follow_up_date' => 'nullable|date',
            'follow_up_type' => 'nullable',
            'comments' => 'nullable|string',

            'quotation_items' => 'nullable|array',
            'quotation_items.item_id.*' => 'required|exists:items,id',
            'quotation_items.price.*' => 'required|numeric|min:1',
            'quotation_items.qty.*' => 'nullable|numeric|min:1',
            'quotation_items.amount.*' => 'nullable|numeric|min:1',

            'file.*' => 'file|mimes:pdf,jpg,png|max:2048',

        ]);
        $validate_data['date'] = \Carbon\Carbon::parse($validate_data['date'])->format('Y-m-d');
        $validate_data['follow_up_date'] = \Carbon\Carbon::parse($validate_data['follow_up_date'])->format('Y-m-d');
        // dd($validate_data);
        try {
            DB::beginTransaction();
            
            $quotation_items = $validate_data['quotation_items'] ?? [];
            
            unset($validate_data['quotation_items']);
            
            // $validate_data['created_by'] = auth()->id();
            
            $quotation = NewQuotation::create($validate_data);
            // dd($quotation);
            if (!empty($quotation_items)) {
                $combined_items = [];
                foreach ($quotation_items['item_id'] as $index => $item_id) {
                    $combined_items[] = [
                        'item_id' => $item_id,
                        'price' => $quotation_items['price'][$index],
                        'qty' => $quotation_items['qty'][$index] ?? 0,
                        'amount' => $quotation_items['amount'][$index] ?? 0,
                    ];
                }
                
                foreach ($combined_items as $item) {
                    $item['quotation_id'] = $quotation->id;
                    NewQuotationItem::create($item);
                }
                // dd($combined_items);
            }
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $file) {
                    $filePath = $file->store(NewQuotationFile::getFileSavePath(), 'public');
                    NewQuotationFile::create([
                        'quotation_id' => $quotation->id,
                        'file' => NewQuotationFile::getFileSavePath() . basename($filePath),
                    ]);
                }
            }

            $pdfDirectory = public_path('files/pdfs');
            if (!File::exists($pdfDirectory)) {
                File::makeDirectory($pdfDirectory, 0755, true, true);
            }
            
            $pdfPath = public_path("files/pdfs/quotation_{$quotation->id}.pdf");
            if (File::exists($pdfPath)) {
                File::delete($pdfPath);
            }
            
            $items = NewQuotationItem::where('quotation_id', $quotation->id)
            ->with('Item')
            ->get();
            $pdf = Pdf::loadView('backend.pdf.quotation', ['quotation' => $quotation, 'items' => $items]);
            
            $pdf->save($pdfPath);

            $quotation->pdf = "files/pdfs/quotation_{$quotation->id}.pdf";
            $quotation->save();

            DB::commit();

            $this->saveSqlLog();

            return back()->with('success', 'Quotation created successfully');
        } catch (\Exception $ex) {
            DB::rollBack();

            return back()->withInput()->with('fail', $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = $this->modelClass::with([
            "party",
            "quotationItem",
            'quotationFiles'
        ])->findOrFail($id);
        // ś
        // dd($model);

        $form = [
            'url' => route($this->routePrefix . '.update', $id),
            'method' => 'PUT',
        ];

        $quotation_items = $model->quotationItem->toArray();
        // dd($quotation_items);

        $model->date = Carbon::today()->format('d-M-Y');

        $this->_set_list_for_form($model);

        $partyList = Party::getList('id');


        $this->setForView(compact("model", "form", "quotation_items", "partyList"));


        return $this->view("form");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $quotation = NewQuotation::findOrFail($id);

        // Validate request
        $validate_data = $request->validate([
            'date' => 'required|date',
            'is_new' => 'nullable|integer',
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'party_id' => 'nullable|exists:parties,id',
            'status' => 'required|string',
            'follow_up_user_id' => 'nullable|integer', // Fixed space issue
            'follow_up_date' => 'nullable|date',
            'follow_up_type' => 'nullable|string',
            'comments' => 'nullable|string',

            'quotation_items' => 'nullable|array',
            'quotation_items.item_id' => 'required|array',
            'quotation_items.item_id.*' => 'required|exists:items,id',
            'quotation_items.price' => 'required|array',
            'quotation_items.price.*' => 'required|numeric|min:1',
            'quotation_items.qty' => 'required|array',
            'quotation_items.qty.*' => 'required|numeric|min:1',
            'quotation_items.amount' => 'required|array',
            'quotation_items.amount.*' => 'required|numeric|min:1',

            'file.*' => 'file|mimes:pdf,jpg,png|max:2048',
        ]);
        $validate_data['date'] = \Carbon\Carbon::parse($validate_data['date'])->format('Y-m-d');
        $validate_data['follow_up_date'] = \Carbon\Carbon::parse($validate_data['follow_up_date'])->format('Y-m-d');

        try {
            DB::beginTransaction();

            $quotation_items = $validate_data['quotation_items'] ?? [];
            unset($validate_data['quotation_items']);
            
            $quotation->update($validate_data);
            
            $new_items = [];
            
            foreach ($quotation_items['item_id'] as $index => $item_id) {
                $new_items[] = [
                    'quotation_id' => $quotation->id,
                    'item_id' => $item_id,
                    'price' => $quotation_items['price'][$index],
                    'qty' => $quotation_items['qty'][$index] ?? 0,
                    'amount' => $quotation_items['amount'][$index] ?? 0,
                ];
            }
            // dd($quotation_items);


            NewQuotationItem::where('quotation_id', $quotation->id)->delete();
            NewQuotationItem::upsert($new_items,['item_id','price', 'qty', 'amount']);
            // NewQuotationItem::update($new_items);

            // Handle file uploads
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->storeAs(NewQuotationFile::getFileSavePath(), $filename, 'public');

                    NewQuotationFile::create([
                        'quotation_id' => $quotation->id,
                        'file' => NewQuotationFile::getFileSavePath() . $filename,
                    ]);
                }
            }
            $pdfPath = "files/pdfs/quotation_{$quotation->id}.pdf";
            $fullPdfPath = public_path($pdfPath);

            $pdfDirectory = public_path('files/pdfs');
            if (!file_exists($pdfDirectory)) {
                mkdir($pdfDirectory, 0755, true);
            }

            if (file_exists($fullPdfPath)) {
                unlink($fullPdfPath);
            }

            $items = NewQuotationItem::where('quotation_id', $quotation->id)
                ->with('Item')
                ->get();
            $pdf = Pdf::loadView('backend.pdf.quotation', [
                'quotation' => $quotation,
                'items' => $items
            ]);

            $pdf->save($fullPdfPath);

            DB::commit();
            return redirect()->route($this->routePrefix . ".index")->with('success', 'Quotation updated successfully');
        } catch (\Exception $ex) {
            DB::rollBack();

            return back()->withInput()->with('fail', 'Error: ' . $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = $this->modelClass::findOrFail($id);
        // dd($model);

        return $this->_destroy($model);
    }
    public function deleteAttachment($id)
    {
        $attachment = NewQuotationFile::findOrFail($id);

        Storage::disk('public')->delete($attachment->file_path);

        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully']);
    }

    protected function beforeViewRender()
    {
        parent::beforeViewRender();

        $statusList = config('constant.status');
        $followtypeList = config('constant.followuptype');
        $quotationstatusList = config('constant.newquotationstatus');

        $this->setForView(compact(
            'statusList','followtypeList', 'quotationstatusList'
        ));
    }
}
