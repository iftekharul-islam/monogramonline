@if($reship->count() > 0)

    <table class ="table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Date</th>
          <th>Customer</th>
          <th>Shipping Address</th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        @foreach($reship as $order)
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
            @if ($order->customer)
              {{ $order->customer->ship_first_name }} {{ $order->customer->ship_last_name }}<br>
            @else
              Customer Error
            @endif
            
            @setvar($emails = 0)
            
            @foreach($order->notes as $note)
              @if(strpos(strtolower($note->note_text), 'confirm address'))
                @if($emails == 0)
                  <strong>Verification Emails Sent:</strong><br>
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
                    onclick="open_email({{ $order->id }},'{{ $order->short_order }}','{{ $order->customer->bill_email }}', 17)">
              <i class = "glyphicon glyphicon-envelope"></i>
            </button>
          </td>
          <td>
              @if ($order->customer)
               {{ $order->customer->ship_full_name }}<br>
               {{ $order->customer->ship_address_1 }}, {{ $order->customer->ship_address_2 }}<br>
               {{ $order->customer->ship_city }},  {{ $order->customer->ship_state }},  {{ $order->customer->ship_zip }}
               @if (substr($order->customer->ship_country, 0, 2) != 'US')
                <br>{{ $order->customer->ship_country }}
              @endif
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

@else
    <div class = "alert alert-warning">No Reshipments</div>
@endif