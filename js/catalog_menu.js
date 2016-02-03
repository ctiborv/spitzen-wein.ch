$(document).ready(function() {
	$(".katalog-menu a, .lista a").each(function() {
		this.href = this.href + '#katalog';
	});
});
