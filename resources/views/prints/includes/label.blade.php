<script type="text/javascript" src="/assets/js/BrowserPrint.js"></script>
<script type="text/javascript" src="/assets/js/DevDemo.js"></script>

<script type="text/javascript">

    var OSName = "Windows";

    $(document).ready(setup_web_print);

    @if (isset($reminder) && strlen($reminder) > 0)
    $(document).ready(alert('{{ $reminder }}'));
    @endif

</script>

<div class="container" style="width:500px">
    <div id="main">
        <div id="printer_data_loading" style="display:none"><span id="loading_message">Loading Printer Details...</span><br/>
            <div class="progress" style="width:100%">
                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100"
                     aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                </div>
            </div>
        </div> <!-- /printer_data_loading -->
        <div id="printer_details" style="display:none">
            <span id="selected_printer">No data</span>
            <button type="button" class="btn btn-success" onclick="changePrinter()">Change</button>
        </div>
        <br/> <!-- /printer_details -->
        <div id="printer_select" style="display:none">
            Zebra Printer Options<br/>
            Printer: <select id="printers"></select>
        </div> <!-- /printer_select -->
        <div id="print_form" style="display:none">
            Enter Name: <input type="text" id="entered_name" value="{{$label}}"/>
            <br/><br/>
            <button type="button" id="sendLabelBtn" class="btn btn-lg btn-primary" onclick="sendData();" value="Print">Print Label
            </button>
        </div> <!-- /print_form -->


            <script type="application/javascript">
                console.log("Auto print has been enabled")
                setTimeout(function () {
                    console.log("Printing it now...")
                    sendData()
                }, 1250)
            </script>

    </div> <!-- /main -->
    <div id="error_div" style="width:500px; display:none">
        <div id="error_message"></div>
        <button type="button" class="btn btn-lg btn-success" onclick="trySetupAgain();">Try Again</button>
    </div><!-- /error_div -->
</div><!-- /container -->