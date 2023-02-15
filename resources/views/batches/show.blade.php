<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Batch view</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link type="text/css" rel="stylesheet" href="/assets/css/bootstrap.min.css">

    <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="/assets/js/bootstrap.min.js"></script>

    <script src="/assets/js/DYMO.Label.Framework.latest.js" type="text/javascript" charset="UTF-8"></script>
    <script src="/assets/js/dymoBarcode.js" type="text/javascript"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if ($label != null)
        @include('prints.includes.label')
    @endif

    <style>
        td {
            /*width: 1px;*/
            white-space: nowrap;
        }

        td.description {
            white-space: normal;
            /* word-wrap: break-word; */
            max-width: 300px;
            min-width: 250px !important;
            width: 100%;
        }

    </style>

</head>
<body>
@include('includes.header_menu')
<div class="container" style="min-width: 1400px;">
    <ol class="breadcrumb">
        <li><a href="{{url('/')}}">Home</a></li>
        <li><a href="{{url('/batches/list')}}">Batch list</a></li>
        <li class="active">Batch View</li>
    </ol>
    @include('includes.success_div')
    @include('includes.error_div')

    <h3 class="page-header">
        Batch: {{ $batch_number }}
        @if ($batch->store)
            ({{ $batch->store->store_name }})
        @endif
        @if ($batch->status != 'active')
            - <span style="color:red">{!! ucfirst($batch->status) !!}</span>
        @endif
        {!! \App\Task::widget('App\Batch', $batch->id); !!}

        @if ($batch)
            <div class="pull-right">
                <div class="btn-group col-xs-4">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                        Action <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{ url(sprintf("summaries/single?batch_number=%s", $batch_number)) }}"
                               target="_blank">
                                @if ($batch->summary_count == 0)
                                    Print batch Summary</a>
                            @else
                                Reprint Summary</a>
                                @endif
                                </a>
                        </li>
                        <li><a href="#"
                               onclick="print_tray_label('{{ $batch_number }}', '{{ $batch->items->sum("item_quantity") }}', '{{ substr( $batch->min_order_date ?? $batch->creation_date, 0, 10) }}')">
                                Print Dymo Label</a>
                        </li>
                        <li role="separator" class="divider"></li>
                        <li><a href="{{ url('graphics/export_batch/' . $batch_number . '/0/CSV') }}">Export</a></li>
                        <li><a href="{{ url('graphics/export_batch/' . $batch_number . '/1/CSV') }}">Force Export</a>
                        </li>
                        <li><a href="{{ url('graphics/export_batch/' . $batch_number . '/2/CSV') }}">Send to Manual
                                Graphics</a></li>
                        <li><a href="{{ url('graphics/export_batch/' . $batch_number . '/1/XLS') }}">Export as XLS</a>
                        </li>
                        @if ($batch->graphic_found == 'Found')
                            <li role="separator" class="divider"></li>
                            {!! Form::open(['url' => 'graphics/reprint_graphic', 'method' => 'post', 'id' => 'reprint_form']) !!}
                            {!! Form::hidden('name', $batch->batch_number, ['id' => 'reprint_name']) !!}
                            @if ($batch->route)
                                {!! Form::hidden('directory', $batch->route->graphic_dir) !!}
                            @endif
                            @if ($batch->section_id == 6)
                                {!! Form::hidden('goto', 'print_sublimation') !!}
                            @endif
                            {!! Form::close() !!}
                            <li><a href="#" onclick="reprint_form.submit();">Get Graphic from Archive</a></li>
                        @endif
                        @if ($batch->status == 'active' || $batch->status == 'back order')
                            <li role="separator" class="divider"></li>
                            @if($batch->items->where('item_status', 'production')->count() == count($batch->items))
                                {!! Form::open(['name' => 'reject-batch', 'url' => 'reject_batch', 'method' => 'post', 'id' => 'reject']) !!}
                                {!! Form::hidden('batch_id', $batch->id, ['id' => 'batch_id']) !!}
                                {!! Form::button('Reject Batch' , ['id'=>'reject-1', 'class' => 'btn btn-default', 'style'=>'border:none;margin-left:10px;']) !!}
                                {!! Form::close() !!}
                            @endif
                            <li><a href="{{ url('supervisor/release/' . $batch_number) }}">Release Items</a></li>
                        @endif

                    </ul>
                </div>
                <div class="col-xs-4">

                </div>
            </div>
        @endif
    </h3>

    <div class="col-xs-12">
        @if($batch)
            <div class="row">
                <div class="col-xs-1" style="font-weight: bold;">
                    **Route:
                </div>
                @if ($batch->route)
                    <div class="col-xs-9">
                        <a href="{{ url(sprintf("/prod_config/batch_routes#%s", $batch->route->batch_code )) }}"
                           target="_blank">{{ $batch->route->batch_code }}</a>
                        / {{ $batch->route->batch_route_name }} =>
                        {!! $stations !!}
                    </div>
                    <div class="col-xs-2">
                        @if ($batch->route->template)
                            <a href="{{url(sprintf("/prod_config/templates/%d", $batch->route->template->id))}}">
                                {!! $batch->route->template->template_name !!} Template</a>
                        @else
                            No Template
                        @endif
                    </div>
                @else
                    <div class="col-xs-11">Route Not Found</div>
                @endif
            </div>
            <div class="row">
                <div class="col-xs-12"><br></div>
            </div>
            <div class="row">
                <div class="col-xs-1" style="font-weight: bold;">
                    Created:<br>
                </div>
                <div class="col-xs-3">
                    {{ $batch->creation_date }}
                </div>
                <div class="col-xs-1" style="font-weight: bold;">
                    Last Scan:<br>
                </div>
                <div class="col-xs-2">
                    @if (isset($last_scan['date']))
                        {{ $last_scan['date'] }} by {{ $last_scan['username'] }}
                    @else
                        {{ $batch->change_date }}
                    @endif
                </div>
                <div class="col-xs-1" style="font-weight: bold;">
                    Station:<br>
                </div>
                <div class="col-xs-4">
                    @if ($batch->station)
                        {{ $batch->station->station_name }} => {{$batch->station->station_description }}
                    @else
                        Station Not Found
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12"><br></div>
            </div>
            <div class="row">
                <div class="col-xs-1" style="font-weight: bold;">
                    Export:
                </div>
                <div class="col-xs-3">
                    @if ($batch->export_date != NULL)
                        @if ($batch->export_count == 1)
                            Exported {{ $batch->export_count }} time
                            {{ $batch->export_date }}
                        @else
                            Exported {{ $batch->export_count }} times
                            Last export {{ $batch->export_date }}
                        @endif
                    @else
                        Not Exported
                    @endif
                </div>
                <div class="col-xs-4">
                    @if ($batch->csv_found != 0)
                        CSV File Found
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12"><br></div>
            </div>
            <div class="row">
                <div class="col-xs-1" style="font-weight: bold;">
                    Summary:
                </div>
                @if ($batch->summary_date != NULL)
                    <div class="col-xs-3">
                        Printed {{ $batch->summary_date }}
                    </div>
                    <div class="col-xs-3">
                        ({{ $batch->summary_count }} printed - last by {{ $batch->summary_user->username }})
                    </div>
                @else
                    <div class="col-xs-3">
                        Not Printed
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="col-xs-12"><br></div>
            </div>
            <div class="row">
                <div class="col-xs-1" style="font-weight: bold;">
                    Graphic:
                </div>
                <div class="col-xs-3">
                    {{ $batch->graphic_found }}
                    @if($batch->graphic_found == 'Found')
                        :
                        <a href="{{ url(sprintf('batches/view_graphic?batch_number=%s',$batch->batch_number)) }}"
                           target="_blank">View Graphics</a>
                    @endif
                </div>
                @if ($batch->to_printer != '0')
                    <div class="col-xs-1" style="font-weight: bold;">
                        Printed:
                    </div>
                    <div class="col-xs-2">
                        {{ $batch->to_printer }} - {{ $batch->to_printer_date }}
                    </div>
                @endif
            </div>
            @if (count($related) > 0)
                <div class="row">
                    <div class="col-xs-12"><br></div>
                </div>
                <div class="row">
                    <div class="col-xs-1" style="font-weight: bold;">
                        Related:
                    </div>
                    <div class="col-xs-4">
                        @foreach($related as $other_batch)
                            <a href="{{ url(sprintf('batches/details/%s',$other_batch)) }}">
                                {{ $other_batch }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="row">
                <br>
                <table class="table" id="batch-items-table">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Order</th>
                        <th colspan=2></th>
                        <th>Product</th>
                        <th>Options</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($batch->items as $item)
                        <tr data-id="{{$item->id}}">
                            <td>
                                @if ($item->item_status == 'production' || $item->item_status == 'wap' || $item->item_status == 'back order')
                                    @if ($item->item_status == 'wap')
                                        @setvar($wap = ' from WAP')
                                    @else
                                        @setvar($wap = '')
                                    @endif
                                    {!! Form::open(['name' => 'reject-' . $item->id, 'url' => '/reject_item', 'method' => 'get', 'id' => 'reject-' . $item->id]) !!}
                                    {!! Form::hidden('item_id', $item->id, ['id' => 'item_id']) !!}
                                    {!! Form::hidden('origin', 'BD', ['id' => 'origin']) !!}
                                    {!! Form::button('Reject Item' . $wap , ['id'=>'reject-' . $item->item_quantity, 'class' => 'btn btn-sm btn-danger']) !!}
                                    {!! Form::close() !!}
                                @else
                                    {!! ucfirst($item->item_status) !!}
                                @endif
                            </td>
                            <td>
                                Order: <a href="{{url(sprintf('/orders/details/%s', $item->order->id))}}"
                                          target="_blank">{{ $item->order->short_order }}</a>
                                <br>
                                Date: {{substr($item->order->order_date, 0, 10)}}
                                <br>
                                {{ $item->order->store->store_name }}
                                <br>
                                Re Download Graphic: <a href="{{url(sprintf('graphics/download_sure3d_by_item_id?item_id=%s', $item->id))}}"
                                          target="_blank">{{ $item->id }}</a>

                                @if ($batch->station->type == 'G')
                                    <br>
                                    {!! Form::button('Upload ' . $item->id . ' Graphic', ['class' => 'btn btn-success upload-btn', 'id' => $item->id, 'data-batch_number' => $item->batch_number]) !!}



                                    @if(\App\Http\Controllers\ZakekeController::hasSure3D($item->child_sku, request()))
                                    {!! Form::button('Zakeke/Link Fetch', ['class' => 'btn btn-primary', 'id' => "download_upload_zakeke-$item->id", "item-id" => $item->id, "item-pws" => isset(json_decode($item->item_option, true)['PWS Zakeke']), 'data-batch_number' => $item->batch_number, "order_id" => $item->order->short_order]) !!}
                                    @endif
                                    {{--                                    {!! Form::button('Zakeke Fetch', ['class' => 'btn btn-warning', 'id' => "download_upload_zakeke-$item->id", "item-id" => $item->id, 'data-batch_number' => $item->batch_number, "order_id" => $item->order->short_order]) !!}--}}
{{--                                    {!! Form::button('Zakeke Fetch Pws', ['class' => 'btn btn-warning', 'id' => "download_upload_zakeke-2-$item->id", "item-id" => $item->id, 'data-batch_number' => $item->batch_number, "order_id" => $item->order->short_order , "style" => "background-color: pink"]) !!}--}}
{{--                               --}}
                                @endif


                            </td>
                            <script type="application/javascript">

                                $("#download_upload_zakeke-{{$item->id}}").click(function () {

                                    var itemId = $("#download_upload_zakeke-{{$item->id}}").attr("item-id");
                                    var batchNumber = $("#download_upload_zakeke-{{$item->id}}").attr("data-batch_number");
                                    var short_order = "{{ $item->order->short_order }}";
                                    var itemIndex = {{$index}}

                                    var pws = $("#download_upload_zakeke-{{$item->id}}").attr("item-pws")

                                    if(typeof pws !== 'undefined' && pws !== false) {
                                        pws = "&pws=true"
                                    }
                                    window.location.href = "/lazy/upload-download/zakeke/" + itemId + "?batch_number=" + batchNumber + "&item_id=" + itemId + "&short_order=" + short_order + "&fetch_link_from_zakeke_cli=true&item_index=" + itemIndex + pws;
                                });

                                $("#download_upload_zakeke-2-{{$item->id}}").click(function () {

                                    var itemId = $("#download_upload_zakeke-2-{{$item->id}}").attr("item-id");
                                    var batchNumber = $("#download_upload_zakeke-2-{{$item->id}}").attr("data-batch_number");
                                    var short_order = "{{ $item->order->short_order }}";
                                    var itemIndex = {{$index}}

                                    window.location.href = "/lazy/upload-download/zakeke/" + itemId + "?batch_number=" + batchNumber + "&item_id=" + itemId + "&short_order=" + short_order + "&fetch_link_from_zakeke_cli=&pws=true&item_index=" + itemIndex;
                                });
                            </script>
                            <td>
                                @setvar($thumb = \Monogram\Sure3d::getThumb($item))
                                @if($thumb)
                                    <img src="{{ $thumb[0] }}" width="150" height="150">
                            @endif
                            <td>
                                <a href="{{ $item->item_url }}" target="_blank">
                                    <img src="{{$item->item_thumb}}"
                                         @if($item->product)
                                         onerror="{{ $item->product->product_thumb }}"
                                         @endif
                                         width="150" height="150"/></a>
                            </td>
                            <td class="description">
                                <a href="{{ url(sprintf("logistics/sku_list?search_for_first=%s&search_in_first=child_sku", $item->child_sku)) }}"
                                   target="_blank">{{$item->child_sku}}</a>
                                <br>
                                {{$item->item_description}}
                                <br>
                                Item# {{ $item->id }}
                                <br>
                                @if ($item->item_quantity == 1)
                                    QTY: {{ $item->item_quantity }}
                                @else
                                    <strong>QTY: {{ $item->item_quantity }}</strong>
                                @endif
                                <br><br>
                                @if ($item->spec_sheet)
                                    <a href="{{ url(sprintf('/products_specifications/%s', $item->spec_sheet->id)) }}"
                                       target="_blank">Production Instruction</a>
                                    <br>
                                @endif
                                <br>
                                @if($item->supervisor_message)
                                    {{ $item->supervisor_message }}
                                    <br>
                                @endif
                                @if($item->tracking_number)

                                    <div style="color: red;">
                                        TRK# {{ $item->tracking_number }}
                                    </div>
                                @endif
                            </td>
                            <td>{!! Form::textarea('nothing', \Monogram\Helper::jsonTransformer($item->item_option), ['rows' => '6', 'cols' => '40', /*"style" => "border: none; width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;"*/]) !!}</td>
                        </tr>
                        @if ($item->rejections)
                            @foreach ($item->rejections as $reject)
                                <tr class="warning">
                                    <td colspan=2></td>
                                    <td colspan=4>
                                        Item {{ $item->id }} Rejected {{ $reject->created_at }}
                                        by {{ $reject->user->username }}
                                        @if ($reject->rejection_reason_info)
                                            - {{ $reject->rejection_reason_info->rejection_message }}
                                        @endif
                                        - {{ $reject->rejection_message }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                   <script type="application/javascript">
                       function dataURLtoBlob(dataurl) {
                           var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
                               bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
                           while(n--){
                               u8arr[n] = bstr.charCodeAt(n);
                           }
                           return new Blob([u8arr], {type:mime});
                       }

                       function toDataUrl(url, callback) {
                           var xhr = new XMLHttpRequest();
                           xhr.onload = function() {
                               callback(xhr.response);
                           };
                           xhr.open('GET', url);
                           xhr.responseType = 'blob';
                           xhr.send();
                       }
                   </script>
                    </tbody>
                </table>
            </div>
        @endif

        <div id="container">
            <div style="width:600px;float:left;">
                <br><br>
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Note</th>
                        <th>Date</th>
                        <th>User</th>
                        <th>Station</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($notes as $note)
                        <tr>
                            <td>{{ $note->note }}</td>
                            <td>{{ $note->created_at }}</td>
                            <td>{{ $note->user->username }}</td>
                            <td>
                                @if ($note->station)
                                    {{ $note->station->station_name }}
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        {!! Form::open(['method' => 'get', 'id' => 'note_form']) !!}
                        <td colspan=2>
                            {!! Form::textarea('batch_note', null, ['id' => 'batch_note', 'rows' => 2, 'class' => "form-control", 'placeholder' => "Enter New Note"]) !!}
                        </td>
                        <td>
                            {!! Form::submit('Add Note', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-sm btn-primary']) !!}
                        </td>
                        <td></td>
                        {!! Form::close() !!}
                    </tr>

                    </tbody>
                </table>
            </div>

        </div>

        <div style="width:600px;float:right;">
            <br><br>
            <table class="table table-sm">
                <thead>
                <tr>
                    <th>Station</th>
                    <th>Scan In</th>
                    <th>Scan Out</th>
                </tr>
                </thead>
                <tbody>

                @foreach ($scans as $scan)
                    <tr>
                        <td>{{ $scan->station->station_name }}</td>
                        <td>{{ $scan->in_user->username }} {{ $scan->in_date }}</td>
                        <td>
                            @if ($scan->out_user)
                                {{ $scan->out_user->username }} {{ $scan->out_date }}
                            @endif
                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>

    <br><br>
</div>

@include('/rejections/rejection_modal')
@include('/batches/includes/graphic_upload_by_item')

<script type="text/javascript">

    $( document ).ready(function() {

        $(".upload-btn").on('click', function (e) {
                var item_id = $(this).attr('id');
                var batch_number = $(this).data('batch_number');
                $("#upload_item_id").val(item_id);
                $("#upload_batch_number").val(batch_number);
                $("#upload-modal").modal('show');
            }
        );
    });
    function createPopUp() {
        window.open('', 'PopUp', 'scrollbars=no,menubar=no,height=500,width=600,resizable=no,toolbar=no,status=no');
    }

    var form = null;

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    $("#reprint").click(function (e) {

        e.preventDefault();

        $(this).button('loading');
        $(this).attr("disabled", "disabled");

        $.ajax({
            type: 'post',
            url: '{{ url("graphics/reprint_graphic") }}',
            data: $("#reprint_form").serialize(),
            context: this,
            success: function (response) {
                if (response != 'success') {
                    $(this).removeClass('btn-info').addClass('btn-danger');
                } else {
                    $(this).removeClass('btn-info').addClass('btn-success');
                }
                $(this).html(response);
            }
        });

    });

</script>

</body>
</html>