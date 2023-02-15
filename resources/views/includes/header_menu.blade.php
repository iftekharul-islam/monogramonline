<style>
@media print {
  a[href]:after {
    content:none;
  }
}
.blink_me {
  animation: blinker 1s linear infinite;
}
@keyframes blinker {  
  50% { opacity: 0; }
}
</style>
@if (env("APP_ENV") == 'Production')
  <div class = "navbar navbar-default">
@elseif (env("APP_ENV") == 'Development')
  <div class = "navbar navbar-default" style="background-color: #ff0000;">
@else
  <div class = "navbar navbar-default" style="background-color: #ffc000;">
@endif    
    
        <div class = "navbar-header">
            <button type = "button" class = "navbar-toggle collapsed" data-toggle = "collapse"
                    data-target = "#bs-example-navbar-collapse-1" aria-expanded = "false">
                <span class = "sr-only">Toggle navigation</span>
                <span class = "icon-bar"></span>
                <span class = "icon-bar"></span>
                <span class = "icon-bar"></span>
            </button>
            <a class = "navbar-brand" href = "{{url('/')}}">{{ env('APPLICATION_NAME') }} - {{env("APP_ENV")}}</a>
        </div>
        
        <div class = "collapse navbar-collapse" id = "bs-example-navbar-collapse-1">
            <ul class = "nav navbar-nav navbar-right">
              
              @if (auth()->user())
                <li>
                  {!! \App\Task::widget('user', auth()->user()->id); !!}
                </li>
               <li>
                 <a href = "{{url('logout')}}"><i class = "glyphicon glyphicon-log-out"></i>
                     {{ (auth()->user()->username) }}  Logout </a>
                </li>
               @endif
            </ul>
        </div>
    </div>
</nav>