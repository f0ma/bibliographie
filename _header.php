<!DOCTYPE html>
<html lang="de">
	<head>
		<title><?php echo strip_tags($title)?> | bibliographie</title>

		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/stylesheets/all.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/stylesheets/silk-icons.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/stylesheets/jquery.jgrowl.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/stylesheets/token-input.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/stylesheets/token-input-facebook.css" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/stylesheets/jquery-ui.css" />

		<script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/javascript/jquery.jgrowl.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/javascript/jquery.tokeninput.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/javascript/jquery-ui.js"></script>
		<script type="text/javascript" src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/javascript/jquery-plugins.js"></script>
	</head>


	<body id="top">
		<div id="jQueryLoading" style="display: none;"><img src="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/resources/images/loading.gif" alt="loading" width="16" height="11" />&nbsp;Actions pending <span id="jQueryLoadingAmount"></span></div>

		<div id="wrapper">
			<div id="header">
				<form action="<?php echo BIBLIOGRAPHIE_WEB_ROOT?>/search/" method="get" id="search">
					<div>
						<input type="hidden" id="task" name="task" value="simpleSearch" />
						<input type="text" id="q" name="q" style="width: 80%" value="<?php echo htmlspecialchars($_GET['q'])?>" />
						<button id="searchSubmit"><span class="silk-icon silk-icon-find"></span></button>
					</div>
				</form>

				<h1>bibliographie</h1>
			</div>

			<script type="text/javascript">
				/* <![CDATA[ */
var jQueryLoading = 0;

$('#jQueryLoading').bind('ajaxSend', function(event, jqXHR, ajaxOptions) {
	$('body').css('cursor', 'wait');
	if(jQueryLoading == 0)
		$(this).show();
	jQueryLoading++;
	$('#jQueryLoadingAmount').html('('+jQueryLoading+')');
	$.jGrowl('Sending AJAX query to: <em>'+ajaxOptions.url+'</em>');
}).bind('ajaxComplete', function(){
	$('body').css('cursor', 'auto');
	jQueryLoading--;
	$('#jQueryLoadingAmount').html('('+jQueryLoading+')');

	if(jQueryLoading == 0)
		$(this).hide('fade');
});

$.jGrowl.defaults.position = 'bottom-right';
$.jGrowl.defaults.life = 10000;
jQuery.ajaxSetup({
	cache: false
});
				/* ]]> */
			</script>

			<div id="menu">
				<h3>Topics</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/topics/?task=showGraph"><?php echo bibliographie_icon_get('sitemap')?> Show topic graph</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/topics/?task=topicEditor"><?php echo bibliographie_icon_get('folder-add')?> Create topic</a>

				<h3>Authors</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/authors/?task=showList"><?php echo bibliographie_icon_get('group')?> Show authors</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/authors/?task=createAuthor"><?php echo bibliographie_icon_get('user-add')?> Create author</a>

				<h3>Publications</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/publications/?task=publicationEditor"><?php echo bibliographie_icon_get('page-white-add')?> Create publication</a>

				<h3>Bookmarks & Tags</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/bookmarks/?task=showBookmarks"><?php echo bibliographie_icon_get('tag-blue')?> Show bookmarks</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/tags/?task=showCloud"><?php echo bibliographie_icon_get('tag-blue')?> Show tags</a>

				<h3>Maintenance</h3>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=ToDo"><?php echo bibliographie_icon_get('page-white-text')?> ToDo</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=lockedTopics"><?php echo bibliographie_icon_get('lock')?> Locked topics</a>
				<a href="<?php echo BIBLIOGRAPHIE_ROOT_PATH?>/maintenance/?task=parseLog"><?php echo bibliographie_icon_get('time-linemarker')?> Parse log</a>
			</div>

			<div id="content">