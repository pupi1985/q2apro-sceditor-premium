<?php

/*
	Plugin Name: SCEditor
	Plugin URI: http://www.q2apro.com/plugins/sceditor
	Plugin Description: Provides the SCEditor as WYSIWYG rich text editor for your question2answer forum.
	Licence: Copyright © q2apro.com - All rights reserved
*/


	class qa_sceditor_upload {
	
		function match_request($request)
		{
			return ($request=='sceditor-upload');
		}

		
		function process_request($request)
		{
			$errormessage = '';
			$url = '';
			$format = '';
			$filename = '';
			$filesize = '';

			/*
			$fileName = $_FILES['imgfile']['name'];
			$fileTmpLoc = $_FILES['imgfile']['tmp_name']; // File in the PHP tmp folder 
			$fileType = $_FILES['imgfile']['type']; // The type of file it is 
			$fileSize = $_FILES['imgfile']['size']; // File size in bytes 
			$fileErrorMsg = $_FILES['imgfile']['error']; // 0 for false... and 1 for true
			*/
			
			// $_FILES should contain the image
			$imageformats = array('png','gif','jpeg','jpg');
			$disallowedformats = array('exe','bat');
			if(is_array($_FILES) && count($_FILES)) {
				if(!qa_opt('q2apro_sceditor_upload_enabled'))
				{
					$errormessage = qa_lang('q2apro_sceditor_lang/upload_disabled');
				}
				else
				{
					$filename = $_FILES['imgfile']['name'];
					$filetype = $_FILES['imgfile']['type'];
					$filesize_bytes = $_FILES['imgfile']['size'];
					
					if($filetype=='application/octet-stream')
					{
						// file has no extension, filetype not set
						$errormessage = qa_lang('q2apro_sceditor_lang/upload_error_filetype');
					}
					else if(empty($filename))
					{
						$errormessage = qa_lang('q2apro_sceditor_lang/upload_error_filename');
					}
					else if($filesize_bytes==0)
					{
						$errormessage = qa_lang('q2apro_sceditor_lang/upload_error_filesize');
					}
					else
					{
						// if bigger than 100 kb display MB
						if($filesize_bytes>1024*100)
						{
							$filesize = number_format($filesize_bytes/1048576, 1, ',', '.').' MB'; // File size in MB
						}
						else
						{
							// display kb instead of MB
							$filesize = round($filesize_bytes/1024).' kb'; // File size in kb
						}
						
						require_once QA_INCLUDE_DIR.'qa-app-upload.php';
						$img_maxwidth = qa_opt('q2apro_sceditor_upload_maximgwidth');
						
						// if an animated gif gets resized, we lose the animation, check if animated
						if($filetype=='image/gif')
						{
							$fileTmpLoc = $_FILES['imgfile']['tmp_name']; // file path in the PHP tmp folder 
							// no resizing if animated gif to keep the animation
							if(gif_is_animated($fileTmpLoc)) {
								$img_maxwidth = null;
							}
						}
						
						// qa_upload_file_one($maxfilesize=null, $onlyimage=false, $imagemaxwidth=null, $imagemaxheight=null)
						$upload = qa_upload_file_one(
							qa_opt('q2apro_sceditor_upload_max_size'),
							false, // onlyimages
							qa_opt('q2apro_sceditor_upload_images') ? $img_maxwidth : null, // max width if it is an image upload
							null // no max height
						);
						
						$errormessage = @$upload['error'];
						$url = @$upload['bloburl'];
						$format = @$upload['format'];
						
						if($errormessage=='' && in_array($format,$imageformats))
						{
							if(!qa_opt('q2apro_sceditor_upload_images')) {
								$errormessage = qa_lang('q2apro_sceditor_lang/image_upload_disabled');
							}
						}
					}
				}
			}
			
			// if we have an error throw back message
			if($errormessage!='') {
				echo '<p></p> <p style="color:#F00;">'.$errormessage.'</p> <p></p>';
			}
			else {
				// disallowed file or upload of other files disallowed
				if(in_array($format,$disallowedformats) || !qa_opt('q2apro_sceditor_upload_enabled')) {
					echo '<p></p> <p style="color:#F00;">'.qa_lang('q2apro_sceditor_lang/filetype_not_allowed').'</p> <p></p>';
				}
				else if(in_array($format,$imageformats)) {
					// return html to embed and display image
					echo '<img src="'.$url.'" alt="Image" /> ';
				}
				else {
					// return link to uploaded file
					echo '<a href="'.$url.'" title="'.$format.'-Dokument ('.$filesize.')">'.$filename.' ('.$filesize.')</a> ';
				}
			}
			
			return null;
		} // end process_request
		
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/