<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Tax;
use App\Models\Account;
use App\Models\Purchase;
use App\Models\ProductPurchase;
use App\Models\Product_Warehouse;
use App\Models\Payment;
use App\Models\PaymentWithCheque;
use App\Models\PaymentWithCreditCard;
use App\Models\PosSetting;
use App\Models\Currency;
use App\Models\CustomField;
use Illuminate\Support\Facades\DB;
use App\Models\GeneralSetting;
use Stripe\Stripe;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use App\Traits\StaffAccess;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Traits\TenantInfo;

class PurchaseController extends Controller
{
    use TenantInfo, StaffAccess;

    public function index(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-index')) {
            if($request->input('warehouse_id'))
                $warehouse_id = $request->input('warehouse_id');
            else
                $warehouse_id = 0;

            if($request->input('purchase_status'))
                $purchase_status = $request->input('purchase_status');
            else
                $purchase_status = 0;

            if($request->input('payment_status'))
                $payment_status = $request->input('payment_status');
            else
                $payment_status = 0;

            if($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date = $request->input('ending_date');
            }
            else {
                $starting_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d') )))));
                $ending_date = date("Y-m-d");
            }
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
            $lims_pos_setting_data = PosSetting::select('stripe_public_key')->latest()->first();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_account_list = Account::where('is_active', true)->get();
            $custom_fields = CustomField::where([
                                ['belongs_to', 'purchase'],
                                ['is_table', true]
                            ])->pluck('name');
            $field_name = [];
            foreach($custom_fields as $fieldName) {
                $field_name[] = str_replace(" ", "_", strtolower($fieldName));
            }
            return view('backend.purchase.index', compact( 'lims_account_list', 'lims_warehouse_list', 'all_permission', 'lims_pos_setting_data', 'warehouse_id', 'starting_date', 'ending_date', 'purchase_status', 'payment_status', 'custom_fields', 'field_name'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function purchaseData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            5 => 'grand_total',
            6 => 'paid_amount',
        );

        $warehouse_id = $request->input('warehouse_id');
        $purchase_status = $request->input('purchase_status');
        $payment_status = $request->input('payment_status');

        $q = Purchase::whereDate('created_at', '>=' ,$request->input('starting_date'))->whereDate('created_at', '<=' ,$request->input('ending_date'));
        //check staff access
        $this->staffAccessCheck($q);
        if($warehouse_id)
            $q = $q->where('warehouse_id', $warehouse_id);
        if($purchase_status)
            $q = $q->where('status', $purchase_status);
        if($payment_status)
            $q = $q->where('payment_status', $payment_status);

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'purchases.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        //fetching custom fields data
        $custom_fields = CustomField::where([
                        ['belongs_to', 'purchase'],
                        ['is_table', true]
                    ])->pluck('name');
        $field_names = [];
        foreach($custom_fields as $fieldName) {
            $field_names[] = str_replace(" ", "_", strtolower($fieldName));
        }
        if(empty($request->input('search.value'))) {
            $q = Purchase::with('supplier', 'warehouse')
                ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir);
            //check staff access
            $this->staffAccessCheck($q);
            if($warehouse_id)
                $q = $q->where('warehouse_id', $warehouse_id);
            if($purchase_status)
                $q = $q->where('status', $purchase_status);
            if($payment_status)
                $q = $q->where('payment_status', $payment_status);
            $purchases = $q->get();
        }
        else
        {
            $search = $request->input('search.value');
            $q = Purchase::join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')
                ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                ->whereDate('purchases.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir);
            if(Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $q =  $q->with('supplier', 'warehouse')
                        ->where('purchases.user_id', Auth::id())
                        ->orwhere([
                            ['purchases.reference_no', 'LIKE', "%{$search}%"],
                            ['purchases.user_id', Auth::id()]
                        ])
                        ->orwhere([
                            ['suppliers.name', 'LIKE', "%{$search}%"],
                            ['purchases.user_id', Auth::id()]
                        ])
                        ->orwhere([
                            ['product_purchases.imei_number', 'LIKE', "%{$search}%"],
                            ['purchases.user_id', Auth::id()]
                        ]);
                foreach ($field_names as $key => $field_name) {
                    $q = $q->orwhere([
                            ['purchases.user_id', Auth::id()],
                            ['purchases.' . $field_name, 'LIKE', "%{$search}%"]
                        ]);
                }
            }
            elseif(Auth::user()->role_id > 2 && config('staff_access') == 'warehouse') {
                $q =  $q->with('supplier', 'warehouse')
                ->where('purchases.user_id', Auth::id())
                ->orwhere([
                    ['purchases.reference_no', 'LIKE', "%{$search}%"],
                    ['purchases.warehouse_id', Auth::user()->warehouse_id]
                ])
                ->orwhere([
                    ['suppliers.name', 'LIKE', "%{$search}%"],
                    ['purchases.warehouse_id', Auth::user()->warehouse_id]
                ])
                ->orwhere([
                    ['product_purchases.imei_number', 'LIKE', "%{$search}%"],
                    ['purchases.warehouse_id', Auth::user()->warehouse_id]
                ]);
                foreach ($field_names as $key => $field_name) {
                    $q = $q->orwhere([
                        ['purchases.warehouse_id', Auth::user()->warehouse_id],
                        ['purchases.' . $field_name, 'LIKE', "%{$search}%"]
                    ]);
                }
            }
            else {
                $q = $q->with('supplier', 'warehouse')
                    ->orwhere('purchases.reference_no', 'LIKE', "%{$search}%")
                    ->orwhere('suppliers.name', 'LIKE', "%{$search}%")
                    ->orwhere('product_purchases.imei_number', 'LIKE', "%{$search}%");
                foreach ($field_names as $key => $field_name) {
                    $q = $q->orwhere('purchases.' . $field_name, 'LIKE', "%{$search}%");
                }
            }
            $purchases = $q->select('purchases.*')->groupBy('purchases.id')->get();
            $totalFiltered = $q->groupBy('purchases.id')->count();
        }
        $data = array();
        if(!empty($purchases))
        {
            foreach ($purchases as $key=>$purchase)
            {
                $nestedData['id'] = $purchase->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($purchase->created_at->toDateString()));
                $nestedData['reference_no'] = $purchase->reference_no;

                if($purchase->supplier_id) {
                    $supplier = $purchase->supplier;
                }
                else {
                    $supplier = new Supplier();
                }
                $nestedData['supplier'] = $supplier->name;
                if($purchase->status == 1){
                    $nestedData['purchase_status'] = '<div class="badge badge-success">'.trans('file.Recieved').'</div>';
                    $purchase_status = trans('file.Recieved');
                }
                elseif($purchase->status == 2){
                    $nestedData['purchase_status'] = '<div class="badge badge-success">'.trans('file.Partial').'</div>';
                    $purchase_status = trans('file.Partial');
                }
                elseif($purchase->status == 3){
                    $nestedData['purchase_status'] = '<div class="badge badge-danger">'.trans('file.Pending').'</div>';
                    $purchase_status = trans('file.Pending');
                }
                else{
                    $nestedData['purchase_status'] = '<div class="badge badge-danger">'.trans('file.Ordered').'</div>';
                    $purchase_status = trans('file.Ordered');
                }

                if($purchase->payment_status == 1)
                    $nestedData['payment_status'] = '<div class="badge badge-danger">'.trans('file.Due').'</div>';
                else
                    $nestedData['payment_status'] = '<div class="badge badge-success">'.trans('file.Paid').'</div>';

                $nestedData['grand_total'] = number_format($purchase->grand_total, config('decimal'));
                $returned_amount = DB::table('return_purchases')->where('purchase_id', $purchase->id)->sum('grand_total');
                $nestedData['returned_amount'] = number_format($returned_amount, config('decimal'));
                $nestedData['paid_amount'] = number_format($purchase->paid_amount, config('decimal'));
                $nestedData['due'] = number_format($purchase->grand_total- $returned_amount  - $purchase->paid_amount, config('decimal'));
                //fetching custom fields data
                foreach($field_names as $field_name) {
                    $nestedData[$field_name] = $purchase->$field_name;
                }
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.trans("file.action").'
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> '.trans('file.View').'</button>
                                </li>';
                if(in_array("purchases-add", $request['all_permission']))
                    $nestedData['options'] .= '<li>
                        <a href="'.route('purchase.duplicate', $purchase->id).'" class="btn btn-link"><i class="fa fa-copy"></i> '.trans('file.Duplicate').'</a>
                        </li>';
                if(in_array("purchases-edit", $request['all_permission']))
                    $nestedData['options'] .= '<li>
                        <a href="'.route('purchases.edit', $purchase->id).'" class="btn btn-link"><i class="dripicons-document-edit"></i> '.trans('file.edit').'</a>
                        </li>';
                if(in_array("purchase-payment-index", $request['all_permission']))
                    $nestedData['options'] .=
                        '<li>
                            <button type="button" class="get-payment btn btn-link" data-id = "'.$purchase->id.'"><i class="fa fa-money"></i> '.trans('file.View Payment').'</button>
                        </li>';
                if(in_array("purchase-payment-add", $request['all_permission']))
                    $nestedData['options'] .=
                        '<li>
                            <button type="button" class="add-payment btn btn-link" data-id = "'.$purchase->id.'" data-toggle="modal" data-target="#add-payment"><i class="fa fa-plus"></i> '.trans('file.Add Payment').'</button>
                        </li>';
                if(in_array("purchases-delete", $request['all_permission']))
                    $nestedData['options'] .= \Form::open(["route" => ["purchases.destroy", $purchase->id], "method" => "DELETE"] ).'
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> '.trans("file.delete").'</button>
                            </li>'.\Form::close().'
                        </ul>
                    </div>';

                // data for purchase details by one click
                $user = User::find($purchase->user_id);
                if($purchase->currency_id) {
                    $currency = Currency::select('code')->find($purchase->currency_id);
                    if($currency)
                        $currency_code = $currency->code;
                }
                else
                    $currency_code = 'N/A';

                $nestedData['purchase'] = array( '[ "'.date(config('date_format'), strtotime($purchase->created_at->toDateString())).'"', ' "'.$purchase->reference_no.'"', ' "'.$purchase_status.'"',  ' "'.$purchase->id.'"', ' "'.$purchase->warehouse->name.'"', ' "'.$purchase->warehouse->phone.'"', ' "'.preg_replace('/\s+/S', " ", $purchase->warehouse->address).'"', ' "'.$supplier->name.'"', ' "'.$supplier->company_name.'"', ' "'.$supplier->email.'"', ' "'.$supplier->phone_number.'"', ' "'.preg_replace('/\s+/S', " ", $supplier->address).'"', ' "'.$supplier->city.'"', ' "'.$purchase->total_tax.'"', ' "'.$purchase->total_discount.'"', ' "'.$purchase->total_cost.'"', ' "'.$purchase->order_tax.'"', ' "'.$purchase->order_tax_rate.'"', ' "'.$purchase->order_discount.'"', ' "'.$purchase->shipping_cost.'"', ' "'.$purchase->grand_total.'"', ' "'.$purchase->paid_amount.'"', ' "'.preg_replace('/\s+/S', " ", $purchase->note).'"', ' "'.$user->name.'"', ' "'.$user->email.'"', ' "'.$purchase->document.'"', ' "'.$currency_code.'"', ' "'.$purchase->exchange_rate.'"]'
                );
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            if(Auth::user()->role_id > 2) {
                $lims_warehouse_list = Warehouse::where([
                    ['is_active', true],
                    ['id', Auth::user()->warehouse_id]
                ])->get();
            }
            else {
                $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            }
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_list_without_variant = $this->productWithoutVariant();
            $lims_product_list_with_variant = $this->productWithVariant();
            $currency_list = Currency::where('is_active', true)->get();
            $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
            return view('backend.purchase.create', compact('lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'currency_list', 'custom_fields'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function productWithoutVariant()
    {
        return Product::ActiveStandard()->select('id', 'name', 'code')
                ->whereNull('is_variant')->get();
    }

    public function productWithVariant()
    {
        return Product::join('product_variants', 'products.id', 'product_variants.product_id')
            ->ActiveStandard()
            ->whereNotNull('is_variant')
            ->select('products.id', 'products.name', 'product_variants.item_code')
            ->orderBy('position')
            ->get();
    }

    public function newProductWithVariant()
    {
        return Product::ActiveStandard()
                ->whereNotNull('is_variant')
                ->whereNotNull('variant_data')
                ->select('id', 'name', 'variant_data')
                ->get();
    }

    public function limsProductSearch(Request $request)
    {
        $product_code = explode("|", $request['data']);
        $product_code[0] = rtrim($product_code[0], " ");
        $lims_product_data = Product::where([
                                ['code', $product_code[0]],
                                ['is_active', true]
                            ])
                            ->whereNull('is_variant')
                            ->first();
        if(!$lims_product_data) {
            $lims_product_data = Product::where([
                                ['name', $product_code[1]],
                                ['is_active', true]
                            ])
                            ->whereNotNull(['is_variant'])
                            ->first();
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->where([
                    ['product_variants.item_code', $product_code[0]],
                    ['products.is_active', true]
                ])
                ->whereNotNull('is_variant')
                ->select('products.*', 'product_variants.item_code', 'product_variants.additional_cost')
                ->first();
            $lims_product_data->cost += $lims_product_data->additional_cost;
        }
        $product[] = $lims_product_data->name;
        if($lims_product_data->is_variant)
            $product[] = $lims_product_data->item_code;
        else
            $product[] = $lims_product_data->code;
        $product[] = $lims_product_data->cost;

        if ($lims_product_data->tax_id) {
            $lims_tax_data = Tax::find($lims_product_data->tax_id);
            $product[] = $lims_tax_data->rate;
            $product[] = $lims_tax_data->name;
        } else {
            $product[] = 0;
            $product[] = 'No Tax';
        }
        $product[] = $lims_product_data->tax_method;

        $units = Unit::where("base_unit", $lims_product_data->unit_id)
                    ->orWhere('id', $lims_product_data->unit_id)
                    ->get();
        $unit_name = array();
        $unit_operator = array();
        $unit_operation_value = array();
        foreach ($units as $unit) {
            if ($lims_product_data->purchase_unit_id == $unit->id) {
                array_unshift($unit_name, $unit->unit_name);
                array_unshift($unit_operator, $unit->operator);
                array_unshift($unit_operation_value, $unit->operation_value);
            } else {
                $unit_name[]  = $unit->unit_name;
                $unit_operator[] = $unit->operator;
                $unit_operation_value[] = $unit->operation_value;
            }
        }

        $product[] = implode(",", $unit_name) . ',';
        $product[] = implode(",", $unit_operator) . ',';
        $product[] = implode(",", $unit_operation_value) . ',';
        $product[] = $lims_product_data->id;
        $product[] = $lims_product_data->is_batch;
        $product[] = $lims_product_data->is_imei;
        return $product;
    }

    public function store(Request $request)
    {
        $data = $request->except('document');
        //return dd($data);
        $data['user_id'] = Auth::id();

        if(!isset($data['reference_no']))
        {
            $data['reference_no'] = 'pr-' . date("Ymd") . '-'. date("his");
        }

        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            $data['document'] = $documentName;
        }

        if(isset($data['created_at'])) {
            $data['created_at'] = str_replace("/","-",$data['created_at']);
            $data['created_at'] = date("Y-m-d H:i:s", strtotime($data['created_at']));
        }
        else
            $data['created_at'] = date("Y-m-d H:i:s");
        //return dd($data);
        $lims_purchase_data = Purchase::create($data);
        //inserting data for custom fields
        $custom_field_data = [];
        $custom_fields = CustomField::where('belongs_to', 'purchase')->select('name', 'type')->get();
        foreach ($custom_fields as $type => $custom_field) {
            $field_name = str_replace(' ', '_', strtolower($custom_field->name));
            if(isset($data[$field_name])) {
                if($custom_field->type == 'checkbox' || $custom_field->type == 'multi_select')
                    $custom_field_data[$field_name] = implode(",", $data[$field_name]);
                else
                    $custom_field_data[$field_name] = $data[$field_name];
            }
        }
        if(count($custom_field_data))
            DB::table('purchases')->where('id', $lims_purchase_data->id)->update($custom_field_data);
        $product_id = $data['product_id'];
        $product_code = $data['product_code'];
        $qty = $data['qty'];
        $recieved = $data['recieved'];
        $batch_no = $data['batch_no'];
        $expired_date = $data['expired_date'];
        $purchase_unit = $data['purchase_unit'];
        $net_unit_cost = $data['net_unit_cost'];
        $discount = $data['discount'];
        $tax_rate = $data['tax_rate'];
        $tax = $data['tax'];
        $total = $data['subtotal'];
        $imei_numbers = $data['imei_number'];
        $product_purchase = [];

        foreach ($product_id as $i => $id) {
            $lims_purchase_unit_data  = Unit::where('unit_name', $purchase_unit[$i])->first();

            if ($lims_purchase_unit_data->operator == '*') {
                $quantity = $recieved[$i] * $lims_purchase_unit_data->operation_value;
            } else {
                $quantity = $recieved[$i] / $lims_purchase_unit_data->operation_value;
            }
            $lims_product_data = Product::find($id);
            $price = $lims_product_data->price;
            //dealing with product barch
            if($batch_no[$i]) {
                $product_batch_data = ProductBatch::where([
                                        ['product_id', $lims_product_data->id],
                                        ['batch_no', $batch_no[$i]]
                                    ])->first();
                if($product_batch_data) {
                    $product_batch_data->expired_date = $expired_date[$i];
                    $product_batch_data->qty += $quantity;
                    $product_batch_data->save();
                }
                else {
                    $product_batch_data = ProductBatch::create([
                                            'product_id' => $lims_product_data->id,
                                            'batch_no' => $batch_no[$i],
                                            'expired_date' => $expired_date[$i],
                                            'qty' => $quantity
                                        ]);
                }
                $product_purchase['product_batch_id'] = $product_batch_data->id;
            }
            else
                $product_purchase['product_batch_id'] = null;

            if($lims_product_data->is_variant) {
                $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($lims_product_data->id, $product_code[$i])->first();
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $id],
                    ['variant_id', $lims_product_variant_data->variant_id],
                    ['warehouse_id', $data['warehouse_id']]
                ])->first();
                $product_purchase['variant_id'] = $lims_product_variant_data->variant_id;
                //add quantity to product variant table
                $lims_product_variant_data->qty += $quantity;
                $lims_product_variant_data->save();
            }
            else {
                $product_purchase['variant_id'] = null;
                if($product_purchase['product_batch_id']) {
                    //checking for price
                    $lims_product_warehouse_data = Product_Warehouse::where([
                                                    ['product_id', $id],
                                                    ['warehouse_id', $data['warehouse_id'] ],
                                                ])
                                                ->whereNotNull('price')
                                                ->select('price')
                                                ->first();
                    if($lims_product_warehouse_data)
                        $price = $lims_product_warehouse_data->price;
                    else
                        $price = null;
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['product_batch_id', $product_purchase['product_batch_id'] ],
                        ['warehouse_id', $data['warehouse_id'] ],
                    ])->first();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['warehouse_id', $data['warehouse_id'] ],
                    ])->first();
                }
            }
            //add quantity to product table
            $lims_product_data->qty = $lims_product_data->qty + $quantity;
            $lims_product_data->save();
            //add quantity to warehouse
            if ($lims_product_warehouse_data) {
                $lims_product_warehouse_data->qty = $lims_product_warehouse_data->qty + $quantity;
                $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
            }
            else {
                $lims_product_warehouse_data = new Product_Warehouse();
                $lims_product_warehouse_data->product_id = $id;
                $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
                $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                $lims_product_warehouse_data->qty = $quantity;
                if($price)
                    $lims_product_warehouse_data->price = $price;
                if($lims_product_data->is_variant)
                    $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
            }
            //added imei numbers to product_warehouse table
            if($imei_numbers[$i]) {
                if($lims_product_warehouse_data->imei_number)
                    $lims_product_warehouse_data->imei_number .= ',' . $imei_numbers[$i];
                else
                    $lims_product_warehouse_data->imei_number = $imei_numbers[$i];
            }
            $lims_product_warehouse_data->save();

            $product_purchase['purchase_id'] = $lims_purchase_data->id ;
            $product_purchase['product_id'] = $id;
            $product_purchase['imei_number'] = $imei_numbers[$i];
            $product_purchase['qty'] = $qty[$i];
            $product_purchase['recieved'] = $recieved[$i];
            $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
            $product_purchase['net_unit_cost'] = $net_unit_cost[$i];
            $product_purchase['discount'] = $discount[$i];
            $product_purchase['tax_rate'] = $tax_rate[$i];
            $product_purchase['tax'] = $tax[$i];
            $product_purchase['total'] = $total[$i];
            ProductPurchase::create($product_purchase);
        }

        return redirect('purchases')->with('message', 'Purchase created successfully');
    }

    public function productPurchaseData($id)
    {
        try {
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $product_purchase = [];
            foreach ($lims_product_purchase_data as $key => $product_purchase_data) {
                $product = Product::find($product_purchase_data->product_id);
                $unit = Unit::find($product_purchase_data->purchase_unit_id);
                if($product_purchase_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::FindExactProduct($product->id, $product_purchase_data->variant_id)->select('item_code')->first();
                    $product->code = $lims_product_variant_data->item_code;
                }
                if($product_purchase_data->product_batch_id) {
                    $product_batch_data = ProductBatch::select('batch_no')->find($product_purchase_data->product_batch_id);
                    $product_purchase[7][$key] = $product_batch_data->batch_no;
                }
                else
                    $product_purchase[7][$key] = 'N/A';
                $product_purchase[0][$key] = $product->name . ' [' . $product->code.']';
                $returned_imei_number_data = '';
                if($product_purchase_data->imei_number) {
                    $product_purchase[0][$key] .= '<br>IMEI or Serial Number: '. $product_purchase_data->imei_number;
                    $returned_imei_number_data = DB::table('return_purchases')
                    ->join('purchase_product_return', 'return_purchases.id', '=', 'purchase_product_return.return_id')
                    ->where([
                        ['return_purchases.purchase_id', $id],
                        ['purchase_product_return.product_id', $product_purchase_data->product_id]
                    ])->select('purchase_product_return.imei_number')
                    ->first();
                }
                $product_purchase[1][$key] = $product_purchase_data->qty;
                $product_purchase[2][$key] = $unit->unit_code;
                $product_purchase[3][$key] = $product_purchase_data->tax;
                $product_purchase[4][$key] = $product_purchase_data->tax_rate;
                $product_purchase[5][$key] = $product_purchase_data->discount;
                $product_purchase[6][$key] = $product_purchase_data->total;
                if($returned_imei_number_data) {
                    $product_purchase[8][$key] = $product_purchase_data->return_qty.'<br>IMEI or Serial Number: '. $returned_imei_number_data->imei_number;
                }
                else
                    $product_purchase[8][$key] = $product_purchase_data->return_qty;
            }
            return $product_purchase;
        }
        catch (Exception $e) {
            /*return response()->json('errors' => [$e->getMessage());*/
            //return response()->json(['errors' => [$e->getMessage()]], 422);
            return 'Something is wrong!';
        }

    }

    public function purchaseByCsv()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();

            return view('backend.purchase.import', compact('lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function importPurchase(Request $request)
    {
        //get the file
        $upload=$request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        //checking if this is a CSV file
        if($ext != 'csv')
            return redirect()->back()->with('message', 'Please upload a CSV file');

        $filePath=$upload->getRealPath();
        $file_handle = fopen($filePath, 'r');
        $i = 0;
        //validate the file
        while (!feof($file_handle) ) {
            $current_line = fgetcsv($file_handle);
            if($current_line && $i > 0){
                $product_data[] = Product::where([
                                    ['code', $current_line[0]],
                                    ['is_active', true]
                                ])->first();
                if(!$product_data[$i-1])
                    return redirect()->back()->with('message', 'Product with this code '.$current_line[0].' does not exist!');
                $unit[] = Unit::where('unit_code', $current_line[2])->first();
                if(!$unit[$i-1])
                    return redirect()->back()->with('message', 'Purchase unit does not exist!');
                if(strtolower($current_line[5]) != "no tax"){
                    $tax[] = Tax::where('name', $current_line[5])->first();
                    if(!$tax[$i-1])
                        return redirect()->back()->with('message', 'Tax name does not exist!');
                }
                else
                    $tax[$i-1]['rate'] = 0;

                $qty[] = $current_line[1];
                $cost[] = $current_line[3];
                $discount[] = $current_line[4];
            }
            $i++;
        }

        $data = $request->except('file');
        $data['reference_no'] = 'pr-' . date("Ymd") . '-'. date("his");
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            $data['document'] = $documentName;
        }
        $item = 0;
        $grand_total = $data['shipping_cost'];
        $data['user_id'] = Auth::id();
        Purchase::create($data);
        $lims_purchase_data = Purchase::latest()->first();

        foreach ($product_data as $key => $product) {
            if($product['tax_method'] == 1){
                $net_unit_cost = $cost[$key] - $discount[$key];
                $product_tax = $net_unit_cost * ($tax[$key]['rate'] / 100) * $qty[$key];
                $total = ($net_unit_cost * $qty[$key]) + $product_tax;
            }
            elseif($product['tax_method'] == 2){
                $net_unit_cost = (100 / (100 + $tax[$key]['rate'])) * ($cost[$key] - $discount[$key]);
                $product_tax = ($cost[$key] - $discount[$key] - $net_unit_cost) * $qty[$key];
                $total = ($cost[$key] - $discount[$key]) * $qty[$key];
            }
            if($data['status'] == 1){
                if($unit[$key]['operator'] == '*')
                    $quantity = $qty[$key] * $unit[$key]['operation_value'];
                elseif($unit[$key]['operator'] == '/')
                    $quantity = $qty[$key] / $unit[$key]['operation_value'];
                $product['qty'] += $quantity;
                $product_warehouse = Product_Warehouse::where([
                    ['product_id', $product['id']],
                    ['warehouse_id', $data['warehouse_id']]
                ])->first();
                if($product_warehouse) {
                    $product_warehouse->qty += $quantity;
                    $product_warehouse->save();
                }
                else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $product['id'];
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = $quantity;
                    $lims_product_warehouse_data->save();
                }
                $product->save();
            }

            $product_purchase = new ProductPurchase();
            $product_purchase->purchase_id = $lims_purchase_data->id;
            $product_purchase->product_id = $product['id'];
            $product_purchase->qty = $qty[$key];
            if($data['status'] == 1)
                $product_purchase->recieved = $qty[$key];
            else
                $product_purchase->recieved = 0;
            $product_purchase->purchase_unit_id = $unit[$key]['id'];
            $product_purchase->net_unit_cost = number_format((float)$net_unit_cost, config('decimal'), '.', '');
            $product_purchase->discount = $discount[$key] * $qty[$key];
            $product_purchase->tax_rate = $tax[$key]['rate'];
            $product_purchase->tax = number_format((float)$product_tax, config('decimal'), '.', '');
            $product_purchase->total = number_format((float)$total, config('decimal'), '.', '');
            $product_purchase->save();
            $lims_purchase_data->total_qty += $qty[$key];
            $lims_purchase_data->total_discount += $discount[$key] * $qty[$key];
            $lims_purchase_data->total_tax += number_format((float)$product_tax, config('decimal'), '.', '');
            $lims_purchase_data->total_cost += number_format((float)$total, config('decimal'), '.', '');
        }
        $lims_purchase_data->item = $key + 1;
        $lims_purchase_data->order_tax = ($lims_purchase_data->total_cost - $lims_purchase_data->order_discount) * ($data['order_tax_rate'] / 100);
        $lims_purchase_data->grand_total = ($lims_purchase_data->total_cost + $lims_purchase_data->order_tax + $lims_purchase_data->shipping_cost) - $lims_purchase_data->order_discount;
        $lims_purchase_data->save();
        return redirect('purchases');
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-edit')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_list_without_variant = $this->productWithoutVariant();
            $lims_product_list_with_variant = $this->productWithVariant();
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            if($lims_purchase_data->exchange_rate)
                $currency_exchange_rate = $lims_purchase_data->exchange_rate;
            else
                $currency_exchange_rate = 1;
            $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
            return view('backend.purchase.edit', compact('lims_warehouse_list', 'lims_supplier_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_tax_list', 'lims_purchase_data', 'lims_product_purchase_data', 'currency_exchange_rate', 'custom_fields'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');

    }

    public function duplicate($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_list_without_variant = $this->productWithoutVariant();
            $lims_product_list_with_variant = $this->productWithVariant();
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            if($lims_purchase_data->exchange_rate)
                $currency_exchange_rate = $lims_purchase_data->exchange_rate;
            else
                $currency_exchange_rate = 1;
            $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
            return view('backend.purchase.duplicate', compact('lims_warehouse_list', 'lims_supplier_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_tax_list', 'lims_purchase_data', 'lims_product_purchase_data', 'currency_exchange_rate', 'custom_fields'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');

    }

    public function update(Request $request, $id)
    {
        $lims_purchase_data = Purchase::find($id);
        $data = $request->except('document');
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $this->fileDelete(public_path('documents/purchase/'), $lims_purchase_data->document);

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            $data['document'] = $documentName;
        }
        //return dd($data);
        DB::beginTransaction();
        try {
            $balance = $data['grand_total'] - $data['paid_amount'];
            if ($balance < 0 || $balance > 0) {
                $data['payment_status'] = 1;
            } else {
                $data['payment_status'] = 2;
            }
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();

            $data['created_at'] = date("Y-m-d", strtotime(str_replace("/", "-", $data['created_at']))) . ' '. date("H:i:s");
            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $recieved = $data['recieved'];
            $batch_no = $data['batch_no'];
            $expired_date = $data['expired_date'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $imei_number = $new_imei_number = $data['imei_number'];
            $product_purchase = [];

            foreach ($lims_product_purchase_data as $product_purchase_data) {

                $old_recieved_value = $product_purchase_data->recieved;
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);

                if ($lims_purchase_unit_data->operator == '*') {
                    $old_recieved_value = $old_recieved_value * $lims_purchase_unit_data->operation_value;
                } else {
                    $old_recieved_value = $old_recieved_value / $lims_purchase_unit_data->operation_value;
                }
                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $lims_product_data->id],
                        ['variant_id', $product_purchase_data->variant_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id]
                    ])->first();
                    $lims_product_variant_data->qty -= $old_recieved_value;
                    $lims_product_variant_data->save();
                }
                elseif($product_purchase_data->product_batch_id) {
                    $product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $product_batch_data->qty -= $old_recieved_value;
                    $product_batch_data->save();

                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                if($product_purchase_data->imei_number) {
                    $position = array_search($lims_product_data->id, $product_id);
                    if($imei_number[$position]) {
                        $prev_imei_numbers = explode(",", $product_purchase_data->imei_number);
                        $new_imei_numbers = explode(",", $imei_number[$position]);
                        foreach ($prev_imei_numbers as $prev_imei_number) {
                            if(($pos = array_search($prev_imei_number, $new_imei_numbers)) !== false) {
                                unset($new_imei_numbers[$pos]);
                            }
                        }
                        $new_imei_number[$position] = implode(",", $new_imei_numbers);
                    }
                }
                $lims_product_data->qty -= $old_recieved_value;
                if($lims_product_warehouse_data) {
                    $lims_product_warehouse_data->qty -= $old_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                $lims_product_data->save();
                $product_purchase_data->delete();
            }

            foreach ($product_id as $key => $pro_id) {
                $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$key])->first();
                if ($lims_purchase_unit_data->operator == '*') {
                    $new_recieved_value = $recieved[$key] * $lims_purchase_unit_data->operation_value;
                } else {
                    $new_recieved_value = $recieved[$key] / $lims_purchase_unit_data->operation_value;
                }

                $lims_product_data = Product::find($pro_id);
                $price = null;
                //dealing with product barch
                if($batch_no[$key]) {
                    $product_batch_data = ProductBatch::where([
                                            ['product_id', $lims_product_data->id],
                                            ['batch_no', $batch_no[$key]]
                                        ])->first();
                    if($product_batch_data) {
                        $product_batch_data->qty += $new_recieved_value;
                        $product_batch_data->expired_date = $expired_date[$key];
                        $product_batch_data->save();
                    }
                    else {
                        $product_batch_data = ProductBatch::create([
                                                'product_id' => $lims_product_data->id,
                                                'batch_no' => $batch_no[$key],
                                                'expired_date' => $expired_date[$key],
                                                'qty' => $new_recieved_value
                                            ]);
                    }
                    $product_purchase['product_batch_id'] = $product_batch_data->id;
                }
                else
                    $product_purchase['product_batch_id'] = null;

                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($pro_id, $product_code[$key])->first();
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $pro_id],
                        ['variant_id', $lims_product_variant_data->variant_id],
                        ['warehouse_id', $data['warehouse_id']]
                    ])->first();
                    $product_purchase['variant_id'] = $lims_product_variant_data->variant_id;
                    //add quantity to product variant table
                    $lims_product_variant_data->qty += $new_recieved_value;
                    $lims_product_variant_data->save();
                }
                else {
                    $product_purchase['variant_id'] = null;
                    if($product_purchase['product_batch_id']) {
                        //checking for price
                        $lims_product_warehouse_data = Product_Warehouse::where([
                                                        ['product_id', $pro_id],
                                                        ['warehouse_id', $data['warehouse_id'] ],
                                                    ])
                                                    ->whereNotNull('price')
                                                    ->select('price')
                                                    ->first();
                        if($lims_product_warehouse_data)
                            $price = $lims_product_warehouse_data->price;

                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['product_batch_id', $product_purchase['product_batch_id'] ],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                    else {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                }

                $lims_product_data->qty += $new_recieved_value;
                if($lims_product_warehouse_data){
                    $lims_product_warehouse_data->qty += $new_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $pro_id;
                    $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
                    if($lims_product_data->is_variant)
                        $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = $new_recieved_value;
                    if($price)
                        $lims_product_warehouse_data->price = $price;
                }
                //dealing with imei numbers
                if($imei_number[$key]) {
                    if($lims_product_warehouse_data->imei_number) {
                        $lims_product_warehouse_data->imei_number .= ',' . $new_imei_number[$key];
                    }
                    else {
                        $lims_product_warehouse_data->imei_number = $new_imei_number[$key];
                    }
                }

                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                $product_purchase['purchase_id'] = $id ;
                $product_purchase['product_id'] = $pro_id;
                $product_purchase['qty'] = $qty[$key];
                $product_purchase['recieved'] = $recieved[$key];
                $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
                $product_purchase['net_unit_cost'] = $net_unit_cost[$key];
                $product_purchase['discount'] = $discount[$key];
                $product_purchase['tax_rate'] = $tax_rate[$key];
                $product_purchase['tax'] = $tax[$key];
                $product_purchase['total'] = $total[$key];
                $product_purchase['imei_number'] = $imei_number[$key];
                ProductPurchase::create($product_purchase);
            }
            DB::commit();
        }
        catch(Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
        $lims_purchase_data->update($data);
        //inserting data for custom fields
        $custom_field_data = [];
        $custom_fields = CustomField::where('belongs_to', 'purchase')->select('name', 'type')->get();
        foreach ($custom_fields as $type => $custom_field) {
            $field_name = str_replace(' ', '_', strtolower($custom_field->name));
            if(isset($data[$field_name])) {
                if($custom_field->type == 'checkbox' || $custom_field->type == 'multi_select')
                    $custom_field_data[$field_name] = implode(",", $data[$field_name]);
                else
                    $custom_field_data[$field_name] = $data[$field_name];
            }
        }
        if(count($custom_field_data))
            DB::table('purchases')->where('id', $lims_purchase_data->id)->update($custom_field_data);
        return redirect('purchases')->with('message', 'Purchase updated successfully');
    }

    public function addPayment(Request $request)
    {
        $data = $request->all();
        $lims_purchase_data = Purchase::find($data['purchase_id']);
        $lims_purchase_data->paid_amount += $data['amount'];
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();

        if($data['paid_by_id'] == 1)
            $paying_method = 'Cash';
        elseif ($data['paid_by_id'] == 2)
            $paying_method = 'Gift Card';
        elseif ($data['paid_by_id'] == 3)
            $paying_method = 'Credit Card';
        else
            $paying_method = 'Cheque';

        $lims_payment_data = new Payment();
        $lims_payment_data->user_id = Auth::id();
        $lims_payment_data->purchase_id = $lims_purchase_data->id;
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->payment_reference = 'ppr-' . date("Ymd") . '-'. date("his");
        $lims_payment_data->amount = $data['amount'];
        $lims_payment_data->change = $data['paying_amount'] - $data['amount'];
        $lims_payment_data->paying_method = $paying_method;
        $lims_payment_data->payment_note = $data['payment_note'];
        $lims_payment_data->save();

        $lims_payment_data = Payment::latest()->first();
        $data['payment_id'] = $lims_payment_data->id;
        $lims_pos_setting_data = PosSetting::latest()->first();
        if($paying_method == 'Credit Card' && $lims_pos_setting_data->stripe_secret_key){
            Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            $token = $data['stripeToken'];
            $amount = $data['amount'];

            // Charge the Customer
            $charge = \Stripe\Charge::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'source' => $token,
            ]);

            $data['charge_id'] = $charge->id;
            PaymentWithCreditCard::create($data);
        }
        elseif ($paying_method == 'Cheque') {
            PaymentWithCheque::create($data);
        }
        return redirect('purchases')->with('message', 'Payment created successfully');
    }

    public function getPayment($id)
    {
        $lims_payment_list = Payment::where('purchase_id', $id)->get();
        $date = [];
        $payment_reference = [];
        $paid_amount = [];
        $paying_method = [];
        $payment_id = [];
        $payment_note = [];
        $cheque_no = [];
        $change = [];
        $paying_amount = [];
        $account_name = [];
        $account_id = [];
        foreach ($lims_payment_list as $payment) {
            $date[] = date(config('date_format'), strtotime($payment->created_at->toDateString())) . ' '. $payment->created_at->toTimeString();
            $payment_reference[] = $payment->payment_reference;
            $paid_amount[] = $payment->amount;
            $change[] = $payment->change;
            $paying_method[] = $payment->paying_method;
            $paying_amount[] = $payment->amount + $payment->change;
            if($payment->paying_method == 'Cheque'){
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id',$payment->id)->first();
                $cheque_no[] = $lims_payment_cheque_data->cheque_no;
            }
            else{
                $cheque_no[] = null;
            }
            $payment_id[] = $payment->id;
            $payment_note[] = $payment->payment_note;
            $lims_account_data = Account::find($payment->account_id);
            if($lims_account_data) {
                $account_name[] = $lims_account_data->name;
                $account_id[] = $lims_account_data->id;
            }
            else {
                $account_name[] = 'N/A';
                $account_id[] = 0;
            }
        }
        $payments[] = $date;
        $payments[] = $payment_reference;
        $payments[] = $paid_amount;
        $payments[] = $paying_method;
        $payments[] = $payment_id;
        $payments[] = $payment_note;
        $payments[] = $cheque_no;
        $payments[] = $change;
        $payments[] = $paying_amount;
        $payments[] = $account_name;
        $payments[] = $account_id;

        return $payments;
    }

    public function updatePayment(Request $request)
    {
        $data = $request->all();
        $lims_payment_data = Payment::find($data['payment_id']);
        $lims_purchase_data = Purchase::find($lims_payment_data->purchase_id);
        //updating purchase table
        $amount_dif = $lims_payment_data->amount - $data['edit_amount'];
        $lims_purchase_data->paid_amount = $lims_purchase_data->paid_amount - $amount_dif;
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();

        //updating payment data
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->amount = $data['edit_amount'];
        $lims_payment_data->change = $data['edit_paying_amount'] - $data['edit_amount'];
        $lims_payment_data->payment_note = $data['edit_payment_note'];
        $lims_pos_setting_data = PosSetting::latest()->first();
        if($data['edit_paid_by_id'] == 1)
            $lims_payment_data->paying_method = 'Cash';
        elseif ($data['edit_paid_by_id'] == 2)
            $lims_payment_data->paying_method = 'Gift Card';
        elseif ($data['edit_paid_by_id'] == 3 && $lims_pos_setting_data->stripe_secret_key) {
            \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            $token = $data['stripeToken'];
            $amount = $data['edit_amount'];
            if($lims_payment_data->paying_method == 'Credit Card'){
                $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $lims_payment_data->id)->first();

                \Stripe\Refund::create(array(
                  "charge" => $lims_payment_with_credit_card_data->charge_id,
                ));

                $charge = \Stripe\Charge::create([
                    'amount' => $amount * 100,
                    'currency' => 'usd',
                    'source' => $token,
                ]);

                $lims_payment_with_credit_card_data->charge_id = $charge->id;
                $lims_payment_with_credit_card_data->save();
            }
            elseif($lims_pos_setting_data->stripe_secret_key) {
                // Charge the Customer
                $charge = \Stripe\Charge::create([
                    'amount' => $amount * 100,
                    'currency' => 'usd',
                    'source' => $token,
                ]);

                $data['charge_id'] = $charge->id;
                PaymentWithCreditCard::create($data);
            }
            $lims_payment_data->paying_method = 'Credit Card';
        }
        else{
            if($lims_payment_data->paying_method == 'Cheque'){
                $lims_payment_data->paying_method = 'Cheque';
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $data['payment_id'])->first();
                $lims_payment_cheque_data->cheque_no = $data['edit_cheque_no'];
                $lims_payment_cheque_data->save();
            }
            else{
                $lims_payment_data->paying_method = 'Cheque';
                $data['cheque_no'] = $data['edit_cheque_no'];
                PaymentWithCheque::create($data);
            }
        }
        $lims_payment_data->save();
        return redirect('purchases')->with('message', 'Payment updated successfully');
    }

    public function deletePayment(Request $request)
    {
        $lims_payment_data = Payment::find($request['id']);
        $lims_purchase_data = Purchase::where('id', $lims_payment_data->purchase_id)->first();
        $lims_purchase_data->paid_amount -= $lims_payment_data->amount;
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();
        $lims_pos_setting_data = PosSetting::latest()->first();

        if($lims_payment_data->paying_method == 'Credit Card' && $lims_pos_setting_data->stripe_secret_key) {
            $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $request['id'])->first();
            \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            \Stripe\Refund::create(array(
              "charge" => $lims_payment_with_credit_card_data->charge_id,
            ));

            $lims_payment_with_credit_card_data->delete();
        }
        elseif ($lims_payment_data->paying_method == 'Cheque') {
            $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $request['id'])->first();
            $lims_payment_cheque_data->delete();
        }
        $lims_payment_data->delete();
        return redirect('purchases')->with('not_permitted', 'Payment deleted successfully');
    }

    public function deleteBySelection(Request $request)
    {
        $purchase_id = $request['purchaseIdArray'];
        foreach ($purchase_id as $id) {
            $lims_purchase_data = Purchase::find($id);
            $this->fileDelete(public_path('documents/purchase/'), $lims_purchase_data->document);

            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $lims_payment_data = Payment::where('purchase_id', $id)->get();
            foreach ($lims_product_purchase_data as $product_purchase_data) {
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);
                if ($lims_purchase_unit_data->operator == '*')
                    $recieved_qty = $product_purchase_data->recieved * $lims_purchase_unit_data->operation_value;
                else
                    $recieved_qty = $product_purchase_data->recieved / $lims_purchase_unit_data->operation_value;

                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($product_purchase_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_purchase_data->product_id, $product_purchase_data->variant_id, $lims_purchase_data->warehouse_id)
                        ->first();
                    $lims_product_variant_data->qty -= $recieved_qty;
                    $lims_product_variant_data->save();
                }
                elseif($product_purchase_data->product_batch_id) {
                    $lims_product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id]
                    ])->first();

                    $lims_product_batch_data->qty -= $recieved_qty;
                    $lims_product_batch_data->save();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_purchase_data->product_id, $lims_purchase_data->warehouse_id)
                        ->first();
                }

                $lims_product_data->qty -= $recieved_qty;
                $lims_product_warehouse_data->qty -= $recieved_qty;

                $lims_product_warehouse_data->save();
                $lims_product_data->save();
                $product_purchase_data->delete();
            }
            $lims_pos_setting_data = PosSetting::latest()->first();
            foreach ($lims_payment_data as $payment_data) {
                if($payment_data->paying_method == "Cheque"){
                    $payment_with_cheque_data = PaymentWithCheque::where('payment_id', $payment_data->id)->first();
                    $payment_with_cheque_data->delete();
                }
                elseif($payment_data->paying_method == "Credit Card" && $lims_pos_setting_data->stripe_secret_key) {
                    $payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment_data->id)->first();
                    \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
                    \Stripe\Refund::create(array(
                      "charge" => $payment_with_credit_card_data->charge_id,
                    ));

                    $payment_with_credit_card_data->delete();
                }
                $payment_data->delete();
            }

            $lims_purchase_data->delete();
        }
        return 'Purchase deleted successfully!';
    }

    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-delete')){
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $lims_payment_data = Payment::where('purchase_id', $id)->get();
            foreach ($lims_product_purchase_data as $product_purchase_data) {
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);
                if ($lims_purchase_unit_data->operator == '*')
                    $recieved_qty = $product_purchase_data->recieved * $lims_purchase_unit_data->operation_value;
                else
                    $recieved_qty = $product_purchase_data->recieved / $lims_purchase_unit_data->operation_value;

                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($product_purchase_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_purchase_data->product_id, $product_purchase_data->variant_id, $lims_purchase_data->warehouse_id)
                        ->first();
                    $lims_product_variant_data->qty -= $recieved_qty;
                    $lims_product_variant_data->save();
                }
                elseif($product_purchase_data->product_batch_id) {
                    $lims_product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id]
                    ])->first();

                    $lims_product_batch_data->qty -= $recieved_qty;
                    $lims_product_batch_data->save();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_purchase_data->product_id, $lims_purchase_data->warehouse_id)
                        ->first();
                }
                //deduct imei number if available
                if($product_purchase_data->imei_number) {
                    $imei_numbers = explode(",", $product_purchase_data->imei_number);
                    $all_imei_numbers = explode(",", $lims_product_warehouse_data->imei_number);
                    foreach ($imei_numbers as $number) {
                        if (($j = array_search($number, $all_imei_numbers)) !== false) {
                            unset($all_imei_numbers[$j]);
                        }
                    }
                    $lims_product_warehouse_data->imei_number = implode(",", $all_imei_numbers);
                }

                $lims_product_data->qty -= $recieved_qty;
                $lims_product_warehouse_data->qty -= $recieved_qty;

                $lims_product_warehouse_data->save();
                $lims_product_data->save();
                $product_purchase_data->delete();
            }
            $lims_pos_setting_data = PosSetting::latest()->first();
            foreach ($lims_payment_data as $payment_data) {
                if($payment_data->paying_method == "Cheque"){
                    $payment_with_cheque_data = PaymentWithCheque::where('payment_id', $payment_data->id)->first();
                    $payment_with_cheque_data->delete();
                }
                elseif($payment_data->paying_method == "Credit Card" && $lims_pos_setting_data->stripe_secret_key) {
                    $payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment_data->id)->first();
                    \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
                    \Stripe\Refund::create(array(
                      "charge" => $payment_with_credit_card_data->charge_id,
                    ));

                    $payment_with_credit_card_data->delete();
                }
                $payment_data->delete();
            }

            $lims_purchase_data->delete();
            $this->fileDelete(public_path('documents/purchase/'), $lims_purchase_data->document);

            return redirect('purchases')->with('not_permitted', 'Purchase deleted successfully');;
        }

    }

    public function updateFromClient(Request $request, $id)
    {
        $data = $request->except('document');
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            $data['document'] = $documentName;
        }
        //return dd($data);
        DB::beginTransaction();
        try {
            $balance = $data['grand_total'] - $data['paid_amount'];
            if ($balance < 0 || $balance > 0) {
                $data['payment_status'] = 1;
            } else {
                $data['payment_status'] = 2;
            }
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();

            $data['created_at'] = date("Y-m-d", strtotime(str_replace("/", "-", $data['created_at'])));
            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $recieved = $data['recieved'];
            $batch_no = $data['batch_no'];
            $expired_date = $data['expired_date'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $imei_number = $new_imei_number = $data['imei_number'];
            $product_purchase = [];
            $lims_product_warehouse_data = null;

            foreach ($lims_product_purchase_data as $product_purchase_data) {

                $old_recieved_value = $product_purchase_data->recieved;
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);

                if ($lims_purchase_unit_data->operator == '*') {
                    $old_recieved_value = $old_recieved_value * $lims_purchase_unit_data->operation_value;
                } else {
                    $old_recieved_value = $old_recieved_value / $lims_purchase_unit_data->operation_value;
                }
                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    if($lims_product_variant_data) {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $lims_product_data->id],
                            ['variant_id', $product_purchase_data->variant_id],
                            ['warehouse_id', $lims_purchase_data->warehouse_id]
                        ])->first();
                        $lims_product_variant_data->qty -= $old_recieved_value;
                        $lims_product_variant_data->save();
                    }
                }
                elseif($product_purchase_data->product_batch_id) {
                    $product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $product_batch_data->qty -= $old_recieved_value;
                    $product_batch_data->save();

                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                if($product_purchase_data->imei_number) {
                    $position = array_search($lims_product_data->id, $product_id);
                    if($imei_number[$position]) {
                        $prev_imei_numbers = explode(",", $product_purchase_data->imei_number);
                        $new_imei_numbers = explode(",", $imei_number[$position]);
                        foreach ($prev_imei_numbers as $prev_imei_number) {
                            if(($pos = array_search($prev_imei_number, $new_imei_numbers)) !== false) {
                                unset($new_imei_numbers[$pos]);
                            }
                        }
                        $new_imei_number[$position] = implode(",", $new_imei_numbers);
                    }
                }
                $lims_product_data->qty -= $old_recieved_value;
                if($lims_product_warehouse_data) {
                    $lims_product_warehouse_data->qty -= $old_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                $lims_product_data->save();
                $product_purchase_data->delete();
            }

            foreach ($product_id as $key => $pro_id) {
                $price = null;
                $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$key])->first();
                if ($lims_purchase_unit_data->operator == '*') {
                    $new_recieved_value = $recieved[$key] * $lims_purchase_unit_data->operation_value;
                } else {
                    $new_recieved_value = $recieved[$key] / $lims_purchase_unit_data->operation_value;
                }

                $lims_product_data = Product::find($pro_id);
                //dealing with product barch
                if($batch_no[$key]) {
                    $product_batch_data = ProductBatch::where([
                                            ['product_id', $lims_product_data->id],
                                            ['batch_no', $batch_no[$key]]
                                        ])->first();
                    if($product_batch_data) {
                        $product_batch_data->qty += $new_recieved_value;
                        $product_batch_data->expired_date = $expired_date[$key];
                        $product_batch_data->save();
                    }
                    else {
                        $product_batch_data = ProductBatch::create([
                                                'product_id' => $lims_product_data->id,
                                                'batch_no' => $batch_no[$key],
                                                'expired_date' => $expired_date[$key],
                                                'qty' => $new_recieved_value
                                            ]);
                    }
                    $product_purchase['product_batch_id'] = $product_batch_data->id;
                }
                else
                    $product_purchase['product_batch_id'] = null;

                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($pro_id, $product_code[$key])->first();
                    if($lims_product_variant_data) {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['variant_id', $lims_product_variant_data->variant_id],
                            ['warehouse_id', $data['warehouse_id']]
                        ])->first();
                        $product_purchase['variant_id'] = $lims_product_variant_data->variant_id;
                        //add quantity to product variant table
                        $lims_product_variant_data->qty += $new_recieved_value;
                        $lims_product_variant_data->save();
                    }
                }
                else {
                    $product_purchase['variant_id'] = null;
                    if($product_purchase['product_batch_id']) {
                        //checking for price
                        $lims_product_warehouse_data = Product_Warehouse::where([
                                                        ['product_id', $pro_id],
                                                        ['warehouse_id', $data['warehouse_id'] ],
                                                    ])
                                                    ->whereNotNull('price')
                                                    ->select('price')
                                                    ->first();
                        if($lims_product_warehouse_data)
                            $price = $lims_product_warehouse_data->price;

                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['product_batch_id', $product_purchase['product_batch_id'] ],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                    else {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                }

                $lims_product_data->qty += $new_recieved_value;
                if($lims_product_warehouse_data){
                    $lims_product_warehouse_data->qty += $new_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $pro_id;
                    $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
                    if($lims_product_data->is_variant && $lims_product_variant_data)
                        $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = $new_recieved_value;
                    if($price)
                        $lims_product_warehouse_data->price = $price;
                }
                //dealing with imei numbers
                if($imei_number[$key]) {
                    if($lims_product_warehouse_data->imei_number) {
                        $lims_product_warehouse_data->imei_number .= ',' . $new_imei_number[$key];
                    }
                    else {
                        $lims_product_warehouse_data->imei_number = $new_imei_number[$key];
                    }
                }

                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                $product_purchase['purchase_id'] = $id ;
                $product_purchase['product_id'] = $pro_id;
                $product_purchase['qty'] = $qty[$key];
                $product_purchase['recieved'] = $recieved[$key];
                $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
                $product_purchase['net_unit_cost'] = $net_unit_cost[$key];
                $product_purchase['discount'] = $discount[$key];
                $product_purchase['tax_rate'] = $tax_rate[$key];
                $product_purchase['tax'] = $tax[$key];
                $product_purchase['total'] = $total[$key];
                $product_purchase['imei_number'] = $imei_number[$key];
                ProductPurchase::create($product_purchase);
            }
            DB::commit();
        }
        catch(Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
        $lims_purchase_data->update($data);
        return redirect('purchases')->with('message', 'Purchase updated successfully');
    }
}
