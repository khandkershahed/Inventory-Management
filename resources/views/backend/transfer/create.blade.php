@extends('backend.layout.main') @section('content')
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>{{trans('file.Add Transfer')}}</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                        {!! Form::open(['route' => 'transfers.store', 'method' => 'post', 'files' => true, 'id' => 'transfer-form']) !!}

                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.Date')}}</label>
                                            <input type="text" name="created_at" class="form-control date" placeholder="Choose date"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.From Warehouse')}} *</label>
                                            <select required name="from_warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" id="from-warehouse-id" title="Select warehouse...">
                                                @foreach($lims_warehouse_list as $warehouse)
                                                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.To Warehouse')}} *</label>
                                            <select required name="to_warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select warehouse...">
                                                @foreach($lims_warehouse_list as $warehouse)
                                                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans("file.Status")}}</label>
                                            <select name="status" class="form-control selectpicker">
                                                <option value="1">{{trans('file.Completed')}}</option>
                                                <option value="2">{{trans('file.Pending')}}</option>
                                                <option value="3">{{trans('file.Sent')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <label>{{trans('file.Select Product')}}</label>
                                        <div class="search-box input-group">
                                            <button type="button" class="btn btn-secondary btn-lg"><i class="fa fa-barcode"></i></button>
                                            <input type="text" name="product_code_name" id="lims_productcodeSearch" placeholder="Please type product code and select..." class="form-control" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-5">
                                    <div class="col-md-12">

                                        <h5>{{trans('file.Order Table')}} *</h5>
                                        <div class="table-responsive mt-3">
                                            <table id="myTable" class="table table-hover order-list">
                                                <thead>
                                                    <tr>
                                                        <th>{{trans('file.name')}}</th>
                                                        <th>{{trans('file.Code')}}</th>
                                                        <th>{{trans('file.Batch No')}}</th>
                                                        <th>{{trans('file.Quantity')}}</th>
                                                        <th>{{trans('file.Net Unit Cost')}}</th>
                                                        <th>{{trans('file.Tax')}}</th>
                                                        <th>{{trans('file.Subtotal')}}</th>
                                                        <th><i class="dripicons-trash"></i></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot class="tfoot active">
                                                    <th colspan="2">{{trans('file.Total')}}</th>
                                                    <th></th>
                                                    <th id="total-qty">0</th>
                                                    <th></th>
                                                    <th id="total-tax">{{number_format(0, optional($general_setting)->decimal, '.', '')}}</th>
                                                    <th id="total">{{number_format(0, optional($general_setting)->decimal, '.', '')}}</th>
                                                    <th><i class="dripicons-trash"></i></th>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_qty" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_discount" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_tax" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_cost" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="item" />
                                            <input type="hidden" name="order_tax" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="grand_total" />
                                            <input type="hidden" name="paid_amount" value="{{number_format(0, optional($general_setting)->decimal, '.', '')}}" />
                                            <input type="hidden" name="payment_status" value="1" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.Shipping Cost')}}</label>
                                            <input type="number" name="shipping_cost" class="form-control" step="any"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{trans('file.Attach Document')}}</label>
                                            <i class="dripicons-question" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                            <input type="file" name="document" class="form-control" />
                                            @if($errors->has('extension'))
                                                <span>
                                                   <strong>{{ $errors->first('extension') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>{{trans('file.Note')}}</label>
                                            <textarea rows="5" class="form-control" name="note"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary" id="submit-button">
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <table class="table table-bordered table-condensed totals">
            <td><strong>{{trans('file.Items')}}</strong>
                <span class="pull-right" id="item">{{number_format(0, optional($general_setting)->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{trans('file.Total')}}</strong>
                <span class="pull-right" id="subtotal">{{number_format(0, optional($general_setting)->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{trans('file.Shipping Cost')}}</strong>
                <span class="pull-right" id="shipping_cost">{{number_format(0, optional($general_setting)->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{trans('file.grand total')}}</strong>
                <span class="pull-right" id="grand_total">{{number_format(0, optional($general_setting)->decimal, '.', '')}}</span>
            </td>
        </table>
    </div>
    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modal_header" class="modal-title"></h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row modal-element">
                            <div class="col-md-4 form-group">
                                <label>{{trans('file.Quantity')}}</label>
                                <input type="number" name="edit_qty" class="form-control" step="any">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>{{trans('file.Unit Cost')}}</label>
                                <input type="number" name="edit_unit_cost" class="form-control" step="any">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>{{trans('file.Product Unit')}}</label>
                                <select name="edit_unit" class="form-control selectpicker">
                                </select>
                            </div>
                        </div>
                            <button type="button" name="update_btn" class="btn btn-primary">{{trans('file.update')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</section>

@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#transfer").siblings('a').attr('aria-expanded','true');
    $("ul#transfer").addClass("show");
    $("ul#transfer #transfer-create-menu").addClass("active");
// array data depend on warehouse
var lims_product_array = [];
var product_code = [];
var product_name = [];
var product_qty = [];

// array data with selection
var product_cost = [];
var tax_rate = [];
var tax_name = [];
var tax_method = [];
var unit_name = [];
var unit_operator = [];
var unit_operation_value = [];
var is_imei = [];
var product_price = [];
// temporary array
var temp_unit_name = [];
var temp_unit_operator = [];
var temp_unit_operation_value = [];

var rowindex;
var row_product_cost;

$('.selectpicker').selectpicker({
    style: 'btn-link',
});

$('[data-toggle="tooltip"]').tooltip();

$('select[name="from_warehouse_id"]').on('change', function() {
    var id = $(this).val();
    $.get('getproduct/' + id, function(data) {
        lims_product_array = [];
        product_code = data[0];
        product_name = data[1];
        product_qty = data[2];
        product_warehouse_price = data[7];
        is_embeded = data[11];
        imei_number = data[12];
        $.each(product_code, function(index) {
            lims_product_array.push(product_code[index]+'|'+product_name[index]+'|'+imei_number[index]+'|'+is_embeded[index]);

           // lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')');
        });
    });
});

$('#lims_productcodeSearch').on('input', function(){
    var warehouse_id = $('select[name="from_warehouse_id"]').val();
    temp_data = $('#lims_productcodeSearch').val();

    if(!warehouse_id){
        $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
        alert('Please select Warehouse!');
    }
});

var lims_productcodeSearch = $('#lims_productcodeSearch');

lims_productcodeSearch.autocomplete({
    source: function(request, response) {
        var matcher = new RegExp(".?" + $.ui.autocomplete.escapeRegex(request.term), "i");
        response($.grep(lims_product_array, function(item) {
            return matcher.test(item);
        }));
    },
    response: function(event, ui) {
        if (ui.content.length == 1) {
            var data = ui.content[0].value;
            $(this).autocomplete( "close" );
            productSearch(data);
        };
    },
    select: function(event, ui) {
        var data = ui.item.value;
        productSearch(data);
    }
});

//Change quantity
$("#myTable").on('input', '.qty', function() {
    rowindex = $(this).closest('tr').index();
    checkQuantity($(this).val(), true);
});

$("#myTable").on("change", ".batch-no", function () {
    rowindex = $(this).closest('tr').index();
    var product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-id').val();
    var warehouse_id = $('#from-warehouse-id').val();
    $.get('../check-batch-availability/' + product_id + '/' + $(this).val() + '/' + warehouse_id, function(data) {
        if(data['message'] != 'ok') {
            alert(data['message']);
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.batch-no').val('');
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-batch-id').val('');
        }
        else {
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-batch-id').val(data['product_batch_id']);
            code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
            pos = product_code.indexOf(code);
            product_qty[pos] = data['qty'];
        }
    });
});
//Delete product
$("table.order-list tbody").on("click", ".ibtnDel", function(event) {
    rowindex = $(this).closest('tr').index();
    product_cost.splice(rowindex, 1);
    tax_rate.splice(rowindex, 1);
    tax_name.splice(rowindex, 1);
    tax_method.splice(rowindex, 1);
    unit_name.splice(rowindex, 1);
    unit_operator.splice(rowindex, 1);
    unit_operation_value.splice(rowindex, 1);
    is_imei.splice(rowindex, 1);
    $(this).closest("tr").remove();
    calculateTotal();
});

//Edit product
$("table.order-list").on("click", ".edit-product", function() {
    rowindex = $(this).closest('tr').index();
    edit();
});

//Update product
$('button[name="update_btn"]').on("click", function() {
    // if(is_imei[rowindex]) {
        var imeiNumbers = '';
        $("#editModal .imei-numbers").each(function(i) {
            if (i)
                imeiNumbers += ','+ $(this).val();
            else
                imeiNumbers = $(this).val();
        });
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val(imeiNumbers);
    // }

    var edit_qty = $('input[name="edit_qty"]').val();
    var edit_unit_cost = $('input[name="edit_unit_cost"]').val();

    var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
    var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));

    if (row_unit_operator == '*') {
        product_cost[rowindex] = $('input[name="edit_unit_cost"]').val() / row_unit_operation_value;
    } else {
        product_cost[rowindex] = $('input[name="edit_unit_cost"]').val() * row_unit_operation_value;
    }

    var position = $('select[name="edit_unit"]').val();
    var temp_operator = temp_unit_operator[position];
    var temp_operation_value = temp_unit_operation_value[position];
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val(temp_unit_name[position]);
    temp_unit_name.splice(position, 1);
    temp_unit_operator.splice(position, 1);
    temp_unit_operation_value.splice(position, 1);

    temp_unit_name.unshift($('select[name="edit_unit"] option:selected').text());
    temp_unit_operator.unshift(temp_operator);
    temp_unit_operation_value.unshift(temp_operation_value);

    unit_name[rowindex] = temp_unit_name.toString() + ',';
    unit_operator[rowindex] = temp_unit_operator.toString() + ',';
    unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';
    checkQuantity(edit_qty, false);
});

function productSearch(data){
    var product_info = data.split("|");
    var code = product_info[0];
    var pre_qty = 0;
    var flag = true;

    $(".product-code").each(function(i) {
        if ($(this).val() == code) {
            rowindex = i;
            if(product_info[2] != 'null') {
                imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .imei-number').val();
                imeiNumbersArray = imeiNumbers.split(",");
                // console.log('arra '+ rowindex);

                if(imeiNumbersArray.includes(product_info[2])) {
                    alert('Same imei or serial number is not allowed!');
                    flag = false;
                    $('#lims_productcodeSearch').val('');
                }
            }
            pre_qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
        }
    });
    if(flag)
    {
        data += '?'+$('#customer_id').val()+'?'+(parseFloat(pre_qty) + 1);

        $.ajax({
            type: 'GET',
            url: 'lims_product_search',
            data: {
                data: data
            },
            success: function(data) {
                var flag = 1;
                if (pre_qty > 0) {
                    var qty = data[15];
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
                    pos = product_code.indexOf(data[1]);

                    flag = 0;
                    checkQuantity(String(qty), true);
                    flag = 0;
                }
                $("input[name='product_code_name']").val('');
                if(flag){
                    var newRow = $("<tr>");
                    var cols = '';
                    temp_unit_name = (data[6]).split(',');
                    cols += '<td>' + data[0] + '<button type="button" class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal"> <i class="dripicons-document-edit"></i></button></td>';
                    cols += '<td>' + data[1] + '</td>';
                    if(data[11])
                        cols += '<td><input type="text" class="form-control batch-no" required/> <input type="hidden" class="product-batch-id" name="product_batch_id[]"/> </td>';
                    else
                        cols += '<td><input type="text" class="form-control batch-no" disabled/> <input type="hidden" class="product-batch-id" name="product_batch_id[]"/> </td>';
                    cols += '<td><input type="number" class="form-control qty" name="qty[]" value="1" step="any" required/></td>';
                    cols += '<td class="net_unit_cost"></td>';
                    cols += '<td class="tax"></td>';
                    cols += '<td class="sub-total"></td>';
                    cols += '<td><button type="button" class="ibtnDel btn btn-md btn-danger">{{trans("file.delete")}}</button></td>';
                    cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + data[1] + '"/>';
                    cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[9] + '"/>';
                    cols += '<input type="hidden" class="purchase-unit" name="purchase_unit[]" value="' + temp_unit_name[0] + '"/>';
                    cols += '<input type="hidden" class="net_unit_cost" name="net_unit_cost[]" />';
                    cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + data[3] + '"/>';
                    cols += '<input type="hidden" class="tax-value" name="tax[]" />';
                    cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" />';
                    cols += '<input type="hidden" class="imei-number" name="imei_number[]" value="'+data[18]+'" />';

                    newRow.append(cols);
                    $("table.order-list tbody").prepend(newRow);
                    rowindex = newRow.index();
                    product_cost.splice(rowindex, 0, parseFloat(data[2]));
                    tax_rate.splice(rowindex, 0, parseFloat(data[3]));
                    tax_name.splice(rowindex, 0, data[4]);
                    tax_method.splice(rowindex, 0, data[5]);
                    unit_name.splice(rowindex, 0, data[6]);
                    unit_operator.splice(rowindex, 0, data[7]);
                    unit_operation_value.splice(rowindex, 0, data[8]);
                    is_imei.splice(rowindex, 0, data[12]);
                    checkQuantity(1, true);
                    if(data[12]) {
                        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.edit-product').click();
                    }
                }
                else if(data[18]) {
                    var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();
                    if(imeiNumbers)
                        imeiNumbers += ','+data[18];
                    else
                        imeiNumbers = data[18];
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val(imeiNumbers);
                }
            }
        });
    }
}

function edit() {
    $(".imei-section").remove();

        var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();
        if(imeiNumbers.length) {
            imeiArrays = imeiNumbers.split(",");
            htmlText = `<div class="col-md-8 form-group imei-section">
                        <label>IMEI or Serial Numbers</label>
                        <div class="table-responsive ml-2">
                            <table id="imei-table" class="table table-hover">
                                <tbody>`;
            for (var i = 0; i < imeiArrays.length; i++) {
                htmlText += `<tr>
                                <td>
                                    <input type="text" class="form-control imei-numbers" name="imei_numbers[]" value="`+imeiArrays[i]+`" />
                                </td>
                                <td>
                                    <button type="button" class="imei-del btn btn-sm btn-danger">X</button>
                                </td>
                            </tr>`;
            }
            htmlText += `</tbody>
                            </table>
                        </div>
                    </div>`;
            $("#editModal .modal-element").append(htmlText);
        }

    var row_product_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
    $('#modal_header').text(row_product_name + '(' + row_product_code + ')');

    var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
    $('input[name="edit_qty"]').val(qty);

    unitConversion();
    $('input[name="edit_unit_cost"]').val(row_product_cost.toFixed({{optional($general_setting)->decimal}}));

    temp_unit_name = (unit_name[rowindex]).split(',');
    temp_unit_name.pop();
    temp_unit_operator = (unit_operator[rowindex]).split(',');
    temp_unit_operator.pop();
    temp_unit_operation_value = (unit_operation_value[rowindex]).split(',');
    temp_unit_operation_value.pop();
    $('select[name="edit_unit"]').empty();
    $.each(temp_unit_name, function(key, value) {
        $('select[name="edit_unit"]').append('<option value="' + key + '">' + value + '</option>');
    });
    $('.selectpicker').selectpicker('refresh');
}

//Delete imei
$(document).on("click", "table#imei-table tbody .imei-del", function() {
    $(this).closest("tr").remove();
    //decreaing qty
    var edit_qty = parseFloat($('input[name="edit_qty"]').val());
    $('input[name="edit_qty"]').val(edit_qty-1);
});

function checkQuantity(purchase_qty, flag) {
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
    var pos = product_code.indexOf(row_product_code);
    var operator = unit_operator[rowindex].split(',');
    var operation_value = unit_operation_value[rowindex].split(',');
    if(operator[0] == '*')
        total_qty = purchase_qty * operation_value[0];
    else if(operator[0] == '/')
        total_qty = purchase_qty / operation_value[0];

    if (total_qty > parseFloat(product_qty[pos])) {
        alert('Quantity exceeds stock quantity!');
        if (flag) {
            purchase_qty = purchase_qty.substring(0, purchase_qty.length - 1);
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(purchase_qty);
        }
        else {
            edit();
            return;
        }
    }
    else {
        $('#editModal').modal('hide');
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(purchase_qty);
    }
    calculateRowProductData(purchase_qty);
}

function calculateRowProductData(quantity) {
    unitConversion();
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed({{optional($general_setting)->decimal}}));

    if (tax_method[rowindex] == 1) {
        var net_unit_cost = row_product_cost;
        var tax = net_unit_cost * quantity * (tax_rate[rowindex] / 100);
        var sub_total = (net_unit_cost * quantity) + tax;

        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').text(net_unit_cost.toFixed({{optional($general_setting)->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').val(net_unit_cost.toFixed({{optional($general_setting)->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax').text(tax.toFixed({{optional($general_setting)->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed({{optional($general_setting)->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').text(sub_total.toFixed({{optional($general_setting)->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed({{optional($general_setting)->decimal}}));
    } else {

        var sub_total_unit = row_product_cost;
        var net_unit_cost = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
        var tax = (sub_total_unit - net_unit_cost) * quantity;
        var sub_total = sub_total_unit * quantity;

        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').text(net_unit_cost.toFixed({{optional($general_setting)->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').val(net_unit_cost.toFixed({{optional($general_setting)->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax').text(tax.toFixed({{optional($general_setting)->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed({{optional($general_setting)->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').text(sub_total.toFixed({{optional($general_setting)->decimal}}));
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed({{optional($general_setting)->decimal}}));
    }

    calculateTotal();
}

function unitConversion() {
    var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
    var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));

    if (row_unit_operator == '*') {
        row_product_cost = product_cost[rowindex] * row_unit_operation_value;
    } else {
        row_product_cost = product_cost[rowindex] / row_unit_operation_value;
    }
}

function calculateTotal() {
    //Sum of quantity
    var total_qty = 0;
    $(".qty").each(function() {

        if ($(this).val() == '') {
            total_qty += 0;
        } else {
            total_qty += parseFloat($(this).val());
        }
    });
    $("#total-qty").text(total_qty);
    $('input[name="total_qty"]').val(total_qty);

    //Sum of tax
    var total_tax = 0;
    $(".tax").each(function() {
        total_tax += parseFloat($(this).text());
    });
    $("#total-tax").text(total_tax.toFixed({{optional($general_setting)->decimal}}));
    $('input[name="total_tax"]').val(total_tax.toFixed({{optional($general_setting)->decimal}}));

    //Sum of subtotal
    var total = 0;
    $(".sub-total").each(function() {
        total += parseFloat($(this).text());
    });
    $("#total").text(total.toFixed({{optional($general_setting)->decimal}}));
    $('input[name="total_cost"]').val(total.toFixed({{optional($general_setting)->decimal}}));

    calculateGrandTotal();
}

function calculateGrandTotal() {

    var item = $('table.order-list tbody tr:last').index();

    var total_qty = parseFloat($('#total-qty').text());
    var subtotal = parseFloat($('#total').text());
    var shipping_cost = parseFloat($('input[name="shipping_cost"]').val());

    if (!shipping_cost)
        shipping_cost = {{number_format(0, optional($general_setting)->decimal, '.', '')}};

    item = ++item + '(' + total_qty + ')';

    var grand_total = (subtotal + shipping_cost);

    $('#item').text(item);
    $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
    $('#subtotal').text(subtotal.toFixed({{optional($general_setting)->decimal}}));
    $('#shipping_cost').text(shipping_cost.toFixed({{optional($general_setting)->decimal}}));
    $('#grand_total').text(grand_total.toFixed({{optional($general_setting)->decimal}}));
    $('input[name="grand_total"]').val(grand_total.toFixed({{optional($general_setting)->decimal}}));
}

$('input[name="shipping_cost"]').on("input", function() {
    calculateGrandTotal();
});

$(window).keydown(function(e){
    if (e.which == 13) {
        var $targ = $(e.target);
        if (!$targ.is("textarea") && !$targ.is(":button,:submit")) {
            var focusNext = false;
            $(this).find(":input:visible:not([disabled],[readonly]), a").each(function(){
                if (this === e.target) {
                    focusNext = true;
                }
                else if (focusNext){
                    $(this).focus();
                    return false;
                }
            });
            return false;
        }
    }
});

$('#transfer-form').on('submit',function(e){
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
        alert("Please insert product to order table!")
        e.preventDefault();
    }
    else if($('select[name="from_warehouse_id"]').val() == $('select[name="to_warehouse_id"]').val()){
        alert('Both Warehouse can not be same!');
        e.preventDefault();
    }
    else {
        $("#submit-button").prop('disabled', true);
    }
});
</script>
@endpush
