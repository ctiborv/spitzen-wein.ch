$(document).ready(function()
{
	language = window.language || 'de';

	var english_options =
	{
		imageLoading:'/styles/images/lightbox/lightbox-ico-loading.' + language + '.gif',
		imageBtnPrev:'/styles/images/lightbox/lightbox-btn-prev.' + language + '.gif',
		imageBtnNext:'/styles/images/lightbox/lightbox-btn-next.' + language + '.gif',
		imageBtnClose:'/styles/images/lightbox/lightbox-btn-close.' + language + '.gif',
		imageBlank:'/styles/images/lightbox/lightbox-blank.' + language + '.gif'
	};

	var czech_options =
	{
		imageLoading:'/styles/images/lightbox/lightbox-ico-loading.' + language + '.gif',
		imageBtnPrev:'/styles/images/lightbox/lightbox-btn-prev.' + language + '.gif',
		imageBtnNext:'/styles/images/lightbox/lightbox-btn-next.' + language + '.gif',
		imageBtnClose:'/styles/images/lightbox/lightbox-btn-close.' + language + '.gif',
		imageBlank:'/styles/images/lightbox/lightbox-blank.' + language + '.gif',
		txtImage:'Obrázek',
		txtOf:'z'
	};

	var options_single =
	{
	}

	var options_multi =
	{
//		fixedNavigation:true
	}

	if (language == 'cz')
	{
		jQuery.extend(options_single, czech_options);
		jQuery.extend(options_multi, czech_options);
	}
	else
	{
		jQuery.extend(options_single, english_options);
		jQuery.extend(options_multi, english_options);
	}

	$("a[rel='lightbox']").map(function(){$(this).lightBox(options_single);});
	$("a[rel='lightbox[roadtrip]']").lightBox(options_multi);
});