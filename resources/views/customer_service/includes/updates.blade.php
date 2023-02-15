@if($updates->count() > 0)

    <table class = "table" >
      <thead>
        <tr>
          <th>Order</th>
          <th>Marketplace</th>
          <th>User</th>
          <th>Date</th>
          <th>Note</th>
          <th>Order Total</th>
        </tr>
      </thead>

      <tbody>
        @foreach($updates as $note)
        <tr>
          <td>
            <a href = "{{ url("orders/details/" . $note->order_5p) }}"
               target = "_blank">{{ $note->order->short_order }}
            </a>
            {!! \App\Task::widget('App\Order', $note->order->id, null, 10); !!}
          </td>
          <td>
            @if ($note->order->store && $note->order->store->store_name != 'MonogramOnline.com')
              {{ $note->order->store->store_name }}
            @endif
          </td>
          <td>
            {{ $note->user->username }}
          </td>
          <td>
            {{ $note->created_at }}
          </td>
          <td>
            {{ $note->note_text }}
          </td>
          <td>
            ${{ number_format($note->order->total, 2) }}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

@else
    <div class = "alert alert-warning text-center">No Updates.</div>
@endif