$(function(){
	let params = new URLSearchParams(window.location.search);
	if (params.has('id')) $('#id').val(params.get('id'));
	if (params.has('delete')) $('#del-pass').val(params.get('delete'));
});
