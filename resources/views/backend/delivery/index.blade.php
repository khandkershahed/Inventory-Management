@extends('backend.layout.main') @section('content')
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="table-responsive">
        <table id="delivery-table" class="table">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{trans('file.Delivery Reference')}}</th>
                    <th>{{trans('file.Sale Reference')}}</th>
                    <th>{{trans('file.Packing Slip Reference')}}</th>
                    <th>{{trans('file.customer')}}</th>
                    <th>{{trans('file.Courier')}}</th>
                    <th>{{trans('file.Address')}}</th>
                    <th>{{trans('file.Products')}}</th>
                    <th>{{trans('file.grand total')}}</th>
                    <th>{{trans('file.Status')}}</th>
                    <th class="not-exported">{{trans('file.action')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lims_delivery_all as $key=>$delivery)
                <?php
                    $customer_sale = DB::table('sales')
                                    ->join('customers', 'sales.customer_id', '=', 'customers.id')
                                    ->where('sales.id', $delivery->sale_id)
                                    ->select('sales.reference_no','customers.name', 'customers.phone_number', 'customers.city', 'sales.grand_total')
                                    ->get();

                    $product_names = DB::table('sales')
                                        ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                                        ->join('products', 'products.id', '=', 'product_sales.product_id')
                                        ->where('sales.id', $delivery->sale_id)
                                        ->pluck('products.name')
                                        ->toArray();
                    if($delivery->packing_slip_ids)
                        $packing_slip_references = \App\Models\PackingSlip::whereIn('id', explode(",", $delivery->packing_slip_ids))->pluck('reference_no')->toArray();
                    else
                        $packing_slip_references[0] = 'N/A';

                    if($delivery->status == 1)
                        $status = trans('file.Packing');
                    elseif($delivery->status == 2)
                        $status = trans('file.Delivering');
                    else
                        $status = trans('file.Delivered');

                    $barcode = \DNS2D::getBarcodePNG($delivery->reference_no, 'QRCODE');
                ?>
                @if($delivery->sale)
                    <tr class="delivery-link" data-barcode="{{$barcode}}" data-delivery='["{{date(optional($general_setting)->date_format, strtotime($delivery->created_at->toDateString()))}}", "{{$delivery->reference_no}}", "{{$delivery->sale->reference_no}}", "{{$status}}", "{{$delivery->id}}", "{{$delivery->sale->customer->name}}", "{{$delivery->sale->customer->phone_number}}", "{{$delivery->sale->customer->address}}", "{{$delivery->sale->customer->city}}", "{{$delivery->note}}", "{{$delivery->user->name}}", "{{$delivery->delivered_by}}", "{{$delivery->recieved_by}}"]'>
                        <td>{{$key}}</td>
                        <td>{{ $delivery->reference_no }}</td>
                        <td>{{ $customer_sale[0]->reference_no }}</td>
                        <td>{{ implode(",", $packing_slip_references) }}</td>
                        <td>{!!$customer_sale[0]->name .'<br>'. $customer_sale[0]->phone_number!!}</td>
                        @if($delivery->courier_id)
                            <td>{{ $delivery->courier->name }}</td>
                        @else
                            <td>N/A</td>
                        @endif
                        <td>{{ $delivery->address }}</td>
                        <td>{{implode(",", $product_names)}}</td>
                        <td>{{number_format($customer_sale[0]->grand_total, 2)}}</td>
                        @if($delivery->status == 1)
                        <td><div class="badge badge-info">{{$status}}</div></td>
                        @elseif($delivery->status == 2)
                        <td><div class="badge badge-primary">{{$status}}</div></td>
                        @else
                        <td><div class="badge badge-success">{{$status}}</div></td>
                        @endif
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{trans('file.action')}}
                                  <span class="caret"></span>
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                    <li>
                                        <button type="button" data-id="{{$delivery->id}}" class="open-EditCategoryDialog btn btn-link"><i class="dripicons-document-edit"></i> {{trans('file.edit')}}</button>
                                    </li>
                                    <li class="divider"></li>
                                    {{ Form::open(['route' => ['delivery.delete', $delivery->id], 'method' => 'post'] ) }}
                                    <li>
                                      <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> {{trans('file.delete')}}</button>
                                    </li>
                                    {{ Form::close() }}
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- Modal -->
<div id="delivery-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
      <div class="modal-content">
        <div class="container mt-3 pb-2 border-bottom">
            <div class="row">
                <div class="col-md-6 d-print-none">
                    <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i class="dripicons-print"></i> {{trans('file.Print')}}</button>

                    {{ Form::open(['route' => 'delivery.sendMail', 'method' => 'post', 'class' => 'sendmail-form'] ) }}
                        <input type="hidden" name="delivery_id">
                        <button class="btn btn-default btn-sm d-print-none"><i class="dripicons-mail"></i> {{trans('file.Email')}}</button>
                    {{ Form::close() }}
                </div>
                <div class="col-md-6">
                    <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close d-print-none"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="col-md-12">
                    <h3 id="exampleModalLabel" class="modal-title text-center container-fluid">
                        {{optional($general_setting)->site_title}}
                    </h3>
                </div>
                <div class="col-md-12 text-center">
                    <i style="font-size: 15px;">{{trans('file.Delivery Details')}}</i>
                </div>
            </div>
        </div>
        <div class="modal-body">
            <table class="table table-bordered" id="delivery-content">
                <tbody></tbody>
            </table>
            <br>
            <table class="table table-bordered product-delivery-list">
                <thead>
                    <th>No</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>{{trans('file.Batch No')}}</th>
                    <th>{{trans('file.Expired Date')}}</th>
                    <th>Qty</th>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div id="delivery-footer" class="row">
            </div>
        </div>
      </div>
    </div>
