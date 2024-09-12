<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Configure Child SKUs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link type="text/css" rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="/assets/css/flexselect.css" media="screen">
    <link type="text/css" rel="stylesheet" href="/assets/css/chosen.min.css">
    <link type="text/css" rel="stylesheet" href="/assets/css/chosenImage.css">

    <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="/assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/assets/js/chosen.jquery.min.js"></script>
    <script type="text/javascript" src="/assets/js/chosenImage.jquery.js"></script>
    <script type="text/javascript" src="/assets/js/liquidmetal.js"></script>
    <script type="text/javascript" src="/assets/js/jquery.flexselect.js"></script>

    <style>
        .chosen-container-single .chosen-single {
            height: 33px;
            border-radius: 3px;
            border: 1px solid #CCCCCC;
        }

        .chosen-container-single .chosen-single span {
            padding-top: 2px;
        }

        .chosen-container-single .chosen-single div b {
            margin-top: 2px;
        }

        .chosenImage-container .chosen-results li,
        .chosenImage-container .chosen-single span {
            background: none 5px center / 25px 25px no-repeat;
            padding-left: 35px;
        }

        input[type=number] {
            width: 50px;
        }

        input[type=checkbox] {
            width: 15px;
            height: 15px;
            padding: 0;
            margin: 0;
            vertical-align: bottom;
            position: relative;
            top: -1px;
            *overflow: hidden;
        }
    </style>
