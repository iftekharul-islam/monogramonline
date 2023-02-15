{{--!! Form::open(['name' => 'reject-' . $item->id, 'url' => '/reject_item', 'method' => 'get', 'id' => 'reject']) !!--}}
{{--!! Form::hidden('item_id', $item->id, ['id' => 'item_id']) !!--}}
{{--!! Form::hidden('quantity', $item->item_quantity, ['id' => 'quantity']) !!--}}
{{--!! Form::hidden('origin', WAP or QC or PR, ['id' => 'origin']) !!--}}
{{--!! Form::button('Reject' , ['id'=>'reject', 'class' => 'btn btn-sm btn-danger']) !!--}}
{{--!! Form::close() !!--}}

@setvar($graphic_statuses = App\Rejection::graphicStatus())
@setvar($rejection_reasons = App\RejectionReason::getReasons())

<div class = "modal fade" id = "rejection-modal" tabindex = "-1" role = "dialog" aria-labelledby = "myModalLabel">
  <div class = "modal-dialog" role = "document">
    <div class = "modal-content">
      <div class = "modal-header">
        <button type = "button" class = "close modal-close" data-dismiss = "modal" aria-label = "Close">
          <span aria-hidden = "true">&times;</span></button>
        <h4 class = "modal-title" id = "myModalLabel">Reject Information</h4> 
      </div>
      <div class = "modal-body">
        <h5>Inventory</h5>
        <div class="btn-group" data-toggle="buttons">
          
          <label class="btn btn-default btn-lg" style="width:280px;" id="scrap_1" value="1">
            <span class="glyphicon glyphicon-trash"></span>
            <input type="radio" name="scrap">Defective Item Produced
          </label>
        
          <label class="btn btn-default btn-lg" style="width:280px;" id="scrap_2" value="0">
            <input type="radio" name="scrap">Nothing Produced 
          </label>
          
        </div>
        
        <div><hr></div>
        
        <div class="btn-group" data-toggle="buttons">
          <h5>Graphic</h5>
          <label class="btn btn-default btn-lg" style="width:180px;" id="graphic_label_1" value="1">
            <span class="glyphicon glyphicon-print"></span>
            <input type="radio" name="graphic_status">Print Again
          </label>
        
          <label class="btn btn-default btn-lg" style="width:180px;" id="graphic_label_2" value="2">
            <span class="glyphicon glyphicon-pencil"></span>
            <input type="radio" name="graphic_status">Edit Graphic
          </label>
          
          <label class="btn btn-default btn-lg" style="width:205px;" id="graphic_label_3"  value="4">
            <span class="glyphicon glyphicon-envelope"></span>
            <input type="radio" name="graphic_status">Contact Customer
          </label>
        </div>
        
        <div><hr></div>
        
        <div class = "form-group" id="qty_group">
           Quantity to Reject:  {!! Form::number('reject_qty', null, [ 'id' => 'reject_qty',  'min' => '1']) !!}
        </div>
        
        <div class = "form-group">
          {!! Form::select('reason_to_reject', $rejection_reasons ?: [], null, ['id' => 'reason-to-reject', 'class' => 'form-control']) !!}
        </div>
        <div class = "form-group">
          {!! Form::textarea('message_to_reject', null, ['id' => 'message-to-reject', 'rows' => 2, 'class' => 'form-control', 'placeholder' => 'More Information']) !!}
        </div>
      </div>
      <div class = "modal-footer">
        <button type = "button" class = "btn btn-default btn-lg modal-close" data-dismiss = "modal">Close</button>
        <button type = "button" class = "btn btn-danger btn-lg" id = "do-reject">Reject</button>
      </div>
    </div>
  </div>
</div>

<script type = "text/javascript">
var form = null;
var scrap = null;
var graphic_status = null;

$('label[id^="graphic_label"]').on('click', function (event)
{
  event.preventDefault();
  $('label[id^="graphic_label"]').removeClass().addClass('btn btn-default btn-lg');
  $(this).removeClass('btn-default').addClass('btn-info');
  graphic_status = $(this).attr("value");
});

$('label[id^="scrap"]').on('click', function (event)
{
  event.preventDefault();
  $('label[id^="scrap"]').removeClass().addClass('btn btn-default btn-lg');
  $(this).removeClass('btn-default').addClass('btn-info');
  scrap = $(this).attr("value");
});

$('button[id^="reject"]').on('click', function (event)
{
  $(this).prop('disabled', true);
  form = $(this).closest('form');
  event.preventDefault();
  id = $(this).attr('id');
  pos = id.indexOf("-");
  qty = id.slice(pos + 1);

  $("#reject_qty").val(qty);
  if (qty == 1) {
    $("#qty_group").hide();
  } else {
    $("#reject_qty").attr({"max" : qty});
  }
  $("#rejection-modal").modal('show');
});


$("#do-reject").on('click', function ()
{ 
  if(scrap == null) { 
    alert('Was a Defective Item Produced?');
    return false;
  }
  
  if(graphic_status == null) { 
    alert('Reprint Graphic, Edit Graphic or Contact Customer?');
    return false;
  }
  
  var rejection_reason = $("#reason-to-reject").val();
  if ( !rejection_reason || rejection_reason == 0 ) {
    alert('Please select a rejection reason.');
    return false;
  }
  
  $(this).prop('disabled', true);
  
  $(form).append("<input type='hidden' name='scrap' value='"+ scrap +"' />");
                    
  $(form).append("<input type='hidden' name='graphic_status' value='"+ graphic_status +"' />");
                    
  $(form).append("<input type='hidden' name='rejection_reason' value='"+ rejection_reason +"' />");

  $(form).append("<input type='hidden' name='rejection_message' value='"+ $("#message-to-reject").val() +"' />");
  
  $(form).append("<input type='hidden' name='reject_qty' value='"+ $("#reject_qty").val() +"' />");
                                      
  $(form).append("<input type='hidden' name='title' value='"+ document.title +"' />");
  
  $(form).submit();
  
});

$(".modal-close").on('click', function ()
{ 
  $('button[id^="reject"]').prop('disabled', false);
});

</script>