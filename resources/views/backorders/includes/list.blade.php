@if(count($result) > 0)
    <table class="table">
      <thead>
        <tr>
          <th colspan=5></th>
          <th colspan=4 style="text-align:center;background-color: #f2f2f2">Inventory Quantity</th>
          <th style="text-align:right;"></th>
        </tr>
      <tr>
        <th colspan=2>Stock #</th>
        <th>Batches</th>
        <th>Image</th>
        <th>Description</th>
        <th style="width:100px;background-color: #f5f5f5" class="data">On Hand</th>
        <th style="width:100px;background-color: #f5f5f5" class="data">Allocated</th>
        <th style="width:100px;background-color: #f5f5f5" class="data">Expected</th>
        <th style="width:100px;background-color: #f5f5f5" class="data">Available</th>
        <th style="width:100px;" class="data">Back Ordered</th>
      </tr>
    </thead>
        <tbody>

      @foreach($stock_nos as $stock_no)
        
        @if ($stock_no != 'ToBeAssigned' && $stock_no != NULL)
        
          @setvar($first_item = $result->where('stock_no_unique', $stock_no)->first())
          @setvar($stock_purchase = $purchases->where('stock_no', $stock_no)->all())
          
          @if ($first_item)
            <tr class="lines">
              <td>
                {!! \App\Task::widget('App\Inventory', $first_item->id, 'text-primary', 11); !!}
              </td>
              <td>
                <a href="{{url(sprintf("/inventories?search_for_first=%s&search_in_first=stock_no_unique", $stock_no))}}">{{ $stock_no }}</a>
              </td>
              <td class="data">
                  @setvar($batch_array = array_unique( $result->where('stock_no_unique', $stock_no)->pluck('batch_number')->all()))
                  @if ($batch_array != ['0'])
                    <a href="{{url(sprintf("/batches/list?batch=%s", implode(',', $batch_array)))}}">
                      {!! count($batch_array) !!}</a>
                  @endif
              </td>
              <td height="40">
                @if ($first_item->warehouse != NULL)
                  <img  border = "0" height="40" src = "{{ $first_item->warehouse }}" />
                @endif
              </td>
              <td>{{ $first_item->stock_name_discription }}</td>
              <td style="text-align:right;" class="data">{{ $first_item->qty_on_hand }}</td>
              <td style="text-align:right;" class="data">{{ $first_item->qty_alloc }}</td>
              <td style="text-align:right;" class="data">
                {{ $first_item->qty_exp }}
                @foreach ($stock_purchase as $purchase)
                        <br>
                        <a href="/purchases/{{ $purchase->purchase_id }}">
                          ETA: {{ isset($purchase->eta) ?  substr($purchase->eta, 5) : 'N/A' }}
                        </a>
                @endforeach
              </td>
              <td style="text-align:right;" class="data">{{ $first_item->qty_av }}</td>
              <td style="text-align:right;" class="data">
                  @setvar($bo_qty = $result->where('stock_no_unique', $stock_no)->sum('item_quantity'))
                  {{ $bo_qty }}
                  @if ($first_item->batch_number != '0')
                    {!! Form::open(['name' => 'arrived', 'method' => 'get', 'id' => 'arrived']) !!} 
                    {!! Form::hidden('stock_no', $stock_no, ['id' => 'stock_no']) !!} 
                    {!! Form::submit('Arrived' , ['id'=>'arrived', 'class' => 'btn btn-xs btn-primary']) !!} 
                    {!! Form::close() !!} 
                  @endif
              </td>
            </tr>
          @endif
        @else
            
            @setvar($items = $result->where('stock_no_unique', $stock_no)->groupBy('item_code')->all())
            
            @foreach($items as $item)
              <tr class="lines">
                <td>
                  {{ $stock_no }}
                </td>
                <td colspan=2>
                  @setvar($batch_array =  array_unique($result->where('stock_no_unique', $stock_no)->where('item_code', $item[0]['item_code'])->pluck('batch_number')->all())) 
                  @if ($batch_array != ['0'])
                    <a href="{{url(sprintf("/batches/list?batch=%s", implode(',', $batch_array)))}}">
                    {!! count($batch_array) !!}</a>
                  @endif
                </td>
                <td height="40">
                    <img  border = "0" height="40" src = "{{ $item[0]['item_thumb'] }}" />
                </td>
                <td colspan=5>{{ $item[0]['item_code'] }} <br> {{ $item[0]['item_description'] }}</td>
                <td style="text-align:right;">
                    @setvar($bo_qty = $item->sum('item_quantity'))
                    {{ $bo_qty }}
                    @if ($item[0]['batch_number'] != '0')
                      {!! Form::open(['name' => 'arrived', 'method' => 'get', 'id' => 'arrived']) !!} 
                      {!! Form::hidden('item_code', $item[0]['item_code'], ['id' => 'item_code']) !!} 
                      {!! Form::submit('Arrived', ['id'=>'arrived', 'class' => 'btn btn-xs btn-primary']) !!} 
                      {!! Form::close() !!} 
                    @endif
                </td>
              </tr>
            @endforeach
            
        @endif
        
      @endforeach

    </tbody>
      
    </table>

@else
    <br>
    <div class = "alert alert-warning text-center">No Back Orders</div>
@endif
