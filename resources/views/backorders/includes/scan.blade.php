<div class="row">
  {!! Form::open(['method' => 'get', 'url' => '/backorders/show', 'id' => 'backorder_form']) !!}
  
  <div class = "form-group col-xs-3">
    {!! Form::text('search_for', isset($search_for) ? $search_for : '', ['id'=>'search_for', 'class' => 'form-control', 'placeholder' => 'Scan']) !!}
  </div>
  
  <div class = "form-group col-xs-3">
    {!! Form::select('search_in', ['batch_number' => 'Batch number', 'stock_no_unique' => 'Stock Number'], isset($search_in) ? $search_in : '', ['id'=>'search_in', 'class' => 'form-control']) !!}
  </div>
  
  <div class = "form-group col-xs-2">
    {!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
  </div>
    
    {!! Form::hidden('tab', 'by_batch') !!}
    
  {!! Form::close() !!}
</div>

<script>
  $(function() {
      // Focus on load
      $('#search_for').focus();    
  });
</script>