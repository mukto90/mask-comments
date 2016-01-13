$ = new jQuery.noConflict();
$(document).ready(function(){
	
	$(".change-mod-settings").click(function(){
		$(this).hide()
		$(".comm-mod-setting").slideToggle();
	})

	$(".is_comment_mask").click(function(e){
		var vala = $(this).val();
		$("#post-comment-mask-display").text(vala);
	})

	$(".save-post-comment-mask, .cancel-post-comment-mask").click(function(){
		$(".comm-mod-setting").slideToggle();
		$(".change-mod-settings").show()
	})

})