</head>
<body>
@include('includes.header_menu')
<div class="container" style="min-width: 1400px;">
    <ol class="breadcrumb">
        <li><a href="{{url('/')}}">Home</a></li>
        <li class="active"><a href="{{url('/logistics/sku_list')}}">Configure Child SKUs</a></li>
    </ol>
    <div class="col-md-12">
        @include('includes.error_div')
        @include('includes.success_div')
    </div>

    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search</div>
            <div class="panel-body">
                {!! Form::open(['method' => 'get']) !!}

                <div class="col-xs-12">

                    <div class="row">
                        <div class="form-group col-xs-2">
                            {!! Form::text('search_for_first', $request->get('search_for_first'), ['id'=>'search_for_first', 'class' => 'form-control', 'placeholder' => 'Search For First']) !!}
                        </div>

                        <div class="form-group col-xs-2">
                            {!! Form::select('contains_first', $operators, $request->get('contains_first'), ['id'=>'contains_first', 'class' => 'form-control']) !!}
                        </div>

                        <div class="form-group col-xs-2">
                            {!! Form::select('search_in_first', $searchable, $request->get('search_in_first'), ['id'=>'search_in_first', 'class' => 'form-control']) !!}
                        </div>

                        <div class="form-group col-xs-2">
                            {!! Form::text('search_for_second', $request->get('search_for_second'), ['id'=>'search_for_second', 'class' => 'form-control', 'placeholder' => 'Search For Second']) !!}
                        </div>

                        <div class="form-group col-xs-2">
                            {!! Form::select('contains_second', $operators, $request->get('contains_second'), ['id'=>'contains_second', 'class' => 'form-control']) !!}
                        </div>

                        <div class="form-group col-xs-2">
                            {!! Form::select('search_in_second', $searchable, $request->get('search_in_second'), ['id'=>'search_in_second', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-xs-2">
                            {!! Form::text('search_for_third', $request->get('search_for_third'), ['id'=>'search_for_third', 'class' => 'form-control', 'placeholder' => 'Search For Third']) !!}
                        </div>

                        <div class="form-group col-xs-2">
                            {!! Form::select('contains_third', $operators, $request->get('contains_third'), ['id'=>'contains_third', 'class' => 'form-control']) !!}
                        </div>

                        <div class="form-group col-xs-2">
                            {!! Form::select('search_in_third', $searchable, $request->get('search_in_third'), ['id'=>'search_in_third', 'class' => 'form-control']) !!}
                        </div>

                        <div class="form-group col-xs-2">
                            {!! Form::text('search_for_fourth', $request->get('search_for_fourth'), ['id'=>'search_for_fourth', 'class' => 'form-control', 'placeholder' => 'Search For Fourth']) !!}
                        </div>

                        <div class="form-group col-xs-2">
                            {!! Form::select('contains_fourth', $operators, $request->get('contains_fourth'), ['id'=>'contains_fourth', 'class' => 'form-control']) !!}
                        </div>

                        <div class="form-group col-xs-2">
                            {!! Form::select('search_in_fourth', $searchable, $request->get('search_in_fourth'), ['id'=>'search_in_fourth', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-xs-2">
                            {!! Form::select('active', ['0' => 'Show All SKUs', '1' => 'Show Active SKUs', '2' => 'Active & Unbatched'], $request->get('active'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group col-xs-2">
                            {!! Form::select('sku_status', ['SN' => 'No Stock Number',
                                                                                            'RT' => 'Route Unassigned',
                                                                                            'TM' => 'No Template',
                                                                                            'TP' => 'Template not Found',
                                                                                            'ST' => 'Settings not Found'
                                                                                        ], $request->get('sku_status'), ['class' => 'form-control', 'placeholder' => 'SKU Status']) !!}
                        </div>
                        <div class="form-group col-xs-3">
                            {!! Form::select('batch_route_id', $batch_routes, $request->get('batch_route_id'),
                                    ['id' => 'search_route', 'class' => 'form-control batch_route', 'placeholder' => 'Route']) !!}
                        </div>
                        <div class="form-group col-xs-2">
                            {!! Form::select('sure3d', ['0' => 'Not Sure3d', '1' => 'Sure3d'], $request->get('sure3d'), ['class' => 'form-control', 'placeholder' => 'Graphic Type']) !!}
                        </div>
                        <div class="form-group col-xs-1"></div>
                        <div class="form-group col-xs-2">
                            {!! Form::submit('Search', ['class' => 'btn btn-success form-control']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>

            </div>
        </div>

        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">Configure Child SKUs</div>
                <div class="panel-body">
                    {!! Form::open(['method' => 'post', 'url' => 'logistics/update_skus']) !!}

                    {!! Form::hidden('search_in_first', $request->get('search_in_first')) !!}
                    {!! Form::hidden('contains_first', $request->get('contains_first')) !!}
                    {!! Form::hidden('search_for_first', $request->get('search_for_first')) !!}
                    {!! Form::hidden('search_in_second', $request->get('search_in_second')) !!}
                    {!! Form::hidden('contains_second', $request->get('contains_second')) !!}
                    {!! Form::hidden('search_for_second', $request->get('search_for_second')) !!}
                    {!! Form::hidden('search_in_third', $request->get('search_in_third')) !!}
                    {!! Form::hidden('contains_third', $request->get('contains_third')) !!}
                    {!! Form::hidden('search_for_third', $request->get('search_for_third')) !!}
                    {!! Form::hidden('search_in_fourth', $request->get('search_in_fourth')) !!}
                    {!! Form::hidden('contains_fourth', $request->get('contains_fourth')) !!}
                    {!! Form::hidden('search_for_fourth', $request->get('search_for_fourth')) !!}
                    {!! Form::hidden('active', $request->get('active')) !!}
                    {!! Form::hidden('sku_status', $request->get('sku_status')) !!}
                    {!! Form::hidden('batch_route_id', $request->get('batch_route_id')) !!}
                    {!! Form::hidden('sure3d', $request->get('sure3d')) !!}
                    {!! Form::hidden('frame_size', $request->get('frame_size')) !!}

                    <div class="col-xs-12">
                        <div class="row">

                            <div class="form-group col-xs-2">
                                {!! Form::select('allow_mixing_update', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control', 'placeholder' => 'Allow Mixing']) !!}
                            </div>
                            <div class="form-group col-xs-3">
                                {!! Form::select('batch_route_id_update', $batch_routes, null, ['id' => 'batch_route_id', 'class' => 'form-control batch_route', 'style' => 'width: 300px;', 'placeholder' => 'Route']) !!}
                            </div>
                            <div class="form-group col-xs-2">
                                {!! Form::text('graphic_sku_update', null, ['id' => 'graphic_sku', 'class' => 'form-control', 'cols' => '20', 'placeholder' => 'Graphic SKU']) !!}
                            </div>
                            <div class="form-group col-xs-2">
                                {!! Form::select('sure3d_update', ['0' => 'Not Sure3d', '1' => 'Sure3d'], $request->get('sure3d'), ['class' => 'form-control', 'placeholder' => 'Graphic Type']) !!}
                            </div>

                            <div class="form-group col-xs-2">
                                {!! Form::number('frame_size_update', null, ['id' => 'frame_size', 'class' => 'form-control',  'placeholder' => 'Frame Size', 'style' => 'width: 120px;']) !!}
                            </div>

                        </div>
                        <div class="row">
                            <div class="form-group col-xs-2 text-right">
                                <label style="margin-top:5px;">Stock Numbers:</label>
                            </div>
                            <div class="form-group col-xs-5">
                                <select name="stock_select" id="stock_select" class="my-select" style="width:500px;">
                                    <option data-img-src="" value="0">Select a Stock Number</option>
                                    @foreach ($stock_no_list as $stock)
                                        <option data-img-src="{{ $stock->warehouse }}"
                                                value="{{ $stock->stock_no_unique }}">{{ $stock->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-xs-4">
                                <table id="stock_table" class="table">
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-xs-12">
                                {!! Form::submit('Update Selected SKUs', ['class'=> 'btn btn-primary', 'id' => 'update']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (count($options) > 0)
            <h4>
                {{ $options->total() }} Child SKUs found
                <small>(Page {{$options->currentPage()}} of {{$options->lastPage()}})</small>
                <a class="btn btn-success btn-sm pull-right"
                   href="{{ url("/logistics/add_child_sku") }}">Add new child sku</a>
            </h4>

            <div>
                <div class="col-md-12">
                    <p>{!! Form::checkbox('selectall', 1, 0, ['id' => 'selectall']) !!} Select All Child SKUs</p>
                    <label>
                        Bypass options
                        <select id="bypass_option" onchange="checkOption()">
                            <option value="none">Select an option</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </label>

                    <script>
                        function checkOption() {
                            var option = $("#bypass_option").val();

                            var all = ""
                            if (option !== null) {
                                $(".child_sku_box").each(function () {
                                    if (this.checked) {
                                        all = all.concat("&list[]=" + this.value)
                                    }
                                });
                                if (all.length !== 0) {
                                    window.location.href = "/option_mass?status=" + option + all
                                }
                            }
                            console.log(all)
                        }
                    </script>
                    <table class="table table-bordered">

                        @foreach($options as $option)

                            <tr class="info">
                                <td rowspan=2 width="10px" class="info">
                                    <input type="checkbox" name="child_skus[]" class="child_sku_box"
                                           value="{{  htmlspecialchars($option->child_sku) }}"/>
                                </td>
                                <td rowspan=2 width="110px" class="info">
                                    <a href="{{url(sprintf("/logistics/edit_sku?row=%s", $option->unique_row_value))}}"
                                       data-toggle="tooltip" data-placement="top"
                                       title="Edit"><i class='glyphicon glyphicon-pencil text-primary'></i></a>
                                    |
                                    <a href="{{url(sprintf("logistics/create_child_sku?id_catalog=%s", $option->id_catalog)) }}"
                                       data-toggle="tooltip" data-placement="top"
                                       title="Create Child SKUs"
                                       target="_blank"><i class='glyphicon glyphicon-th text-primary'></i></a>
                                    @if ($option->product)
                                        | <a href="{{ $option->product->product_url }}"
                                             data-toggle="tooltip" data-placement="top"
                                             title="View on Web"
                                             target="_blank"><i
                                                    class='glyphicon glyphicon-picture text-primary'></i></a>
                                    @endif
                                    |
                                    <a href="{{url(sprintf("/logistics/add_child_sku?id_catalog=%s&parent_sku=%s", $option->id_catalog, $option->parent_sku)) }}"
                                       data-toggle="tooltip" data-placement="top" title="Duplicate Child SKU"
                                       target="_blank"><i class='glyphicon glyphicon-paste text-primary'></i></a>
                                    <br><br>
                                    @if($option->product && $option->product->product_thumb)
                                        <img src="{{ $option->product->product_thumb }}" width="70">
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td colspan=5 class="info">
                                    <a href="{{url(sprintf("logistics/sku_list?search_for_first=%s&search_in_first=parent_sku", $option->parent_sku)) }}"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       title="View All Child SKUs for Product {{ $option->parent_sku }}"
                                       target="_blank">
                                        @if ($option->product)
                                            {{ $option->product->product_name }}
                                        @else
                                            PRODUCT NOT FOUND
                                        @endif
                                    </a>
                                    :
                                    <a href="{{url(sprintf("/items?search_for_first=%s&search_in_first=exact_child_sku", $option->child_sku)) }}"
                                       data-toggle="tooltip"
                                       title="View All Items Ordered"
                                       target="_blank">{{ $option->child_sku }}</a>
                                </td>
                                <td>
                                    {!! \App\Task::widget('App\Option', $option->id); !!}
                                </td>
                            <tr>
                                <td width="300px">
                                    @foreach($option->inventoryunit_relation as $inventoryunit)
                                        @if ($inventoryunit->stock_no_unique != 'ToBeAssigned')
                                            <table>
                                                <tr>
                                                    <td rowspan=2 width="50px">
                                                        @if ($inventoryunit->inventory && $inventoryunit->inventory->warehouse != '')
                                                            <img src="{{ $inventoryunit->inventory->warehouse }}"
                                                                 height="40">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{!! sprintf('/inventories?search_for_first=%s&search_in_first=stock_no_unique', $inventoryunit->stock_no_unique) !!}"
                                                           target="_blank">{{ $inventoryunit->stock_no_unique }}</a>
                                                        @if ( $inventoryunit->unit_qty != 1)
                                                            x {{ $inventoryunit->unit_qty }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @if ($inventoryunit->inventory)
                                                            {{ $inventoryunit->inventory->stock_name_discription }}
                                                        @else
                                                            Stock Number Not Found
                                                    @endif
                                                    <td>
                                                </tr>
                                            </table>
                                            <br>
                                        @else
                                            {{ $inventoryunit->stock_no_unique }}
                                        @endif
                                    @endforeach
                                </td>
                                <td width="90px">
                                    Mix:
                                    <br>
                                    {!! Form::select('mx_' . $option->unique_row_value, ['1' => 'Yes', '0' => 'No'], $option->allow_mixing, ['id' => 'mx_' . $option->unique_row_value, 'class' => 'form-control', 'cols' => '10']) !!}
                                </td>
                                <td width="300px">
                                    Route:
                                    <br>
{{--                                    <!-- <a href="{!! sprintf('/prod_config/batch_routes#%s', $option->route->batch_code) !!}" -->--}}
{{--                                    <!--	target="_blank">{{ $option->route->batch_route_name }}</a> -->--}}
                                    {!! Form::select('br_' . $option->unique_row_value , $batch_routes, $option->batch_route_id, ['id' => 'br_' . $option->unique_row_value, 'class' => 'form-control batch_route', 'cols' => '100']) !!}
                                    <br>
                                </td>
                                <td width="150px">
                                    Graphic SKU:
                                    <br>
                                    {!! Form::text('gs_' . $option->unique_row_value, $option->graphic_sku, ['id' => 'gs_' . $option->unique_row_value, 'class' => 'form-control', 'cols' => '25']) !!}
                                    <div align="center">
                                        <a class="btn btn-info btn-xs graphic_sku
										@if ($option->graphic_sku == 'NeedGraphicFile' || $option->graphic_sku == '')
											 hidden
										@endif
										" id="CNF-{{ $option->unique_row_value }}" name="{{ $option->graphic_sku }}">Configure
                                            Graphic SKU</a>
                                    </div>
                                </td>
                                <td width="175px">
                                    @if ($option->design)
                                        @setvar($design = $option->design)
                                    @else
                                        @setvar($design = \App\Design::check($option->graphic_sku))
                                    @endif
                                    <table calss="table table-condensed">
                                        <tr>
                                            <td>Template:</td>
                                            <td>
                                                <span id="TPL-{{ $option->unique_row_value }}">{!! $design->template == '1' ? 'Found' : 'Not Found' !!}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Settings:</td>
                                            <td>
                                                <span id="XML-{{ $option->unique_row_value }}">{!! $design->xml == '1' ? 'Found' : 'Not Found' !!}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100">Use Sure3d:</td>
                                            <td>
                                                {!! Form::checkbox('s3_' . $option->unique_row_value, 1, $option->sure3d, ['id' => 's3_' . $option->unique_row_value, 'class' => 'checkbox']) !!}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100">Orientation:</td>
                                            <td>
                                                {!! Form::select('orientation' . $option->unique_row_value, ['0' => 'portrait', '1' => 'landscape'], $option->orientation, ['id' => 'orientation' . $option->unique_row_value, 'class' => 'form-control form-control-sm', 'style' => 'font-size:10px; padding: 1px 0px 1px 0px;']) !!}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Frame Size:</td>
                                            <td>
                                                {!! Form::number('fs_' . $option->unique_row_value, $option->frame_size, ['id' => 'fs_' . $option->unique_row_value, 'class' => 'form-control', 'style' => 'width: 80px;']) !!}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="100">Mirror:</td>
                                            <td>
                                                {!! Form::checkbox('mr_' . $option->unique_row_value, 1, $option->mirror, ['id' => 'mr_' . $option->unique_row_value, 'class' => 'checkbox']) !!}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="75px">
                                    <a class="btn btn-success btn-xs update_sku" id="{{ $option->unique_row_value }}">Update</a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan=8>

                                </td>
                            </tr>
                        @endforeach
                    </table>
                    <div class="col-xs-12 text-center">
                        {!! $options->appends($request->all())->render() !!}
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        @else
            <div class="col-xs-12">
                <div class="alert alert-warning">No Child SKUs found</div>
            </div>
        @endif
    </div>
</div>

{!! Form::open(['url' => url('/graphics/designs'), 'method' => 'POST', 'id' => 'graphic_form', 'target' => '_blank']) !!}
{!! Form::hidden('StyleName', '', ['id' => 'StyleName']) !!}
{!! Form::close() !!}

<script type="text/javascript">

    $(document).ready(function () {

        $(".my-select").chosenImage({
            search_contains: true
        });

        $('.batch_route').flexselect();

        $(window).keydown(function (event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });

        $('.batch_route').on('focus', function () {

            if ($(this).val() == 'Route') {
                $(this).val('');
            }
        });

        $('#stock_select').change(function () {
            var stockno = $('#stock_select').val();

            if (stockno != '0') {
                var description = $('#stock_select').find('option:selected').text();
                var image = $('#stock_select').find('option:selected').attr("data-img-src");

                var markup = '<tr height="60" id="ROW_' + $('#stock_table tr').length + '">' +
                    '<td width="50"><img src="' + image + '" height="50"></td>' +
                    '<td width="275">' + description + '</td>' +
                    '<input type="hidden" name="stocknos[]" value="' + stockno + '">' +
                    '<td>QTY: <input name="QTY_' + stockno + '" type="number"  min="1" value="1"></td>' +
                    '<td><a href = "#" class = "delete" title = "Delete" onclick="ROW_' + $('#stock_table tr').length + '.remove()">' +
                    '<i class ="glyphicon glyphicon-remove text-danger"></i></a></td>'
                '</tr>';

                $('#stock_table tbody').append(markup);

            }
        });

        $('.graphic_sku').click(function (e) {

            e.preventDefault();

            var unique_row_value = $(this).attr("id").substring(4);
            var StyleName = $('#gs_' + unique_row_value).val();

            $("#StyleName").val(StyleName);

            $('form#graphic_form').submit();
        });

        $('.update_sku').click(function (e) {

            e.preventDefault();
            var unique_row_value = $(this).attr("id");
            var graphic_sku = $('#gs_' + unique_row_value).val();
            var route = $('#br_' + unique_row_value).val();
            var mix = $('#mx_' + unique_row_value).val();
            var isChecked = $('#s3_' + unique_row_value).is(":checked");
            if (isChecked) {
                var sure3d = 1;
            } else {
                var sure3d = 0;
            }
            var orientation = $('#orientation' + unique_row_value).val();
            // var sure3d = $('#s3_' + unique_row_value).val();
            var frame_size = $('#fs_' + unique_row_value).val();

            var isChecked = $('#mr_' + unique_row_value).is(":checked");
            if (isChecked) {
                var mirror = 1;
            } else {
                var mirror = 0;
            }

            $(this).html('Loading...');

            var data = "unique_row_value=" + unique_row_value + "&graphic_sku=" + graphic_sku +
                "&route=" + route + "&mix=" + mix + "&sure3d=" + sure3d + "&orientation=" + orientation +
                "&frame_size=" + frame_size + "&mirror=" + mirror;

            console.log(orientation)
            console.log(data)

            $.ajax({
                url: "{{ url('logistics/update_ajax') }}",
                type: "GET",
                dataType: "text",
                data: data,
                context: this,
                success: function (response) {
                    if (response.search('NoXML')) {
                        $('#XML-' + unique_row_value).text('Not Found');
                    }

                    if (response.search('NoTemplate')) {
                        $('#TPL-' + unique_row_value).text('Not Found');
                    }

                    if (graphic_sku != 'NeedGraphicFile' & graphic_sku != '') {
                        $('#CNF-' + unique_row_value).attr("name", graphic_sku);
                        $('#CNF-' + unique_row_value).removeClass('hidden');
                    }

                    if (response.substring(0, 7) == 'Updated') {
                        $(this).removeClass().addClass('btn btn-primary btn-xs update_sku');
                        $(this).html('Updated');
                    } else {
                        $(this).removeClass().addClass('btn btn-danger btn-xs update_sku');
                        $(this).html(response);
                    }
                },
                failure: function (response) {
                    $(this).removeClass().addClass('btn btn-danger btn-xs update_sku');
                    $(this).html('FAILED');
                }
            });
        });

    });

    var state = false;

    $("#selectall").on('click', function () {
        state = !state;
        $(".child_sku_box").prop('checked', state);
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });


</script>
</body>
</html>