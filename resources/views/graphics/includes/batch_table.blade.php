<table class="table">
  <thead>
    
    @if ($type != 'no_form')
      {!! Form::open(['url' => 'graphics/export_batchbulk', 'id' => $type . '_form', 'method' => 'post']) !!}
      
      {!! Form::hidden('tab', $type) !!}
      {!! Form::hidden('force', '0') !!}
      <tr>
        <td colspan=11>
          {!! Form::button('Export Selected Batches' , ['id'=> $type . '_button', 'class' => 'btn btn-sm btn-primary']) !!}
        </td>
      </tr>  
    @endif
      
    <tr>
        @if ($type != 'no_form')
          <th style="width:30px;">
            <input type="checkbox" id="{{ $type }}_select" class="checkbox">	
          </th>
          <th style="width:150px;">Select All</th>
        @else
          <th style="width:150px;"></th>
        @endif
        
        <th colspan=6></th>
    </tr>
  </thead>

  <tbody>
    
  @foreach($batches as $batch) 
    
    <tr onclick="$('#barcode').val({{ $batch->batch_number }});"> 
    
      @if ($type != 'no_form')
        <td>
          <input type = "checkbox" name = "batch_number[]" class = "{{ $type }}_checkbox"
                 value = "{{ $batch->batch_number }}" />
        </td>
      @endif
      
      <td>
        <a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number)) }}">
                  {{ $batch->batch_number }}</a>
        <small>
        @if ($batch->status != 'active')
          <br>
          ({{ $batch->status }})
        @endif
        <br>
        @if (isset($sections[$batch->section_id]))
          {{ $sections[$batch->section_id] }} 
        @else
          Section Error
        @endif
        </small>
      </td>
      
      @if ($batch->first_item)
          <td>
            <span data-toggle = "tooltip" data-placement = "top"
                    title = "{{ $batch->first_item->child_sku }}">
              <img src = "{{ $batch->first_item->item_thumb }}" width = "70" height = "70" />
            </span>
          </td>
      @else
          <td> No Items </td>
      @endif
    
      <td>
        <table class="table table-condensed">
          <tr>
            <td>
              @if ($batch->export_count == 0)
                <i class = 'glyphicon glyphicon-remove'></i>
              @elseif ($batch->export_count == 1)
                <i class = 'glyphicon glyphicon-ok text-success'></i>
              @else
                {{ $batch->export_count }}
              @endif
            </td>
            <td>
              @if ($batch->export_count < 2)
                Export
              @else
                Exports
              @endif
            </td>
            <td>
              @if ($batch->csv_found)
                <i class = 'glyphicon glyphicon-ok text-success'></i>
              @else
                <i class = 'glyphicon glyphicon-remove'></i>
              @endif
            </td>
            <td>CSV Found</td>
          </tr>
          <tr>
            <td>
              @if ($batch->graphic_found == 'Found')
                <i class = 'glyphicon glyphicon-ok text-success'></i>
              @elseif ($batch->graphic_found == 'Not Found')
                <i class = 'glyphicon glyphicon-remove'></i>
              @else
                <i class = 'glyphicon glyphicon-remove text-danger'></i>
              @endif
            </td>
            <td>Graphic Found</td>

            <td>
              @if ($batch->summary_date)
                <i class = 'glyphicon glyphicon-ok text-success'></i>
              @else
                <i class = 'glyphicon glyphicon-remove'></i>
              @endif
            </td>
            <td>Summary Printed</td>
          </tr>
        </table>
      
      </td>
      
      <td>
        First Order: {{ substr($batch->min_order_date, 0, 10) }}
        <br><br>
        @if ($batch->itemsCount->first())
          {{ $batch->itemsCount->first()->count }} 
          @if($batch->itemsCount->first()->count == 1)
            Line
          @else
            Lines
          @endif
        @endif
      </td>
      
      <td align="right">
          {!! Form::button('Upload ' . $batch->batch_number . ' ***Graphic', ['class' => 'btn btn-success upload-btn', 'id' => $batch->batch_number]) !!}
      </td>
      
    </tr>
  @endforeach
  
  @if ($type != 'no_form')
    {!! Form::close() !!}
  @endif
  
</table>

<div class="modal fade" id="upload-modal" tabindex="-1" role="dialog" aria-labelledby="upload-modal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Upload a Graphic
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
        </h4>
      </div>
      {!! Form::open(['name' => 'track', 'url' => '/graphics/upload_file', 'method' => 'post', 'files'=>'true']) !!}
      {!! Form::hidden('batch_number', '', ['id' => 'upload_batch_number']) !!}
      <div class="modal-body">
        <p>
          Upload a graphic to the MAIN directory.
        </p>
        {!! Form::file('upload_file', ['class' => 'form-control']) !!}
      </div>
      <div class="modal-footer">
        {!! Form::submit('Upload', ['class' => 'btn btn-success']) !!}
      </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>

<script type = "text/javascript">

   $( document ).ready(function() {
     
     $(".upload-btn").on('click', function (e) {
              var batch_number = $(this).attr('id');
             $("#upload_batch_number").val(batch_number);
             $("#upload-modal").modal('show');
           }
        );
     
     var state = false;
     
     $("#{{ $type }}_select").on('click', function ()
     {
       state = !state;
       $(".{{ $type }}_checkbox").prop('checked', state);
     });
     
     $("#{{ $type }}_button").click(function() {
         var action = confirm("Are you sure you want to export the selected batches?");
         if ( action ) {
           $("#{{ $type }}_form").submit();
         }
      });
   });
   
</script>
