$(document).ready(function() {
	$.featureList(
		$(".uvod-str li a"),
		$(".output li"), {
			start_item	:	0,
			transition_interval	:	5000
		}
	);
});