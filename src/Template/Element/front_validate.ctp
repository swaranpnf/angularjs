<style type="text/css">
    .error-message {
        color: #ff0f14;
        font-size: 14px;
        position: absolute;
        top: 50px;
    }
    .error-message_for_term {
        color: #ff0f14;
        font-size: 14px;
        
    }
    .clb-err{
        margin-top: -10px;
    }
    .errorMessages .error-message{
      margin-top: 24px;
        color: #ff0f14;
        font-size: 14px;
    }

</style>

<?php echo $this->Html->script(array('jquery.ajax.form.min')); ?>
<script type="text/javascript">
    $(document).ready(function () {
       //on  key up remove the validation
        $("input").on('keyup', function() {
                        $this = $(this);
                        var len = $(this).val().length;
                       if (len > 0)
                        {
                            $this.next('.error-message').addClass('hide');
                        }
                        else
                        {
                            $this.next('.error-message').removeClass('hide');
                        }
                    });
        
         // Signup
        var options = {
            beforeSubmit: showRequest,
            success: showResponse
        };
        $('#Signup').submit(function () {
            $(this).ajaxSubmit(options);
            return false;
        });
        function showRequest(formData, jqForm, options) {
        }
        function showResponse(responseText, statusText, xhr, $form) {
            data = $.parseJSON(responseText);
            //console.log(data);
              $('div .error-message').remove();
              $('div .error-message_for_term').remove();
            
            if (data.username) {
                $('input[name="data[User][username]"]').addClass('err');
                errorDiv = '<div class="error-message">' + data.username[0] + '</div>';
                 $('input[name="data[User][username]"]').after(errorDiv);
            } else {
                $('input[name="data[User][username]"]').removeClass('err');
            }
            if (data.email) {
                $('input[name="data[User][email]"]').addClass('err');
                errorDiv = '<div class="error-message">' + data.email[0] + '</div>';
                 $('input[name="data[User][email]"]').after(errorDiv);
            } else {
                $('input[name="data[User][email]"]').removeClass('err');
            }
            if (data.password) {
                $('input[name="data[User][password]"]').addClass('err');
                errorDiv = '<div class="error-message">' + data.password[0] + '</div>';
                 $('input[name="data[User][password]"]').after(errorDiv);
            } else {
                $('input[name="data[User][password]"]').removeClass('err');
            }
            if (data.terms) {
              //  $('input[name="data[User][terms]"]').parent().parent().addClass('message_for_term');
                errorDiv = '<div class="error-message_for_term">' + data.terms+ '</div>';
                 $('.this_is_for_term').after(errorDiv);
                 
                
            } else {
                $('input[name="data[User][terms]"]').parent().parent().removeClass('err_term');
            }
            if (data.status == 'success') {
               //$("#reg_success").removeClass('hide');
               //$("#suc_mesg").text(data.message);
                //window.location.href = data.url;
                $("#Signup")[0].reset();
                swal("Success", data.message, "success");
            }
            else {
            }
        }


        // User Login
        var options1 = {
            beforeSubmit: showRequest1,
            success: showResponse1
        };
        $('#Login').submit(function () {
            $(this).ajaxSubmit(options1);
            return false;
        });
        function showRequest1(formData, jqForm, options) {
        }
        function showResponse1(responseText, statusText, xhr, $form) {
            data1 = $.parseJSON(responseText);
           // console.log(data1);
            $('div.error-message').remove(); 
            if (data1.email1) {      
                $('input[name="data[User][email1]"]').addClass('err');
                 errorDiv = '<div class="error-message">' + data1.email1[0] + '</div>';
                 $('input[name="data[User][email1]"]').after(errorDiv);
            
            } 
           if (data1.password) {
                $('input[name="data[User][password]"]').addClass('err');
                errorDiv = '<div class="error-message">' + data1.password[0] + '</div>';
                $('input[name="data[User][password]"]').after(errorDiv);
                
            } 
            if (data1.match) {
                $('input[name="data[User][password]"]').addClass('err');
                errorDiv = '<div class="error-message">' + data1.match[0] + '</div>';
                $('input[name="data[User][password]"]').after(errorDiv);
                
            } 
            if (data1.notexistemail) {
                 $('input[name="data[User][email1]"]').addClass('err');
                 errorDiv = '<div class="error-message">' + data1.notexistemail[0] + '</div>';
                 $('input[name="data[User][email1]"]').after(errorDiv);

              }
              if (data1.suspendedstatus) {
                //errorDiv = '<div class="error-message">' + data1.disablestatus[0] + '</div>';
//                $('.errorMessages').show();
//                $('.errorMessages').append(errorDiv);
//var message2=disablestatus[0];
                swal("Warning!", data1.suspendedstatus, "warning");
            }
            if (data1.disablestatus) {
                //errorDiv = '<div class="error-message">' + data1.disablestatus[0] + '</div>';
//                $('.errorMessages').show();
//                $('.errorMessages').append(errorDiv);
//var message2=disablestatus[0];
                swal("Error!", data1.disablestatus, "error");
            }
            if (data1.status == 'success') {
                
                if (data1.email_remember && data1.password_remember) {
                    setCookie('email', data1.email_remember);
                    setCookie('password', data1.password_remember);
                } else {
                    document.cookie = "email=; expires=Thu, 01 Jan 1970 00:00:00 UTC";
                    document.cookie = "password=; expires=Thu, 01 Jan 1970 00:00:00 UTC";
                }
              window.location.href = data1.url;


            }
            else {
            }
        }
//Forgot Password
var optionsForgotPassword = {
    beforeSubmit:showRequestForgot,
    success:showResponseForgot
    };
    $("#ForgotPassword").submit(function(){
        $(this).ajaxSubmit(optionsForgotPassword);
        return false;
    });
    function showRequestForgot(formData, jqForm, options) {
        }
    function showResponseForgot(responseText, statusText, xhr, $form) {
            dataForgot = $.parseJSON(responseText);
            //console.log(dataForgot);
            $('div.error-message').remove(); 
            if (dataForgot.email1) {      
                $('input[name="data[User][email1]"]').addClass('err');
                 errorDiv = '<div class="error-message">' + dataForgot.email1[0] + '</div>';
                 $('input[name="data[User][email1]"]').after(errorDiv);
            
            } 
           if (dataForgot.notexistemail) {
                 $('input[name="data[User][email]"]').addClass('err');
                 errorDiv = '<div class="error-message">' + dataForgot.notexistemail[0] + '</div>';
                 $('input[name="data[User][email1]"]').after(errorDiv);
              }
             if (dataForgot.status == 'success') {
                 $("#reg_success").removeClass('hide');
               $("#suc_mesg").text(dataForgot.message);
             }
            else {
            }
        }
//Reset password

//Forgot Password
var optionsResetPassword = {
    beforeSubmit:showRequestReset,
    success:showResponseReset
    };
    $("#ResetPassword").submit(function(){
        $(this).ajaxSubmit(optionsResetPassword);
        return false;
    });
    function showRequestReset(formData, jqForm, options) {
        }
    function showResponseReset(responseText, statusText, xhr, $form) {
            dataReset = $.parseJSON(responseText);
           // console.log(dataReset);
            $('div.error-message').remove(); 
            if (dataReset.email1) {      
                $('input[name="data[User][email1]"]').addClass('err');
                 errorDiv = '<div class="error-message">' + dataReset.email1[0] + '</div>';
                 $('input[name="data[User][email1]"]').after(errorDiv);
            
            } 
            if (dataReset.password) {      
                $('input[name="data[User][password]"]').addClass('err');
                 errorDiv = '<div class="error-message">' + dataReset.password[0] + '</div>';
                 $('input[name="data[User][password]"]').after(errorDiv);
            
            } 
            if (dataReset.con_password) {      
                $('input[name="data[User][con_password]"]').addClass('err');
                 errorDiv = '<div class="error-message">' + dataReset.con_password[0] + '</div>';
                 $('input[name="data[User][con_password]"]').after(errorDiv);
            
            } 
            
           if (dataReset.passmatch) {
                 $('input[name="data[User][con_password]"]').addClass('err');
                 errorDiv = '<div class="error-message">' + dataReset.passmatch[0] + '</div>';
                 $('input[name="data[User][con_password]"]').after(errorDiv);
              }
             if (dataReset.status == 'success') {
               $("#reg_success").removeClass('hide');
               $("#suc_mesg").text(dataReset.message);
               //window.location.href = dataReset.url;
             }
            else {
            }
        }

    });
</script>