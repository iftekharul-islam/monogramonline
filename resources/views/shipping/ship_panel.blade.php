@if($origin == 'QC')
  @setvar($items = $items)
@elseif($origin == 'WAP')
  @setvar($items = $bin->items)
@endif

@if (is_object($order->store) && $order->store->qc == '0' || auth()->user()->accesses->where('page', 'ship_order')->all())

  @setvar($btn_class = 'success')
  @setvar($url = '/shipping/ship_items')
  @setvar($shipping_methods = \Ship\Shipper::listMethods())

  @if ($order->carrier == 'MN')
    @setvar($btn_text = $order->method)
    @setvar($btn_class = 'info')
    @setvar($url = '/ship_order/ship_items')
  @elseif (count($items) > 1)
      @setvar($btn_text = count($items) . ' Lines Approved by ' . auth()->user()->username)
  @else
    @setvar($btn_text = 'Approved by ' . auth()->user()->username)
  @endif

  @if (count($order->shippable_items) != count($items))
    @setvar($btn_text = 'Partial Ship ' . $btn_text)
    @setvar($btn_class = 'default')
    @setvar($url = '/ship_order/ship_items')
  @endif

@else
  @setvar($btn_text = 'Ship by Supervisor')
  @setvar($btn_class = 'danger')
  @setvar($url = '#')
@endif

<style>
#packages td
  {
    border: none !important;
  }
</style>

<div class="col-xs-12 col-sm-12 col-md-12 panel panel-success">
    @if($order->carrier != null && $order->carrier != 'MN')
      <div class="panel-header">
        <h4>
        <strong>
          <u>Ship By {!! $shipping_methods[$order->carrier .'*'. $order->method] !!}</u>
        </strong>
        </h4>
      </div>
    @endif
  <div class="panel-body">
    <div class="col-xs-12 col-sm-4 col-md-2">
    @if ($order->carrier != null)
        @if ($order->carrier == 'FX')
            <img src="/assets/images/fedex.jpg" style="height: 100px;">
        @elseif ($order->carrier == 'UP' && !strpos($order->method, 'MAIL_INNOVATIONS'))
            <img src="/assets/images/ups.jpg" style="height: 100px;">
        @else
            <img src="/assets/images/usps.jpg" style="height: 100px;">
        @endif
    @else
      <strong>
          Ship to:
          {!! $shipping_methods[''] !!}
      </strong>
            <img src="/assets/images/usps.jpg" style="height: 100px;">
    @endif
    </div>
    <div class="col-xs-12 col-sm-8 col-md-5">
      {{ $order->customer->ship_full_name}}<br>
      @if (!empty($order->customer->ship_company_name))
        {{ $order->customer->ship_company_name}}<br>
      @endif
      {{ $order->customer->ship_address_1}}<br>
      @if (!empty($order->customer->ship_address_2))
        {{ $order->customer->ship_address_2}}<br>
      @endif
      {{ $order->customer->ship_city}}, {{ $order->customer->ship_state}} {{ $order->customer->ship_zip}}<br>
      @if (substr($order->customer->ship_country, 0, 2) != 'US')
        {{ $order->customer->ship_country}}
      @endif

      @if ($origin == 'QC')
         {!! Form::open(['url' => 'shipping/add_wap', 'name' => 'approve', 'method' => 'post', 'id' => 'approve']) !!}
         {!! Form::hidden('action', 'address', ['id' => 'action']) !!}
         {!! Form::hidden('batch_number', $batch->batch_number, ['id' => 'batch_number']) !!}
         {!! Form::hidden('id', $id, ['id' => 'id']) !!}
         {!! Form::hidden('order_id', $order->id, ['id' => 'order_id']) !!}
         {!! Form::hidden('origin', 'QC', ['id' => 'origin']) !!}
         {!! Form::hidden('count', count($items), ['id' => 'count']) !!}
         {!! Form::button('Bad Address' , ['id'=>'address', 'class' => 'btn btn-primary btn-xs', 'onclick' => 'this.disabled=true;this.form.submit();']) !!}
         {!! Form::close() !!}
      @else
        {!! Form::open(['url' => 'shipping/bad_address', 'name' => 'address', 'method' => 'post', 'id' => 'address']) !!}
        {!! Form::hidden('order_id',$order->id, ['id' => 'order_id']) !!}
        {!! Form::submit('Bad Address' , ['id'=>'address', 'class' => 'btn btn-primary btn-xs']) !!}
        {!! Form::close() !!}
      @endif

    </div>
    <div class="col-xs-12 col-sm-12 col-md-12" style="padding:0;">
      {!! Form::open(['url' => $url, 'method' => 'post']) !!}
        <div class="row">
            <div class="col-sm-6 pull-right">
                <table class="table table-condensed borderless" id="packages">
                    <tr>
                        <td>
                            @if ($order->carrier == 'US')
                                {!! Form::label('*Weight:', '', ['style' => 'color:red;']) !!}
                            @else
                                {!! Form::label('Weight:') !!}
                            @endif
                        </td>
                        <td>{!! Form::number('pounds[]', $weightLBS ?? 0 , ['id' => 'pounds', 'style' => 'width:50px', 'min' => '0']) !!}</td>
                        <td>lbs</td>
                        <td>{!! Form::number('ounces[]', $remainingOZS ?? 0, ['id' => 'ounces', 'style' => 'width:50px', 'min' => '0']) !!}</td>
                        <td>ozs</td>
                        <td>
                            @if ($order->carrier != null && $order->carrier != 'US' && $order->store->multi_carton == 1)
                                <a onclick='addPackage();'><i class = 'glyphicon glyphicon-plus'></i></a>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
      <br><br>
      @if(isset($batch))
        {!! Form::hidden('batch_number', $batch->batch_number, ['id' => 'batch_number']) !!}
      @endif
      @if(isset($id))
        {!! Form::hidden('id', $id, ['id' => 'id']) !!}
      @endif
      @if(isset($bin))
        {!! Form::hidden('bin', $bin->id, ['id' => 'bin']) !!}
      @endif
      {!! Form::hidden('order_id', $order->id, ['id' => 'order_id']) !!}
      {!! Form::hidden('selected-items-json', "", ['id' => 'selected-items-json']) !!}
      {!! Form::hidden('origin', $origin, ['id' => 'origin']) !!}
      {!! Form::hidden('location', 'FL', ['id' => 'location']) !!}
      {!! Form::hidden('count', count($items), ['id' => 'count']) !!}
      {!! Form::button($btn_text . ' (GA)', ['value' => 'fl', 'name' => 'submitButton', 'class' => 'btn btn-lg btn-' . $btn_class, 'id' => 'focus-btn', 'style' => 'margin-top:5px;', 'onclick' => 'setLocation("FL");this.disabled=true;this.form.submit();']) !!}
      {!! Form::button($btn_text . ' (NY)', ['value' => 'ny', 'name' => 'submitButton', 'class' => 'btn btn-lg btn-warning', 'id' => 'focus-btn', 'style' => 'margin-top:5px;', 'onclick' => 'setLocation("NY");this.disabled=true;this.form.submit();']) !!}
      {!! Form::close() !!}
    </div>
  </div>
</div>
<script type = "text/javascript">
  function setLocation(location) {
      try {
          var items = document.getElementById("allItems").value;
          document.getElementById("selected-items-json").value = items;
      } catch (e) {

      }
    $("#location").val(location);
  }
  function addPackage() {
      var row ='<tr><td></td> \
                <td><input id="pounds[]" style="width:50px" min="0" name="pounds[]" type="number" value="0"></td> \
                <td>lbs</td> \
                <td><input id="ounces[]" style="width:50px" min="0" name="ounces[]" type="number" value="0"></td> \
                <td>ozs</td> \
                <td></td></tr>';
      var parent = $("table#packages");
      $(parent).append($(row));
  }
</script>

@setvar($notificationItems = ['SB11306','SB11305','SB11304','SB11301','SB11253','SB11117','SB11107','SB10953','SB9051'])
@if(in_array($items->first()->item_code, $notificationItems))
<script type = "text/javascript">
window.onload = (event) => {
    $("#packingNotificationForm").modal('show');
};
</script>
@endif

<div class = "modal fade" id = "packingNotificationForm" tabindex = "-1" role = "dialog" aria-labelledby = "myModalLabel">
    <div class = "modal-dialog modal-sm" role = "document">
        <div class = "modal-content">
            <div class = "modal-header">
                <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
                    <span aria-hidden = "true">&times;</span></button>
                <h4 class = "modal-title" id = "myModalLabel">Packing Notification</h4>
            </div>

            <div class = "modal-body">
                <div>
                    Please Insert Pillow.
                </div>
                <div>
                    Inserte la almohada.
                </div>
            </div>
        </div>
    </div>
</div>
