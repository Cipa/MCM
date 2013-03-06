$(document).ready(function() {
    
    
	$("input:radio[name=list]").click(function() {
	    var value = $(this).val();
	    
	    $('#' + value).show();
	    $('.groups').show();

	});
    
    $('.js-button-preview').click(function(e){
	    
	    e.preventDefault();
	    $('.js-form-config').submit();
	    
    });
    
    $('.js-button-send-test').click(function(e){
	    e.preventDefault();
	    
	    $.ajax({
			type: 'POST',
			url: sendTestUrl,
			data: sendTestData + '&emails=' + $('input[name=emails]').val(),
			success: function(result) {	
				if(result){
					$('.js-message').html(result.error).append(result.success);
				}else{
					$('.js-message').html("You're proabably doing something wrong.");
				}			
				
        	},
			dataType: 'json'

		});

	    return false;
    });
    
});

$(window).resize(function(){

});

$(window).load(function () {

	var selectedList = $("input:radio[name=list]:checked").val();
	
	if(selectedList){
		$("input:radio[name=list]:checked").trigger('click');
	}

});