$('li.search a').click(function() {
	$('#search').toggle('slow');
});
$('#search span.close a').click(function() {
	$('#search').hide('slow');
});
$('li.audio a').click(function() {
	$('#audio').toggle('slow');
});
$('#audio span.close a').click(function() {
	$('#audio').hide('slow');
});
