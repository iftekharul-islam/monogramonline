@if($incompatible->count() > 0)

    <table class = "table" >
      <thead>
        <tr>
          <th>Order</th>
          <th>Order date</th>
          <th>Customer Name</th>
          <th>Item</th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        @foreach($incompatible as $order)
        <tr>
          <td>
            <a href = "{{ url("orders/details/" . $order->id) }}"
               target = "_blank">{{ $order->short_order }}
            </a>
              {!! \App\Task::widget('App\Order', $order->id, null, 10); !!}
          </td>
          <td>
            {{substr($order->order_date, 0, 10)}}
          </td>
          <td>
            {{ $order->customer->ship_first_name }} {{ $order->customer->ship_last_name }}
          </td>
          <td>
            @foreach ($order->items as $item) 
              @if (count($order->items) == 1 || $item->item_option == '[]' || $item->item_option == '0') 
                {{ $item->child_sku }} : {{ $item->item_description }} <br>
              @endif
            @endforeach
          </td>
          <td>
            {!! Form::open(['class' => 'ajax_form', 'method' => 'get']) !!}
            {!! Form::hidden('tab', 'incompatible') !!}
            {!! Form::hidden('order_5p', $order->id) !!}
            {!! Form::submit('Release Options Hold', ['class' => 'btn btn-xs btn-info']) !!}
            {!! Form::close() !!}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

@else
    <div class = "alert alert-warning text-center">No Incompatible Options Holds.</div>
@endif