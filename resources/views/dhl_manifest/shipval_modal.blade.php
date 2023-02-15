
{{--!! Form::button('approve' , ['id'=>'approve', 'class' => 'btn btn-sm btn-danger']) !!--}}


<div class = "modal fade" id = "validate-modal" tabindex = "-1" role = "dialog" aria-labelledby = "myModalLabel">
  <div class = "modal-dialog" role = "document">
    <div class = "modal-content">
      <div class = "modal-header">
        <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
          <span aria-hidden = "true">&times;</span></button>
        <h4 class = "modal-title" id = "myModalLabel">Enter passcode to ship Monogram at Home</h4>
      </div>
      <div class = "modal-body">
        <div class = "form-group">
          {!! Form::textarea('passcode', null, ['id' => 'passcode', 'rows' => 1,  'cols' => '5', 'class' => 'form-control']) !!}
        </div>
      </div>
      <div class = "modal-footer">
        <button type = "button" class = "btn btn-default" data-dismiss = "modal">Close</button>
        <button type = "button" class = "btn btn-danger" id = "do-ship">Ship</button>
      </div>
    </div>
  </div>
</div>

<script type = "text/javascript">
var form = null;

$('button[id^="MAH"]').on('click', function (event)
{
  form = $(this).closest('form');
  event.preventDefault();
  $("#validate-modal").modal('show');
});

$('button[id^="ship"]').on('click', function (event)
{
  $(this).closest('form').submit();
  
});

$("#do-ship").on('click', function ()
{
  var passcode = $("#passcode").val();
  if ( !passcode || passcode == 0 || passcode != '7254' ) {
    alert('Incorrect passcode');
    return false;
  }
  
  $(form).submit();
  
});

</script>