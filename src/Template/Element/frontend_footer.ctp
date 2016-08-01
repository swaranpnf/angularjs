 
   <section class=" ftr footer">
   <p>Copyright ReverseAdvisor.com</p>
   </section>
   


    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <?php echo $this->element('front_validate'); ?>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <?php  echo $this->Html->Script(array('bootstrap','icheck','sweetalert.min.js','basic'));?>
    <script>
    function setCookie(cname, cvalue) {
        var exdays = 30;
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    }
  $(document).ready(function () {

// cookie create and get
    $('.terms').on('click', function () {
        alert("1");
            var check = $(this).is(':checked');
            if (check == true) {
                $(this).val(1);
            } else {
                $(this).val(0);
            }
        });
    });
</script>
    <script>
$(document).ready(function(){
  $('input').iCheck({
    checkboxClass: 'icheckbox_square-orange',
    radioClass: 'iradio_square-orange',
    increaseArea: '20%' // optional
  });
  
  // 
  $( "body" ).on('click','#email', function () {
	 var a =  $(this).next().children();
$(a).addClass('addone');
});
 $( "body" ).on('click','#password', function () {
	 var a =  $(this).next().children();
$(a).addClass('addone');
});


/*$( ".login-dv" ).mouseout(function() {
	alert('a');
});*/
// Document Ready closed
});
</script>
 
  </body>
</html>