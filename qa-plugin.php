<?php
	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}

	// editor module
	qa_register_plugin_module('editor', 'q2apro-sceditor-editor.php', 'qa_sceditor', 'SCEditor');

	// upload page
	qa_register_plugin_module('page', 'q2apro-sceditor-upload.php', 'qa_sceditor_upload', 'SCEditor Upload');

	// layer to insert script in head
	qa_register_plugin_layer('q2apro-sceditor-layer.php', 'q2apro SCEditor Layer');

	// language file
	qa_register_plugin_phrases('q2apro-sceditor-lang-*.php', 'q2apro_sceditor_lang');

	// core function overrides to allow iframe
	qa_register_plugin_overrides('q2apro-sceditor-overrides.php');

	/* custom function */

	// check if GIF is animated, credits go to http://php.net/manual/en/function.imagecreatefromgif.php#104473
	function gif_is_animated($filename) {
		if(!($fh = @fopen($filename, 'rb')))
			return false;

		$count = 0;
		// an animated gif contains multiple "frames", with each frame having a header made up of:
		// * a static 4-byte sequence (\x00\x21\xF9\x04)
		// * 4 variable bytes
		// * a static 2-byte sequence (\x00\x2C)

		// read through the file till we reach the end of the file, or we have found at least 2 frame headers
		while(!feof($fh) && $count < 2) {
			$chunk = fread($fh, 1024 * 100); // read 100kb at a time
			$count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00\x2C#s', $chunk, $matches);
		}

		fclose($fh);
		return $count > 1;
	}
