@if(isset($reject_summary) && count($reject_summary) > 0)
  <div class="col-md-6">
    <div class="row">
      <div class="col-md-2">
        {!! Form::open(['method' => 'get']) !!}
        {!! Form::hidden('tab', 'rejects') !!}
        {!! Form::text('reject_batch', '', ['class' => 'form-control', 'placeholder' => 'Find Batch']) !!}
        {!! Form::close() !!}
        <br>
      </div>
    </div>
    <table class="table table-bordered">
      <thead>
        <tr bgcolor="#dae6e6">
          <th width="400">Reason</th>
          <th width="200" style="text-align:right;">Count</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($reject_summary as $line)
          <tr>
            <td>
              @if (isset($reasons[$line->rejection_reason]))
                {{ $reasons[$line->rejection_reason] }}
              @endif
            </td>
            <td align="right">
              <a href="{{ url(sprintf('/customer_service/index?tab=rejects&rejection_reason=%d', $line->rejection_reason)) }}">
              {{ $line->count }}
              </a>
            </td>
          </tr>
        @endforeach
        <tr bgcolor="#dae6e6">
          <th style="text-align:right;">Total:</th>
          <th style="text-align:right;">{{ $reject_summary->sum('count') }}</th>
        </tr>
      </tbody>
    </table>
  </div>
@else
    <div class = "alert alert-warning">No Rejects.</div>
@endif