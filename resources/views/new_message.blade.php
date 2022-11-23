@extends('my_profile')

@section('head')
<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();

      $("#mainForm").on('submit',(function(e){
        e.preventDefault();

    }));

});


function prepareLocale()
{

    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";
}

function clearMessage()
{
    $("#message").val("");
    $("#subject").val("");
}

function submitMessage()
{

    if($("#mainForm").attr("enabled") == 0)
    {
        return;
    }

    $("#mainForm").attr("enabled",0);

        
    $.ajax({
        type: "POST",
        url: "/ajax/message/new",
        data:  new FormData($("#mainForm")[0]),
        contentType: false,
        cache: false,
         processData:false,
        success: function(data) 
        {
            $("#message").val("");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status,onMainModalDismiss);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onMainModalDismissError);
            }


        },
        error: function(){}       
    });
}

function onMainModalDismiss()
{
    window.location.href = "/message/sent";
}

function onMainModalDismissError()
{
    $("#mainForm").attr("enabled",1);
}
</script>

<style>
</style>

@endsection

@section('details')

<div class="body">
    <div class="account-info section" data-section="messages">
        <div class="title">
            <span class="span-title"></span>
            <span class="unreadmsg"></span>
        </div>

        <div class="messages-container">
            <div class="section-body">
                <div id="content-top-bar" class="tab-container clearfix">
                </div>

                <div class="card" style="background:transparent;border:none;">
                    <div class="card-body profile-body">
                        <form method="POST" id="mainForm" class="send-message-form">
                            @csrf
                            <div class="form-container">
                                <div class="form-group">
                                    <div class="form-element">
                                        <label>{{ __('app.newmessage.subject') }}</label>
                                        <div class="input-wrapper">
                                            <input name="subject" class="ember-text-field ember-view" type="text">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-element">
                                        <label>{{ __('app.newmessage.message') }}</label>
                                        <div class="input-wrapper textarea">
                                            <textarea id="message" name="message" class="ember-text-area ember-view"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="footer">
                                    <button class="btn btn1" onclick="submitMessage()">
                                        {{ __('app.newmessage.button.send') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection