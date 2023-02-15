<table class = "table">
  <tbody>

  @foreach ($reject_batches as $batch => $items)
      
      {!! Form::open(['url' => url('/customer_service/csProcess'), 'method' => 'get', 'id' => 'form-process-' . $batch]) !!}
      
      {!! Form::hidden('batch_number', $batch, ['id' => 'batch_number']) !!}
    
      <tr><td colspan="6"></td></tr>
      
      <tr>
        <td rowspan="{!! count($items) !!}"  bgcolor="#cbdcdc" class="noline">
        <strong>Batch:</strong> <br>
        <a href = "{{url(sprintf('/batches/details/%s', $batch)) }}"
             target = "_blank">{{ $batch }}</a>
        <br>
        {!! \App\Task::widget('App\Batch', $items[0]->batch->id, 'text-primary', 12); !!}
        </td>

      @foreach($items as $item)

          <td bgcolor="#dae6e6">
            <span data-toggle = "tooltip" data-placement = "top" 
                  title = "Order: {{ $item->order->id }}
                            Status: {{ $item->order->order_status }}
                            Date: {{substr($item->order->order_date, 0, 10)}}">
                  <a href = "{{url(sprintf('/orders/details/%s', $item->order->id))}}"
                       target = "_blank">{{ $item->order->short_order }}</a></span>
            <br>
            
              Item: {{$item->id }}
            <br>
            @if ($item->item_quantity > 1)
              <strong style="font-size: 125%;">QTY: {{ $item->item_quantity }}</strong>
            @else
              QTY: {{ $item->item_quantity }}
            @endif
            <br>
            
            <br>
            @if (count($items) > 1)
              <a href="{{url(sprintf('/rejections/split?item_id=%d&batch_number=%s', $item->id, $item->batch_number))}}" class="btn btn-xs">
                New Batch</a>
            @endif
          </td>

        
          <td>
            <a href = "{{ $item->item_url }}" target = "_blank">
            <img src = "{{ $item->item_thumb }}" width="90" height="90"></a>
          </td>
        
          <td>
              {{ $item->item_description }}
              <br>
              SKU: {{ $item->child_sku }}
              <br>
              <br>
              <strong>Rejected:</strong> {{ $item->rejection->created_at }} from {{ $item->rejection->from_station->station_name }} by {{ $item->rejection->user->username }}
              
              @if($item->rejection)
                <br> <strong>Status:</strong>{{ $item->rejection->graphic_status }}
                <br> <strong>Reason:</strong>
                  @if ($item->rejection->rejection_reason_info)
                    {{ $item->rejection->rejection_reason_info->rejection_message }} 
                  @endif
                <br> <strong>Note:</strong>{{ $item->rejection->rejection_message }}
              @else
                -
              @endif
              
              @if($item->rejection->supervisor_message) 
                <br><strong>Supervisor:</strong>{{ $item->rejection->supervisor_message }} <br>
              @endif
              
              {!! Form::text('supervisor_message['  . $item->rejection->id . ']', null, ['class' => 'supervisor_message form-control', 'style' => 'min-width: 200px;', 'placeholder' => 'Enter a message']) !!}

          </td>
          <td class="divline" colspan=2>{{ \Monogram\Helper::jsonTransformer($item->item_option) }}</td>
        </tr>
      @endforeach
      <tr bgcolor="#cbdcdc">
        <td colspan="4"></td>
        <td align="center">
            {!! Form::checkbox('solved', null, 0) !!} Issues Solved
        </td>
        <td width="150">
          {!! Form::submit('Update Batch ' . $batch, ['id'=>'process-' . $batch, 'class' => 'btn btn-primary btn-sm form-control']) !!}
        </td>
      </tr>
      {!! Form::close() !!}
  @endforeach
  </tbody>
</table>