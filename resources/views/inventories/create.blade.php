<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Create New Stock</title>
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
</head>
<body>
    @include('includes.header_menu')
    <div class = "container">
        <ol class="breadcrumb">
            <li><a href="{{url('/')}}">Home</a></li>
            <li><a href="{{url('inventories')}}">Inventories</a></li>
            <li class="active">Create New Stock</li>
        </ol>
        
        @include('includes.error_div')
        @include('includes.success_div')
        
        {!! Form::open(['url' => url('/inventories'), 'method' => 'post']) !!}
        <fieldset>
            <legend>Create New Stock</legend>
            <div class = "form-group col-xs-12">
                {!! Form::label('stock_no_unique', 'Stock Number', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('stock_no_unique', null, ['id' => 'stock_no', 'class' => "form-control", 'placeholder' => "Leave blank to generate"]) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('stock_name_discription', 'Description', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('stock_name_discription', $inventory->stock_name_discription ?? null, ['id' => 'stock_name_discription', 'class' => "form-control", 'placeholder' => "Enter description"]) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('section_id', 'Section', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::select('section_id', $sections, $inventory->section_id ?? null, ['id'=>'section_id', 'class' => 'form-control']) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('sku_weight', 'Weight', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::number('sku_weight', $inventory->sku_weight ?? null, ['id' => 'sku_weight', 'class' => "form-control", 'min' => '0', 'step' => '.001']) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('re_order_qty', 'Order Quantity', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::number('re_order_qty', $inventory->re_order_qty ?? null, ['id' => 're_order_qty', 'class' => "form-control", 'min' => '0']) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('min_reorder', 'Minimum Stock Quantity', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::number('min_reorder', $inventory->min_reorder ?? null, ['id' => 'min_reorder', 'class' => "form-control", 'min' => '0']) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('last_cost', 'Last Cost', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::number('last_cost', $inventory->last_cost ?? 0, ['id' => 'last_cost', 'class' => "form-control", 'min' => '0', 'step' => '.01']) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('upc', 'Upc', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('upc', $inventory->upc ?? null, ['id' => 'upc', 'class' => "form-control", 'placeholder' => "Enter Upc"]) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('wh_bin', 'Bin', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('wh_bin', $inventory->wh_bin ?? null, ['id' => 'wh_bin', 'class' => "form-control", 'placeholder' => "Enter Bin"]) !!}
                </div>
            </div>    
            <div class = "form-group col-xs-12">
                {!! Form::label('warehouse', 'Image URL', ['class' => 'col-xs-3 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('warehouse', $inventory->warehouse ?? null, ['id' => 'warehouse', 'class' => "form-control", 'placeholder' => "Enter Image URL"]) !!}
                </div>
            </div>                                                                                
            
            <div class = "col-xs-12 apply-margin-top-bottom">
                <div class = "col-xs-offset-2 col-xs-4">
                    {!! Form::submit('Create New Stock', ['class' => 'btn btn-primary btn-block']) !!}
                </div>
            </div>
        </fieldset>
        {!! Form::close() !!}
    </div>
</body>
</html>