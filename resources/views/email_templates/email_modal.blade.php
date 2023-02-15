
<link type = "text/css" rel = "stylesheet" href = "/assets/css/nprogress.css">

<script src = "//cdn.ckeditor.com/4.5.9/standard/ckeditor.js"></script>
<script type = "text/javascript" src = "/assets/js/nprogress.js"></script>

@setvar($message_types = App\EmailTemplate::where('is_deleted', 0)->get()->pluck('message_type', 'id')->prepend('Email', '0'))

<div class = "row">
  <div class = "col-md-12">
    <div class = "modal fade bs-example-modal-lg" id = "large-email-modal-lg" tabindex = "-1" role = "dialog"
         aria-labelledby = "large-modal">
      <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
          <div class = "modal-header">
            <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
              <span aria-hidden = "true">×</span>
            </button>
            <h4 class = "modal-title">Send email to customer</h4>
          </div>
          <div class = "modal-body">
            {!! Form::open(['url' => '/orders/send_mail', 'id' => 'email-sender-form']) !!}
            {!! Form::hidden('order_5p', null, ['id' => 'email-orderid']) !!}
            <table class = "table table-bordered">
              <tr>
                <td>Email</td>
                <td>{!! Form::text('recipient', null, ['id' => 'email-recipient', 'class' => 'form-control']) !!}</td>
              </tr>
              <tr>
                <td>Message type</td>
                <td>
                  {!! Form::select('message_types', $message_types, null, ['id' => 'message-types', 'class' => 'form-control']) !!}
                </td>
              </tr>
              <tr>
                <td>Subject</td>
                <td> {!! Form::text('subject', null, ['id' => 'email-subject', 'class' => 'form-control']) !!} </td>
              </tr>
              <tr>
                <td></td>
                <td>
                  {!! Form::textarea('message', null, ['id' => 'email-message', 'class' => 'form-control' ]) !!}
                </td>
              </tr>
            </table>
            {!! Form::close() !!}
          </div>
          <div class = "modal-footer">
            <button type = "button" class = "btn btn-default" data-dismiss = "modal"
                    id = "dismiss-email">Close
            </button>
            <button type = "button" class = "btn btn-primary" id = "send-email">Send</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  
<script type = "text/javascript">
  
  function open_email (order_id, short_order, email, type)
  {
    $("#email-orderid").val(order_id);
    $("#email-recipient").val(email);
    
    if (type != 0) {
      getEmailMessageType(type);
    } else {
      setEmailMessageSubject('Order ' + short_order);
    }
  }
  
  var editor = null;
  function initializeCkeditor ()
  {
    editor = CKEDITOR.replace('email-message');
  }

  initializeCkeditor();

  function reInitializeCkeditor ()
  {
    CKEDITOR.remove(CKEDITOR.instances['email-message']);
    $("#cke_email-message").remove();
    setTimeout(initializeCkeditor, 100);
  }

  function setEmailMessageSubject (subject)
  {
    $("#email-subject").val(subject);
  }

  function setEmailMessageToEditor (message)
  {
    CKEDITOR.instances['email-message'].setData(message);
  }

  function setEmailMessageToTextArea (message)
  {
    $("#email-message").val(message);
  }

  // on message type select change
  $("#message-types").on('change', function (event)
  {
    var message_type = $(this).val();
    if ( message_type == 0 || message_type == undefined ) {
      // no action is selected.
      reInitializeCkeditor();
      setEmailMessageSubject($("#email-subject").attr('data-default-subject'));
      return;
    }
    
    getEmailMessageType(message_type);
  });
  
  function getEmailMessageType(message_type) 
  {
    var url = "/orders/mailer";
    var order_id = $("#email-orderid").val();
    
    var data = {
      order_id: order_id, message_type: message_type, _token: "{{ csrf_token() }}"
    };
    var method = "POST";
    ajax(url, method, data, emailMessageTypeSelectionSuccessHandler, emailMessageTypeSelectionErrorHandler);
  }
  
  function emailMessageTypeSelectionSuccessHandler (data)
  {
    setEmailMessageSubject(data.subject);
    setEmailMessageToEditor(data.message);
  }

  function emailMessageTypeSelectionErrorHandler (data)
  {
    console.log(data);
    alert("Something went wrong!");
  }

  editor.on('change', function (event)
  {
    setEmailMessageToTextArea(event.editor.getData());
  });

  $("#send-email").on('click', function (event)
  {
    ajax("/orders/send_mail", "POST", $("#email-sender-form").serialize(), emailMessageSuccessHandler, emailMessageErrorSendHandler);
  });

  function emailMessageErrorSendHandler (data)
  {
    $("#large-email-modal-lg").modal('toggle');
    alert("Something went wrong!");
  }

  function emailMessageSuccessHandler (data)
  {
    $("#large-email-modal-lg").modal('toggle');
    alert(data);
  }
  
  function ajax (url, method, data, successHandler, errorHandler)
  {
    NProgress.start();
    $.ajax({
      url: url, method: method, data: data, success: function (data, status)
      {
        NProgress.done();
        successHandler(data);
      }, error: function (xhr, status, error)
      {
        NProgress.done();
        errorHandler(xhr);
      }
    })
  }
</script>