//https://www.w3schools.com/howto/howto_js_countdown.asp
// Dec 05, 2017 14:25:24        153
/*function countdown(time, id)
{
  // Set the date we're counting down to
  var countDownDate = new Date(time).getTime();

  // Update the count down every 1 second
  var x = setInterval(function() {

      // Get todays date and time
      var now = new Date().getTime();

      // Find the distance between now an the count down date
      var distance = countDownDate - now;

      // Time calculations for days, hours, minutes and seconds
      //var days = Math.floor(distance / (1000 * 60 * 60 * 24));
      var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Output the result in an element with id='demo'
      $('#clock-invoice-' + id + ' td span.clock').text(hours + 'h '+ minutes + 'm ' + seconds + 's ');

      // If the count down is over, write some text
      if (distance < 0) {
          clearInterval(x);
          //$('#clock-invoice-' + id).text('WAIT');
          $('#clock-invoice-' + id).remove();
      }
  }, 1000);
}*/

// Ref: https://www.w3resource.com/javascript/form/ip-address-validation.php
// Another: https://www.regextester.com/22
// ^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$
function ValidateIPSyntax(ip)
{
	if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ip))
		return false;
	else return true;
}

function create(id)
{
  ipaddress = $('#item-'+id+' td input.ipaddress').val();

  if (ipaddress.length == 0)
  {
    $.notify('Creating item requires an IP address', 'error');
  } else if (ValidateIPSyntax(ipaddress))
  {
    $.notify('Invalid IP address '+ipaddress+' at item #'+id, 'error');
  } else
  {
    $.ajax({
      type: 'GET',
      url: 'addonmodules.php',
      timeout: 5000,		// When use ',' as delim, it is converted to %2C
      data: {module: 'proxmox', action: 'create', id: id, ipaddress: ipaddress},
      success: function(data) {
        $.notify('Invoice item #'+id+' is moved to queue. Please reload the page for updates.', 'success');
      },
      error: function(x, t, m) {
        $.notify('Invalid invoice item #' + id, 'error');
      }
    });
  }
}

$(document).ready(function() {
	$(".nav-tabs a").click(function(){
			$(this).tab('show');
	});
	
	/*var groups = ['paid', 'queued', 'created'];
	for (var i = 0; i < groups.length; i++)
	{
		console.log('#'+groups[i]+' p input');

		$('#'+groups[i]+' p input').on('keyup', function() {
		    var value = $(this).val().toLowerCase();
		    $('#'+groups[i]+' table:eq(1) tbody tr').filter(function() {
		      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
		    });
		  });
	}*/

	$('#paid p input').on('keyup', function() {
			var value = $(this).val().toLowerCase();
			$('#paid table:eq(1) tbody tr').filter(function() {
				$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
			});
		});

	$('#queued p input').on('keyup', function() {
			var value = $(this).val().toLowerCase();
			$('#queued table:eq(1) tbody tr').filter(function() {
				$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
			});
		});

	$('#created p input').on('keyup', function() {
			var value = $(this).val().toLowerCase();
			$('#created table:eq(1) tbody tr').filter(function() {
				$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
			});
		});


});
