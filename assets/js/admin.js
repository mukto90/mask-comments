$ = new jQuery.noConflict();
$(document).ready(function(){

	$('#mask-till').datetimepicker();

	$(".change-mod-settings").click(function(e){
		e.preventDefault();
		$(this).slideUp()
		$(".comm-mod-setting").slideToggle();
	})

	if( $("#post-comment-mask-display").text() == "Yes"){
		$(".comm-mod-inline-settings").slideDown();
	} else{
		$(".comm-mod-inline-settings").slideUp();
	}

	$(".is_comment_mask").click(function(e){
		var  sel = $(this).val();
		$("#post-comment-mask-display").text( sel);
		
		if( sel == "Yes"){
			$(".comm-mod-inline-settings").slideDown();
		} else{
			$(".comm-mod-inline-settings").slideUp();
		}
	})

	$(".save-post-comment-mask, .cancel-post-comment-mask").click(function(e){
		e.preventDefault();
		$(".comm-mod-setting").slideToggle();
		$(".change-mod-settings").slideDown()
	})

	// disable options in free version
	$(".mdc-mask-comment.free-version input[id*='wpuf-mask_general[user_levels]']").attr('disabled', true)
	$(".mdc-mask-comment.free-version input[id*='wpuf-mask_general[always_show]']").attr('disabled', true)
	$(".mdc-mask-comment.free-version textarea").attr('disabled', true)
})