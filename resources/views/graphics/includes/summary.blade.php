<style>
  tr {
    font-size: 11px;
  }
  tr.toplabel {
    text-align: center;
    background-color: #f2f2f2;
  }
  tr.lines:nth-child(even) {
    background-color: #f2f2f2;
  }
  tr.lines:hover {
    background-color: #FEF9E7;
  }
  td th {
    table-layout: fixed;
    width: auto;
    white-space: nowrap;
  }
  .right {
    text-align: right;
  }
  .data {
    border-left: 1px solid #ddd;
    text-align: right;
  }
  .databorder {
    border-left: 3px solid #ddd;
    text-align: right;
  }
  .total {
    border-left: 1px solid #ddd;
    text-align: right;
    font-weight: bold;
  }
  .totalborder {
    border-left: 3px solid #ddd;
    text-align: right;
    font-weight: bold;
  }
  .data_late {
    border-left: 1px solid #ddd;
    text-align: right;
  }
  .data_late a:link {
    color: #FF0000;
  }
  .remove_button_css { 
    outline: none;
    padding: 0px; 
    border: 0px; 
    box-sizing: none; 
    background-color: transparent;
    color: #428bca;
  }
</style>

<div class="col-xs-8">
  <table id="summary_table" class="table" cellspacing="0" cellpadding="0">
    <thead>
    <tr class="toplabel">
      <td colspan="2" align="left">{{ $now }}</td>
      <td colspan="2">Totals</td>
      <td colspan="3">Order Date Aging</td>
      <td colspan="3">Scan Date Aging</td>
    </tr>
    <tr>
      <th width="10%">Station</th>
      <th width="25%">Description</th>
      <th width="8%" class="right">lines</th>
      <th width="8%" class="right">Qty</th>
      <th width="8%" class="right">0-3</th>
      <th width="8%" class="right">4-7</th>
      <th width="8%" class="right">7+</th>
      <th width="8%" class="right">0-3</th>
      <th width="8%" class="right">4-7</th>
      <th width="8%" class="right">7+</th>
    </tr>
  </thead>
      <tbody>


    @if (count($rejects) > 0)
      <tr class="success">
        <td colspan=9>Rejects</td>
        <td align="right">{{ sprintf("%4.2f", $rejects->sum('items_count') / $total * 100) }}%</td>
      </tr>
      
      @foreach($rejects as $reject)
      <tr class="lines">
        <td>{{ $reject->section_name }}
        <td>{{ $graphic_statuses[$reject->graphic_status] }}
        <td class="data"><a href="/rejections?graphic_status={{ $reject->graphic_status }}&section={{ $reject->section_id }}">{{ number_format($reject->lines_count) }}</a></td>
        <td class="data">{{ number_format($reject->items_count) }}</td>
        <td class="databorder">{{ number_format($reject->order_1) }}</td>
        <td class="databorder">{{ number_format($reject->order_2) }}</td>
        <td class="databorder">{{ number_format($reject->order_3) }}</td>			
        <td class="databorder">{{ number_format($reject->scan_1) }}</td>
        <td class="databorder">{{ number_format($reject->scan_2) }}</td>
        <td class="databorder">{{ number_format($reject->scan_3) }}</td>		
      </tr>
      @endforeach
      
      <tr>
        <td></td>
        <td align="right">Reject SubTotals:</td>
        <td class="total">{!! number_format($rejects->sum('lines_count')) !!}</td>
        <td class="total">{!! number_format($rejects->sum('items_count')) !!}</td>
        <td class="totalborder">{!! number_format($rejects->sum('order_1')) !!}</td>
        <td class="total">{!! number_format($rejects->sum('order_2')) !!}</td>
        <td class="total">{!! number_format($rejects->sum('order_3')) !!}</td>
        <td class="totalborder">{!! number_format($rejects->sum('scan_1')) !!}</td>
        <td class="total">{!! number_format($rejects->sum('scan_2')) !!}</td>
        <td class="total">{!! number_format($rejects->sum('scan_3')) !!}</td>
      </tr>
    @endif
    
  @if (count($items) > 0)
    @foreach($items as $summary) 
      @if ($section != $summary->section_id)
        @if ($section != 'start')
        <tr>
          <td></td>
          <td align="right">{{ $section_name }} SubTotals: </td>
          <td class="total">{!! number_format($items->where('section_id', $section)->sum('lines_count')) !!}</td>
          <td class="total">{!! number_format($items->where('section_id', $section)->sum('items_count')) !!}</td>
          <td class="totalborder">{!! number_format($items->where('section_id', $section)->sum('order_1')) !!}</td>
          <td class="total">{!! number_format($items->where('section_id', $section)->sum('order_2')) !!}</td>
          <td class="total">{!! number_format($items->where('section_id', $section)->sum('order_3')) !!}</td>
          <td class="totalborder">{!! number_format($items->where('section_id', $section)->sum('scan_1')) !!}</td>
          <td class="total">{!! number_format($items->where('section_id', $section)->sum('scan_2')) !!}</td>
          <td class="total">{!! number_format($items->where('section_id', $section)->sum('scan_3')) !!}</td>
        </tr>
        @endif
        
        @setvar($section = $summary->section_id)
        @setvar($section_name = $summary->section_name)
        
        <tr class="success">
          @if ($summary->section_id == '0')
            <td colspan="9">Unassigned</td>
          @else
            <td colspan="9">{{ $summary->section_name }}</td>
          @endif
          <td align="right">{{ sprintf("%4.2f", $items->where('section_id', $section)->sum('items_count') / $total * 100) }}%</td>
        </tr>
      @endif
      <tr class="lines">
        <td>
          <a href = "{!! url(sprintf("/production/status_detail?station=%s", $summary->station_id)) !!}" target = "_blank">{{ $summary->station_name }}</a>
        </td>
        <td>
          {{ $summary->station_description }}
        </td>
        <td class="data">
          {!! Form::open(['method' => 'post', 'url' => '/move_next', 'target' => '_blank']) !!}
          {!! Form::hidden('station',  $summary['station_id']) !!}
          {!! Form::submit(number_format($summary->lines_count), ['class' => 'remove_button_css']) !!}
          {!! Form::close() !!}
        </td>
        <td class="data">{{ number_format($summary->items_count) }}</td>
        <td class="databorder">
            @if ($summary->order_1 > 0)
              <a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&status=1", 
                            $summary->station_name, $date[1], $date[0])) !!}" target="_blank">{{ number_format($summary->order_1) }}</a>
            @else 
              {{ $summary->order_1 }}
            @endif
        </td>
        <td class="data">
          @if ($summary->order_2 > 0)
            <a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&status=1", 
                          $summary->station_name, $date[3], $date[2])) !!}" target="_blank">{{ number_format($summary->order_2) }}</a>
          @else 
            {{ $summary->order_2 }}
          @endif
        </td>
        <td class="data">
          @if ($summary->order_3 > 0)
            <a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&status=1", 
                          $summary->station_name, $summary->earliest_order_date, $date[4])) !!}" target="_blank">{{ number_format($summary->order_3) }}</a>
          @else 
            {{ $summary->order_3 }}
          @endif
        </td>
              
        <td class="databorder">
          @if ($summary->scan_1 > 0)
            <a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&status=1", 
                          $summary['station_name'], $date[1], $date[0])) !!}" target = "_blank">{{ number_format($summary->scan_1) }}</a>
          @else 
              {{ $summary->scan_1 }}
          @endif
        </td>
        <td class="data">
          @if ($summary->scan_2 > 0)
            <a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&status=1", 
                          $summary['station_name'], $date[3], $date[2])) !!}" target = "_blank">{{ number_format($summary->scan_2) }}</a>
          @else 
              {{ $summary->scan_2 }}
          @endif
        </td>
        <td class="data">
          @if ($summary->scan_3 > 0)
            <a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&status=1", 
                          $summary['station_name'], $summary->earliest_scan_date, $date[4])) !!}" target = "_blank">{{ number_format($summary->scan_3) }}</a>
          @else 
              {{ $summary->scan_3 }}
          @endif
        </td>
      </tr>
    @endforeach
    
      <tr>
        <td></td>
        @if ($section == '0')
          <td align="right">Unassigned SubTotals:</td>
        @else
          <td align="right">{{ $section_name }} SubTotals:</td>
        @endif
        <td class="total">{!! number_format($items->where('section_id', $section)->sum('lines_count')) !!}</td>
        <td class="total">{!! number_format($items->where('section_id', $section)->sum('items_count')) !!}</td>
        <td class="totalborder">{!! number_format($items->where('section_id', $section)->sum('order_1')) !!}</td>
        <td class="total">{!! number_format($items->where('section_id', $section)->sum('order_2')) !!}</td>
        <td class="total">{!! number_format($items->where('section_id', $section)->sum('order_3')) !!}</td>
        <td class="totalborder">{!! number_format($items->where('section_id', $section)->sum('scan_1')) !!}</td>
        <td class="total">{!! number_format($items->where('section_id', $section)->sum('scan_2')) !!}</td>
        <td class="total">{!! number_format($items->where('section_id', $section)->sum('scan_3')) !!}</td>
      </tr>

      </tbody>
      
      <tfoot>
        <tr class="success">
          <td colspan=10 height=30></td>
        </tr>
        <tr class="total_footer">
          <td></td>
          <td align="right"><strong>Graphics SubTotals:</strong></td>
          <td class="total">{!! number_format($items->sum('lines_count') +  $rejects->sum('lines_count')) !!}</td>
          <td class="total">{!! number_format($items->sum('items_count') +  $rejects->sum('items_count')) !!}</td>
          <td class="totalborder">{!! number_format($items->sum('order_1') +  $rejects->sum('order_1')) !!}</td>
          <td class="total">{!! number_format($items->sum('order_2') +  $rejects->sum('order_2')) !!}</td>
          <td class="total">{!! number_format($items->sum('order_3') +  $rejects->sum('order_3'))  !!}</td>
          <td class="totalborder">{!! number_format($items->sum('scan_1') +  $rejects->sum('scan_1')) !!}</td>
          <td class="total">{!! number_format($items->sum('scan_2') +  $rejects->sum('scan_2')) !!}</td>
          <td class="total">{!! number_format($items->sum('scan_3') +  $rejects->sum('scan_3')) !!}</td>
        </tr>
  @endif

  @if (count($unbatched) > 0)
        <tr class="total_footer">
              <td></td>
              <td align="right">Unbatched:</td>
              <td class="data">{{ number_format($unbatched->lines_count) }}</td>
              <td class="data">{{ number_format($unbatched->items_count) }}</td>
              <td class="databorder">
                @if ($unbatched->order_1 > 0)
                  <a href="{!! url(sprintf("/items?start_date=%s&end_date=%s&unbatched=1&status=0", $date[1], $date[0])) !!}" 
                            target = "_blank">{{ number_format($unbatched->order_1) }}</a>
                @else 
                  {{ $unbatched->order_1 }}
                @endif
              </td>
              <td class="data">
                @if ($unbatched->order_2 > 0)
                  <a href="{!! url(sprintf("/items?start_date=%s&end_date=%s&unbatched=1&status=0", $date[3], $date[2])) !!}" 
                            target = "_blank">{{ number_format($unbatched->order_2) }}</a>
                @else 
                  {{ $unbatched->order_2 }}
                @endif
              </td>
              <td class="data">
                @if ($unbatched->order_3 > 0)
                  <a href="{!! url(sprintf("/items?start_date=%s&end_date=%s&unbatched=1&status=0", $unbatched->earliest_order_date, $date[4])) !!}" 
                            target = "_blank">{{ number_format($unbatched->order_3) }}</a>
                @else 
                  {{ $unbatched->order_3 }}
                @endif
              </td>
              <td class="databorder" colspan="3"></td>
        </tr>
  @endif

  @if (count($items) > 0)
        <tr class="total_footer">
          <td></td>
          <td align="right"><strong>Graphics Totals:</strong></td>
          <td class="total">{!! number_format($items->sum('lines_count') +  $rejects->sum('lines_count') + $unbatched->lines_count) !!}</td>
          <td class="total">{!! number_format($items->sum('items_count') +  $rejects->sum('items_count') + $unbatched->items_count) !!}</td>
          <td class="totalborder">{!! number_format($items->sum('order_1') +  $rejects->sum('order_1') + $unbatched->order_1) !!}</td>
          <td class="total">{!! number_format($items->sum('order_2') +  $rejects->sum('order_2') + $unbatched->order_2) !!}</td>
          <td class="total">{!! number_format($items->sum('order_3') +  $rejects->sum('order_3') + $unbatched->order_3)  !!}</td>
          <td class="totalborder">{!! number_format($items->sum('scan_1') +  $rejects->sum('scan_1')) !!}</td>
          <td class="total">{!! number_format($items->sum('scan_2') +  $rejects->sum('scan_2')) !!}</td>
          <td class="total">{!! number_format($items->sum('scan_3') +  $rejects->sum('scan_3')) !!}</td>
        </tr>
  @endif

  <tr class="success">
    <td colspan=10 height="30"></td>
  </tr>


  </tfoot>
  </table>
</div>