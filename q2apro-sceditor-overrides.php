<?php
/*
	Plugin Name: Q2APRO User Rules Overrides
*/

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}


	function qa_sanitize_html($html, $linksnewwindow=false, $storage=false)
	{
		// require_once 'vendor/htmLawed.php';
		require_once 'qa-htmLawed.php';

		global $qa_sanitize_html_newwindow;

		$qa_sanitize_html_newwindow=$linksnewwindow;

		$safe=htmLawed($html, array(
			'safe' => 1,
			'elements' => '*+embed+iframe+object-form', // q2apro: added iframe
			'schemes' => 'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; *:file, http, https; style: !; classid:clsid',
			'keep_bad' => 0,
			'anti_link_spam' => array('/.*/', ''),
			'hook_tag' => 'qa_sanitize_html_hook_tag',
		));

		return $safe;
	}

/*
	Omit PHP closing tag to help avoid accidental output
*/