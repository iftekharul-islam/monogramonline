<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Edit Stock</title>
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

</head>
<body>
    @include('includes.header_menu')
    <div class = "container">
        <ol class="breadcrumb">
            <li><a href="{{url('/')}}">Home</a></li>
            <li><a href="{{url('inventories')}}">Inventories</a></li>
            <li class="active">Edit Stock</li>
        </ol>
        @if($errors->any())
            <div class = "col-xs-12">
                <div class = "alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

         {!! Form::open(['url' => url(sprintf("/inventories/%d", $inventory->id)), 'method' => 'put']) !!}
        <fieldset>
            <legend>Edit Stock</legend>
            <div class = "form-group col-xs-12">
                {!! Form::label('stock_no_unique', 'Stock #', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('stock_no_unique', $inventory->stock_no_unique, ['readonly', 'id' => 'stock_no_unique', 'class' => "form-control"]) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('section_id', 'Section', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::select('section_id', $sections, $inventory->section_id, ['id'=>'section_id', 'class' => 'form-control']) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('stock_name_discription', 'Description', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('stock_name_discription', $inventory->stock_name_discription, ['id' => 'stock_name_discription', 'class' => "form-control", 'placeholder' => "Enter description"]) !!}
                </div>
            </div>

            <div class = "form-group col-xs-12">
                {!! Form::label('sku_weight', 'Weight (ozs)', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::number('sku_weight', $inventory->sku_weight, ['id' => 'sku_weight', 'class' => "form-control", 'min' => '0', 'step' => '.001']) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('re_order_qty', 'Order Quantity', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::number('re_order_qty', $inventory->re_order_qty, ['id' => 're_order_qty', 'class' => "form-control", 'min' => '0']) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('min_reorder', 'Minimum Stock Quantity', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::number('min_reorder', $inventory->min_reorder, ['id' => 'min_reorder', 'class' => "form-control", 'min' => '0']) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('last_cost', 'Last Cost', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::number('last_cost', $inventory->last_cost, ['id' => 'last_cost', 'class' => "form-control", 'min' => '0', 'step' => '.01']) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('upc', 'Upc', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('upc', $inventory->upc, ['id' => 'upc', 'class' => "form-control", 'placeholder' => "Enter Upc"]) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('wh_bin', 'Bin', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('wh_bin', $inventory->wh_bin, ['id' => 'wh_bin', 'class' => "form-control", 'placeholder' => "Enter Bin"]) !!}
                </div>
            </div>    
            <div class = "form-group col-xs-12">
                {!! Form::label('warehouse', 'Image URL', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('warehouse', $inventory->warehouse, ['id' => 'warehouse', 'class' => "form-control", 'placeholder' => "Enter Image URL"]) !!}
                </div>
            </div>

            <div class = "form-group col-xs-12">
                {!! Form::label('dropship', 'Dropship', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::checkbox('dropship', 1, $dropship, ['id' => 'dropship', 'class' => 'checkbox']) !!}
                </div>
            </div>

            <div class="form-group col-xs-12">
                {!! Form::label('dropship_sku', 'Dropship SKU', ['class' => 'col-xs-3 control-label']) !!}
                <div class="col-xs-4">
                    {!! Form::text('dropship_sku', $dropshipSKU, ['id' => 'dropship_sku', 'class' => "form-control", 'placeholder' => "Enter Dropship SKU"]) !!}
                </div>
            </div>

            <div class = "form-group col-xs-12">
                {!! Form::label('dropship_cost', 'Dropship Cost', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::number('dropship_cost', $dropshipCost, ['id' => 'dropship_cost', 'class' => "form-control", 'placeholder' => "Enter Dropship Cost", 'min' => '0', 'step' => '.01']) !!}
                </div>
            </div>
            
            <div class = "col-xs-12 apply-margin-top-bottom">
                <div class = "col-xs-offset-2 col-xs-4">
                    {!! Form::submit('Edit Stock', ['class' => 'btn btn-primary btn-block']) !!}
                </div>
            </div>
        </fieldset>
        {!! Form::close() !!}
    </div>
</body>
</html>