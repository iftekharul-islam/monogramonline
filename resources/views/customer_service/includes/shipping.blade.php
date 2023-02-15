@if($shipping->count() > 0)

    <table class = "table" >
      <thead>
        <tr>
          <th>Order</th>
          <th>Order date</th>
          <th>Store</th>
          <th>Hold Type</th>
          <th width=250>Hold Reason</th>
          <th>Customer Name</th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        @foreach($shipping as $order)
        <tr>
          <td>
            <a href = "{{ url("orders/details/" . $order->id) }}"
               target = "_blank">{{ $order->short_order }}
            </a>
            {!! \App\Task::widget('App\Order', $order->id, null, 10); !!}
          </td>
          <td>
            {{ substr($order->order_date, 0, 10) }}
          </td>
          <td>
            @if ($order->store && $order->store->store_id != 52053152)
              {{ $order->store->store_name }}
            @endif
          </td>
          <td>
            {{ $statuses[$order->order_status] }}
            @if ($order->wap)
              <br>
              WAP: <a href="{{ url('/wap/details?bin=' . $order->wap->id) }}" target="_blank">{{ $order->wap->name }}</a>
            @endif
          </td>
          <td>
            @if ($order->order_status == 12)
              Hold Shipping until: {{ $order->ship_date }}
              @if ($order->hold_reason)
                <br>
              @endif
            @endif
            
            @if ($order->hold_reason)
              {{ str_replace('OH: Reason - ', '', $order->hold_reason->note_text)  }}
            @endif
          </td>
          <td>
            {{ $order->customer->ship_first_name }} {{ $order->customer->ship_last_name }}
          </td>
          <td>
            {!! Form::open(['class' => 'ajax_form', 'method' => 'get']) !!}
            {!! Form::hidden('tab', 'shipping') !!}
            {!! Form::hidden('order_5p', $order->id) !!}
            {!! Form::submit('Release Shipping Hold', ['class' => 'btn btn-xs btn-info']) !!}
            {!! Form::close() !!}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

@else
    <div class = "alert alert-warning text-center">No Shipping Holds.</div>
@endif