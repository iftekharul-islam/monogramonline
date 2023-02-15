@if (count($error_list) > 0)
  <table class="table">
    <thead>
      {!! Form::open(['url' => 'graphics/export_batchbulk', 'method' => 'get', 'id' => 'export3_form']) !!}
      
      {!! Form::hidden('tab', 'error') !!}
      {!! Form::hidden('force', '0') !!}
      <tr>
        <td colspan=11>
          {!! Form::button('Export Selected Batches Again' , ['id'=>'export3', 'class' => 'btn btn-sm btn-primary']) !!}
        </td>
      </tr>      
      <tr>
          <th style="width:30px;">
            <input type="checkbox" name="error_export" id="error_export" class="checkbox">	
          </th>
          <th style="width:150px;">Select All</th>
          <th style="width:150px;">Graphic Error</th>
          <th style="width:50px;">Lines</th>
          <th style="width:250px;">Current Station</th>
          <th style="width:100px;">Image</th>
          <th style="width:700px;"></th>
      </tr>
    </thead>

    <tbody>
      
    @foreach($error_list as $error)
      @setvar($batch = $error['batch'])

      <tr>
        <td>
          <input type = "checkbox" name = "batch_number[]" class = "error_checkbox"
                 value = "{{ $batch->batch_number }}" />
        </td>
        <td>
            <a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number)) }}">
                      {{ $batch->batch_number }}</a>
            <br>
            @if ($batch->status != 'active')
              ({{ $batch->status }})
            @endif
        </td>
          
        <td>{!! $batch->graphic_found !!}</td>
        
        <td>
          @if ($batch->items)
            {{ count($batch->items) }}
          @endif
        </td>
        <td>
          @if ($batch->station)
            <span data-toggle = "tooltip" data-placement = "top"
                title = "{{ $batch->station_description }}">{{ $batch->station_name }}<br>
                        {{ $batch->station_description }}</span>
          @endif
        </td>    
        
        @if ($batch->items->first())
                            
            <td>
              <span data-toggle = "tooltip" data-placement = "top"
                      title = "{{ $batch->items->first()->child_sku }}">
                <img src = "{{ $batch->items->first()->item_thumb }}" width = "70" height = "70" />
              </span>

            </td>
            
        @else
            
            <td> No Items </td>
          
        @endif
      
        @if (isset($error['graphics']))
          @setvar($graphics = $error['graphics'])
          
          <td>
            <table class="table table-bordered">
              <tr bgcolor="#d1e0e0">
                <th style="width:200px;word-wrap:break-word;">Child SKU</th>
                <th style="width:85px;">Graphic SKU</th>
                <th style="width:75px;">XML Settings</th>
                <th style="width:75px;">Template File</th>
              </tr>
              
              @foreach ($graphics as $graphic)
                <tr>
                  <td>{{ $graphic['child_sku'] }} </td>
                  <td>{{ $graphic['sku'] }} </td>
                  <td>{{ $graphic['xml'] }} </td>
                  <td>{{ $graphic['template'] }} </td>
                </tr>
              @endforeach
            
            </table>
          <td>
        @else 
          <td></td>
        @endif
      </tr>
    @endforeach
    
    {!! Form::close() !!}
  </tbody>
    
  </table>
@else 
  <div class = "alert alert-warning">No errors found</div>
@endif

<script>

  var state = false;

  $("#error_export").on('click', function ()
  {
    state = !state;
    $(".error_checkbox").prop('checked', state);
  });
  
  $("#export3").click(function() {
      var action = confirm("Are you sure you want to export the selected batches again?");
      if ( action ) {
        $("#export3_form").submit();
      }
   });
</script>