</div>

<div id="edit-delivery" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Update Delivery')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => 'delivery.update', 'method' => 'post', 'files' => true]) !!}
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Delivery Reference')}}</label>
                        <p id="dr"></p>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Sale Reference')}}</label>
                        <p id="sr"></p>
                    </div>
                    <div class="col-md-12 form-group">
                        <label>{{trans('file.Status')}} *</label>
                        <select name="status" required class="form-control selectpicker">
                            <option value="1">{{trans('file.Packing')}}</option>
                            <option value="2">{{trans('file.Delivering')}}</option>
                            <option value="3">{{trans('file.Delivered')}}</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Courier')}}</label>
                        <select name="courier_id" id="courier_id" class="selectpicker form-control" data-live-search="true" title="Select courier...">
                            @foreach($lims_courier_list as $courier)
                            <option value="{{$courier->id}}">{{$courier->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mt-2 form-group">
                        <label>{{trans('file.Delivered By')}}</label>
                        <input type="text" name="delivered_by" class="form-control">
                    </div>
                    <div class="col-md-6 mt-2 form-group">
                        <label>{{trans('file.Recieved By')}}</label>
                        <input type="text" name="recieved_by" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.customer')}} *</label>
                        <p id="customer"></p>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Attach File')}}</label>
                        <input type="file" name="file" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Address')}} *</label>
                        <textarea rows="3" name="address" class="form-control" required></textarea>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{trans('file.Note')}}</label>
                        <textarea rows="3" name="note" class="form-control"></textarea>
                    </div>
                </div>
                <input type="hidden" name="reference_no">
                <input type="hidden" name="delivery_id">
                <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#sale").siblings('a').attr('aria-expanded','true');
    $("ul#sale").addClass("show");
    $("ul#sale #delivery-menu").addClass("active");

    var delivery_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#print-btn").on("click", function(){
          var divContents = document.getElementById("delivery-details").innerHTML;
        var a = window.open('');
        a.document.write('<html>');
        a.document.write('<body><style>body{font-family: sans-serif;line-height: 1.15;-webkit-text-size-adjust: 100%;}.d-print-none{display:none}.text-center{text-align:center}.row{width:100%;margin-right: -15px;margin-left: -15px;}.col-md-12{width:100%;display:block;padding: 5px 15px;}.col-md-6{width: 50%;float:left;padding: 5px 15px;}table{width:100%;margin-top:30px;}th{text-aligh:left}td{padding:10px}table,th,td{border: 1px solid black; border-collapse: collapse;}#delivery-footer{margin-left:10px}</style><style>@media print {.modal-dialog { max-width: 1000px;} }</style>');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();
        setTimeout(function(){a.close();},10);
        a.print();
    });

    function confirmDelete() {
      if (confirm("Are you sure want to delete?")) {
          return true;
      }
      return false;
    }

    $("tr.delivery-link td:not(:first-child, :last-child)").on("click", function() {
        var delivery = $(this).parent().data('delivery');
        var barcode = $(this).parent().data('barcode');
        deliveryDetails(delivery, barcode);
    });

    function deliveryDetails(delivery, barcode) {
        $('input[name="delivery_id"]').val(delivery[4]);
        $("#delivery-content tbody").remove();
        var newBody = $("<tbody>");
        var rows = '';
        rows += '<tr><td>Date</td><td>'+delivery[0]+'</td></tr>';
        rows += '<tr><td>Delivery Reference</td><td>'+delivery[1]+'</td></tr>';
        rows += '<tr><td>Sale Reference</td><td>'+delivery[2]+'</td></tr>';
        rows += '<tr><td>Status</td><td>'+delivery[3]+'</td></tr>';
        rows += '<tr><td>Customer Name</td><td>'+delivery[5]+'</td></tr>';
        rows += '<tr><td>Address</td><td>'+delivery[7]+', '+delivery[8]+'</td></tr>';
        rows += '<tr><td>Phone Number</td><td>'+delivery[6]+'</td></tr>';
        rows += '<tr><td>Note</td><td>'+delivery[9]+'</td></tr>';

        newBody.append(rows);
        $("table#delivery-content").append(newBody);

        $.get('delivery/product_delivery/' + delivery[4], function(data) {
            $(".product-delivery-list tbody").remove();
            var code = data[0];
            var description = data[1];
            var batch_no = data[2];
            var expired_date = data[3];
            var qty = data[4];
            var newBody = $("<tbody>");
            $.each(code, function(index) {
                var newRow = $("<tr>");
                var cols = '';
                cols += '<td><strong>' + (index+1) + '</strong></td>';
                cols += '<td>' + code[index] + '</td>';
                cols += '<td>' + description[index] + '</td>';
                cols += '<td>' + batch_no[index] + '</td>';
                cols += '<td>' + expired_date[index] + '</td>';
                cols += '<td>' + qty[index] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
            });
            $("table.product-delivery-list").append(newBody);
        });

        var htmlfooter = '<div class="col-md-4 form-group"><p>Prepared By: '+delivery[10]+'</p></div>';
        htmlfooter += '<div class="col-md-4 form-group"><p>Delivered By: '+delivery[11]+'</p></div>';
        htmlfooter += '<div class="col-md-4 form-group"><p>Recieved By: '+delivery[12]+'</p></div>';
        htmlfooter += '<br><br>';
        htmlfooter += '<div class="col-md-2 offset-md-5"><img style="max-width:850px;height:100%;max-height:130px" src="data:image/png;base64,'+barcode+'" alt="barcode" /></div>';

        $('#delivery-footer').html(htmlfooter);
        $('#delivery-details').modal('show');
    }

    $(document).ready(function() {
        $(document).on('click', '.open-EditCategoryDialog', function(){
          var url ="delivery/"
          var id = $(this).data('id').toString();
          url = url.concat(id).concat("/edit");

          $.get(url, function(data){
                $('#dr').text(data[0]);
                $('#sr').text(data[1]);
                $('select[name="status"]').val(data[2]);
                $('.selectpicker').selectpicker('refresh');
                $('input[name="delivered_by"]').val(data[3]);
                $('input[name="recieved_by"]').val(data[4]);
                $('#customer').text(data[5]);
                $('textarea[name="address"]').val(data[6]);
                $('textarea[name="note"]').val(data[7]);
                $('select[name="courier_id"]').val(data[8]);
                $('input[name="reference_no"]').val(data[0]);
                $('input[name="delivery_id"]').val(id);
                $('.selectpicker').selectpicker('refresh');
          });
          $("#edit-delivery").modal('show');
        });
    });

    $('#delivery-table').DataTable( {
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax":{
            url:"delivery/delivery_list_data",
            dataType: "json",
            type:"get",
        },
        "columns": [
            {"data": "key"},
            {"data": "reference_no"},
            {"data": "sale_reference"},
            {"data": "packing_slip_references"},
            {"data": "customer"},
            {"data": "courier"},
            {"data": "address"},
            {"data": "products"},
            {"data": "grand_total"},
            {"data": "status"},
            {"data": "options"},
        ],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
             "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 6]
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }

                   return data;
                },
                'checkboxes': {
                   'selectRow': true,
                   'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        delivery_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                delivery_id[i-1] = $(this).closest('tr').data('id');
                            }
                        });
                        if(delivery_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'delivery/deletebyselection',
                                data:{
                                    deliveryIdArray: delivery_id
                                },
                                success:function(data){
                                    alert(data);
                                }
                            });
                            dt.rows({ page: 'current', selected: true }).remove().draw(false);
                        }
                        else if(!delivery_id.length)
                            alert('Nothing is selected!');
                    }
                    else
                        alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    } );
</script>
@endpush
