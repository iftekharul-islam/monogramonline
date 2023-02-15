@if(isset($bo_summary) && count($bo_summary) > 0)
  
  <table class ="table">
    <thead>
      <tr>
        <th>Quantity</th>
        <th>Description</th>
        <th>First Order Date</th>
        <th>Inventory Item</th>
      </tr>
    </thead>

    <tbody>
      @foreach($bo_summary as $row)
      <tr>
        <td class="clickable-cell" align="center"
          data-href="{{ url('/customer_service/index?tab=backorder&stock_no_unique=' . $row->stock_no_unique) }}">
          <a href="{{ url('/customer_service/index?tab=backorder&stock_no_unique=' . $row->stock_no_unique) }}"
            ><strong>{{ $row->qty }}</strong></a>
        </td>
        <td>
          {{ $row->stock_name_discription }}
        </td>
        <td>
          {{ substr($row->min_date, 0, 10) }}
        </td>
        <td>
          <a href = "{{ url('/inventories?operator_first=equals&search_in_first=stock_no_unique&search_for_first=' . $row->stock_no_unique) }}"
            target = "_blank">{{ $row->stock_no_unique }}</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

@elseif(count($backorders) > 0)
    
    <table class ="table">
      <thead>
        {!! Form::open(['url' => url('/customer_service/bulk_email'), 'method' => 'get']) !!}
        {!! Form::hidden('id_type', '5p') !!}
        <tr>
          <th width=75>
            <a href = "{{ url('/customer_service/index?tab=backorder') }}"
                data-toggle = "tooltip" data-placement = "top"
                title = "Back to Summary">
                <i class = 'glyphicon glyphicon-arrow-left text-primary' style="font-size:20px;"></i>
              </a>
          </th>
          <th width=50>
            <input type = "checkbox" name = "bo_select" class = "checkbox" id="select_deselect"/>
          </th>
          <th>
            {!! Form::submit('Send Bulk Email',['class'=>'btn btn-sm btn-primary']) !!}
          </th>
          <th width=150>Order</th>
          <th width=150>Date</th>
          <th>Customer</th>
          <th></th>
          <th>Item</th>
        </tr>
      </thead>

      <tbody>
        
        @foreach($backorders as $item)
        <tr>
          <td></td>
          <td>
            <input type = "checkbox" name = "order_ids[]" class = "checkbox"
                   value = "{{ $item->order_5p }}" />
          </td>
          <td>
              <a href = "{{ url('/inventories?operator_first=equals&search_in_first=stock_no_unique&search_for_first=' . $item->stock_no_unique) }}"
                target="_blank">{{ $item->stock_no_unique }}</a>
          </td>
          <td>
            <a href = "{{ url("orders/details/" . $item->order_5p) }}"
               target = "_blank">{{ $item->order->short_order }}</a>
             {!! \App\Task::widget('App\Order', $item->order->id, null, 10); !!}
          </td>
          <td>
            {{substr($item->order->order_date, 0, 10)}}
          </td>
          <td>
            {{ $item->order->customer->ship_first_name }} {{ $item->order->customer->ship_last_name }}<br>
          </td>
          <td>
            @setvar($emails = 0)
            
            @foreach($item->order->notes as $note)
              @if(strpos(strtolower($note->note_text), 'back order notification'))
                @if($emails == 0)
                  <strong>Back Order Notifications Sent:</strong><br>
                  @setvar($emails = 1)
                @endif
                <span data-toggle = "tooltip" data-placement = "top" 
                      title = 
                      "{{ $note->note_text }} 
                      Sent By {{ $note->user->username }}">
                      {!! substr($note->created_at, 0 , 10) !!}</span>
                 <br>
              @endif
            @endforeach
            
            <button type = "button" class = "btn btn-link" data-toggle = "modal"
                    data-target = "#large-email-modal-lg" 
                    onclick="open_email({{ $item->order->id }},'{{ $item->order->short_order }}','{{ $item->order->customer->bill_email }}', 28)">
              <i class = "glyphicon glyphicon-envelope"></i>
            </button>
          </td>
          <td>
            {{ $item->item_description }}
            <br>
            SKU: {{ $item->child_sku }}
          </td>
          
        </tr>
        @endforeach
        <tr>
          <td></td>
          <td colspan=2>
            
          </td>
          <td colspan=5></td>
        </tr>
        {!! Form::close() !!}
        
      </tbody>
    </table>

@else
    <div class = "alert alert-warning">No Backorders.</div>
@endif

<script type = "text/javascript">

  $(".clickable-cell").click(function() {
      window.location = $(this).data("href");
  });
  
  var state = false;

  $("#select_deselect").on('click', function ()
  {
    state = !state;
    $(".checkbox").prop('checked', state);
  });
  
</script>