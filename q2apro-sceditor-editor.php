<?php

/*
	Plugin Name: SCEditor
	Plugin URI: http://www.q2apro.com/plugins/sceditor
	Plugin Description: Provides the SCEditor as WYSIWYG rich text editor for your question2answer forum.
	Licence: Copyright © q2apro.com - All rights reserved
*/


	class qa_sceditor 
	{
		
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->urltoroot=$urltoroot;
		}

		
		function option_default($option)
		{
			if ($option=='q2apro_sceditor_upload_max_size') {
				require_once QA_INCLUDE_DIR.'qa-app-upload.php';
				return min(qa_get_max_upload_size(), 1048576);
			}
			switch($option) {
				case 'q2apro_sceditor_enabled':
					return 1; // true
				case 'q2apro_sceditor_editorlocale':
					return 'en'; // English
				case 'q2apro_sceditor_editorplugin':
					return 'xhtml'; // xhtml or bbcode
				case 'q2apro_sceditor_editortheme':
					return 'square.min.css'; // xhtml or bbcode
				case 'q2apro_sceditor_toolbar':
					return 'bold,italic,underline|color|orderedlist,bulletlist|link,table'; // editor buttons on toolbar
				case 'q2apro_sceditor_editorcolors':
					return '#000|#F00|#11C11D|#00F|#B700B7|#FF8C00|#008080|#808080|#D3D3D3'; // color selection by q2apro
				case 'q2apro_sceditor_editorwidth':
					return 600; // change later to 100%
				case 'q2apro_sceditor_editorheight':
					return 350;
				case 'q2apro_sceditor_editorresizable':
					return true;
				case 'q2apro_sceditor_editorexpand':
					return false;
				case 'q2apro_sceditor_preview_enabled':
					return false;
				case 'q2apro_sceditor_upload_enabled':
					return false;
				case 'q2apro_sceditor_upload_images':
					return true;
				case 'q2apro_sceditor_upload_docs':
					return false;
				case 'q2apro_sceditor_upload_maximgwidth':
					return 600;
				case 'q2apro_sceditor_editorrtl':
					return false;
				case 'q2apro_sceditor_mathjax':
					return false;
				case 'q2apro_sceditor_mathjax_global':
					return false;
				default:
					return null;
			}
		}
	
	
		function bytes_to_mega_html($bytes)
		{
			return qa_html(number_format($bytes/1048576, 1));
		}
	
	
		function admin_form(&$qa_content) 
		{
			
			require_once QA_INCLUDE_DIR.'qa-app-upload.php';
			
			// process the admin form if admin hit Save-Changes-button
			$ok = null;
			if (qa_clicked('q2apro_sceditor_save')) {
				// qa_opt('q2apro_sceditor_enabled', (bool)qa_post_text('q2apro_sceditor_enabled')); // empty or 1
				qa_opt('q2apro_sceditor_editorlocale', qa_post_text('q2apro_sceditor_editorlocale')); // language code, e.g. lt
				qa_opt('q2apro_sceditor_editortheme', qa_post_text('q2apro_sceditor_editortheme')); // theme
				qa_opt('q2apro_sceditor_editorplugin', qa_post_text('q2apro_sceditor_editorplugin')); // xhtml or bbcode
				qa_opt('q2apro_sceditor_toolbar', qa_post_text('q2apro_sceditor_toolbar')); // bold,italic,underline|color|orderedlist,bulletlist|link,table
				qa_opt('q2apro_sceditor_editorcolors', qa_post_text('q2apro_sceditor_editorcolors')); // #000|#F00|#11C11D|#00F|#B700B7|#FF8C00|#008080|#808080|#D3D3D3
				qa_opt('q2apro_sceditor_editorwidth', (int)qa_post_text('q2apro_sceditor_editorwidth')); // e.g. 500
				qa_opt('q2apro_sceditor_editorheight', (int)qa_post_text('q2apro_sceditor_editorheight')); // e.g. 350
				qa_opt('q2apro_sceditor_editorresizable', (bool)qa_post_text('q2apro_sceditor_editorresizable')); // empty or 1
				qa_opt('q2apro_sceditor_editorexpand', (bool)qa_post_text('q2apro_sceditor_editorexpand')); // empty or 1
				qa_opt('q2apro_sceditor_preview_enabled', (bool)qa_post_text('q2apro_sceditor_preview_enabled')); // empty or 1
				qa_opt('q2apro_sceditor_mathjax', (bool)qa_post_text('q2apro_sceditor_mathjax')); // empty or 1
				qa_opt('q2apro_sceditor_mathjax_global', (bool)qa_post_text('q2apro_sceditor_mathjax_global')); // empty or 1
				qa_opt('q2apro_sceditor_upload_enabled', (bool)qa_post_text('q2apro_sceditor_upload_enabled')); // empty or 1
				qa_opt('q2apro_sceditor_upload_images', (bool)qa_post_text('q2apro_sceditor_upload_images')); // empty or 1
				qa_opt('q2apro_sceditor_upload_docs', (bool)qa_post_text('q2apro_sceditor_upload_docs')); // empty or 1
				qa_opt('q2apro_sceditor_upload_max_size', min(qa_get_max_upload_size(), 1048576*(float)qa_post_text('q2apro_sceditor_upload_max_size')));
				qa_opt('q2apro_sceditor_upload_maximgwidth', (int)qa_post_text('q2apro_sceditor_upload_maximgwidth'));
				qa_opt('q2apro_sceditor_editorrtl', (bool)qa_post_text('q2apro_sceditor_editorrtl')); // empty or 1
				
				// if upload is enabled but neither images nor docs have been selected, disable upload
				if( qa_opt('q2apro_sceditor_upload_enabled') && (!qa_opt('q2apro_sceditor_upload_images') && !qa_opt('q2apro_sceditor_upload_docs')) ) {
					qa_opt('q2apro_sceditor_upload_enabled', false);
				}
				
				// everything alright
				$ok = qa_lang('admin/options_saved');
			}
			
			// form fields to display frontend for admin
			$fields = array();
			
			/*
			$fields[] = array(
				'type' => 'checkbox',
				'label' => qa_lang('q2apro_sceditor_lang/enable_plugin'),
				'tags' => 'name="q2apro_sceditor_enabled"',
				'value' => qa_opt('q2apro_sceditor_enabled'),
			);
			*/
			
			// editor language
			$editorlocale = qa_opt('q2apro_sceditor_editorlocale'); // xhtml or bbcode
			$editorlocaleOptions = array(
				'ar' => 'ar',
				'cn' => 'cn',
				'de' => 'de',
				'el' => 'el',
				'en' => 'en',
				'en-US' => 'en-US',
				'es' => 'es',
				'et' => 'et',
				'fa' => 'fa',
				'fr' => 'fr',
				'hu' => 'hu',
				'it' => 'it',
				'ja' => 'ja',
				'lt' => 'lt',
				'nb' => 'nb',
				'nl' => 'nl',
				'pl' => 'pl',
				'pt-BR' => 'pt-BR',
				'pt-PT' => 'pt-PT',
				'ru' => 'ru',
				'sv' => 'sv',
				'tr' => 'tr',
				'tw' => 'tw',
				'uk' => 'uk',
				'vi' => 'vi',
			);
			$fields[] = array(
				'type' => 'select',
				'label' => 'Editor language', // qa_lang('q2apro_sceditor_lang/editorplugin_label'),
				'tags' => 'name="q2apro_sceditor_editorlocale"',
				'options' => $editorlocaleOptions,
				'value' => $editorlocaleOptions[$editorlocale],
			);
			
			// editor syntax type
			$editorplugin = qa_opt('q2apro_sceditor_editorplugin'); // xhtml or bbcode
			$editorpluginOptions = array(
				'xhtml' => 'xhtml',
				'bbcode' => 'bbcode',
			);
			$fields[] = array(
				'type' => 'select',
				'label' => 'XHTML or BBCODE', // qa_lang('q2apro_sceditor_lang/...'),
				'tags' => 'name="q2apro_sceditor_editorplugin"',
				'options' => $editorpluginOptions,
				'value' => $editorpluginOptions[$editorplugin],
			);
			
			// editor theme
			$editortheme = qa_opt('q2apro_sceditor_editortheme'); // default.min.css modern.min.css office.min.css office-toolbar.min.css square.min.css
			$editorthemeOptions = array(
				'default.min.css' => 'default.min.css',
				'modern.min.css' => 'modern.min.css',
				'office.min.css' => 'office.min.css',
				'office-toolbar.min.css' => 'office-toolbar.min.css',
				'square.min.css' => 'square.min.css',
			);
			$fields[] = array(
				'type' => 'select',
				'label' => 'Editor Theme', // qa_lang('q2apro_sceditor_lang/...'),
				'tags' => 'name="q2apro_sceditor_editortheme"',
				'options' => $editorthemeOptions,
				'value' => $editorthemeOptions[$editortheme],
			);
			
			// available buttons: bold italic underline strike subscript superscript left center right justify font color size removeformat pastetext bulletlist orderedlist table code quote horizontalrule image email link unlink emoticon youtube date time ltr rtl print maximize source
			$fields[] = array(
				'type' => 'input',
				'label' => 'Toolbar buttons<br /><span style="font-size:11px;line-height:110%;">Available: bold italic underline strike subscript superscript left center right justify font color size removeformat pastetext bulletlist orderedlist table code quote horizontalrule image email link unlink emoticon youtube date time ltr rtl print maximize source</span>', // qa_lang('q2apro_sceditor_lang/...'),
				'tags' => 'name="q2apro_sceditor_toolbar"',
				'value' => qa_opt('q2apro_sceditor_toolbar'),
			);
			
			$fields[] = array(
				'type' => 'input',
				'label' => 'Available colors for color button<br /><span style="font-size:11px;line-height:110%;">Leave empty for default.</span>', // qa_lang('q2apro_sceditor_lang/...'),
				'tags' => 'name="q2apro_sceditor_editorcolors"',
				'value' => qa_opt('q2apro_sceditor_editorcolors'),
			);
			
			$fields[] = array(
				'type' => 'number',
				'label' => 'Default editor width:<br /><span style="font-size:11px;line-height:110%;">Leave empty (or 0) for 100% width.</span>', // qa_lang('q2apro_sceditor_lang/...'),
				'suffix' => 'px',
				'tags' => 'name="q2apro_sceditor_editorwidth"',
				'value' => (int)qa_opt('q2apro_sceditor_editorwidth'),
			);
			
			$fields[] = array(
				'type' => 'number',
				'label' => 'Default editor height:', // qa_lang('q2apro_sceditor_lang/...'),
				'suffix' => 'px',
				'tags' => 'name="q2apro_sceditor_editorheight"',
				'value' => (int)qa_opt('q2apro_sceditor_editorheight'),
			);
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => 'Editor resizable<br /><span style="font-size:11px;line-height:110%;">Provides a little handle at bottom-right of the editor.</span>', // qa_lang('q2apro_sceditor_lang/...'),
				'tags' => 'name="q2apro_sceditor_editorresizable"',
				'value' => (int)qa_opt('q2apro_sceditor_editorresizable'),
			);
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => 'Expand editor height<br /><span style="font-size:11px;line-height:110%;">Expands the editor to the height of the text inserted.</span>', // qa_lang('q2apro_sceditor_lang/...'),
				'tags' => 'name="q2apro_sceditor_editorexpand"',
				'value' => (int)qa_opt('q2apro_sceditor_editorexpand'),
			);
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => 'Text Right-To-Left<br /><span style="font-size:11px;line-height:110%;">For languages such as Arabic, Aramaic, Southern Balochi, Bakthiari, Sorani, Dhivehi, Persian, Gilaki, Hebrew, Kurdish, Mazanderani, Western Punjabi, Pashto, Sindhi, Uyghur, Urdu, Yiddish</span>', // qa_lang('q2apro_sceditor_lang/...'),
				'tags' => 'name="q2apro_sceditor_editorrtl"',
				'value' => (int)qa_opt('q2apro_sceditor_editorrtl'),
			);
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => 'Enable preview window below editor',
				'tags' => 'name="q2apro_sceditor_preview_enabled" id="q2apro_sceditor_preview_enabled"',
				'value' => (int)qa_opt('q2apro_sceditor_preview_enabled'),
			);
			$fields[] = array(
				'type' => 'checkbox',
				'id' => 'q2apro_sceditor_mathjax',
				'label' => 'Enable Mathjax LaTeX-parsing in preview window',
				'tags' => 'name="q2apro_sceditor_mathjax"',
				'value' => (int)qa_opt('q2apro_sceditor_mathjax'),
			);
			$fields[] = array(
				'type' => 'checkbox',
				'id' => 'q2apro_sceditor_mathjax_global',
				'label' => 'Enable LaTeX-parsing (loads Mathjax CDN script on all question pages)',
				'tags' => 'name="q2apro_sceditor_mathjax_global"',
				'value' => (int)qa_opt('q2apro_sceditor_mathjax_global'),
			);
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => 'Allow uploads of files',
				'tags' => 'name="q2apro_sceditor_upload_enabled" id="q2apro_sceditor_upload_enabled"',
				'value' => (int)qa_opt('q2apro_sceditor_upload_enabled'),
			);
			$fields[] = array(
				'type' => 'checkbox',
				'id' => 'q2apro_sceditor_upload_images',
				'label' => 'Allow images to be uploaded', // qa_lang('q2apro_sceditor_lang/...'),
				'tags' => 'name="q2apro_sceditor_upload_images" id="q2apro_sceditor_upload_images"',
				'value' => (int)qa_opt('q2apro_sceditor_upload_images'),
			);
			$fields[] = array(
				'type' => 'checkbox',
				'id' => 'q2apro_sceditor_upload_docs',
				'label' => 'Allow other files to be uploaded, e.g. documents like pdf, doc, xls or zip-files',
				'tags' => 'name="q2apro_sceditor_upload_docs" id="q2apro_sceditor_upload_docs"',
				'value' => (int)qa_opt('q2apro_sceditor_upload_docs'),
			);
			$fields[] = array(
				'type' => 'number',
				'id' => 'q2apro_sceditor_upload_max_size',
				'label' => 'Maximum size of uploads:',
				'suffix' => 'MB (max '.$this->bytes_to_mega_html(qa_get_max_upload_size()).')',
				'tags' => 'name="q2apro_sceditor_upload_max_size" id="q2apro_sceditor_upload_max_size"',
				'value' => $this->bytes_to_mega_html(qa_opt('q2apro_sceditor_upload_max_size')),
			);
			$fields[] = array(
				'type' => 'number',
				'id' => 'q2apro_sceditor_upload_maximgwidth',
				'label' => 'Maximum width of uploaded image:',
				'suffix' => 'px',
				'tags' => 'name="q2apro_sceditor_upload_maximgwidth" id="q2apro_sceditor_upload_maximgwidth"',
				'value' => (int)qa_opt('q2apro_sceditor_upload_maximgwidth'),
			);

			// hide max-size-input if allow-images is disabled
			qa_set_display_rules($qa_content, array(
				'q2apro_sceditor_upload_images' => 'q2apro_sceditor_upload_enabled',
				'q2apro_sceditor_upload_docs' => 'q2apro_sceditor_upload_enabled',
				'q2apro_sceditor_upload_max_size' => 'q2apro_sceditor_upload_enabled',
				'q2apro_sceditor_upload_maximgwidth' => 'q2apro_sceditor_upload_enabled',
				// hide mathjax-checkbox if preview is disabled
				'q2apro_sceditor_mathjax' => 'q2apro_sceditor_preview_enabled',
			));
			
			// link to q2apro.com
			$fields[] = array(
				'type' => 'static',
				'note' => '<span style="font-size:75%;color:#789;">'.strtr( qa_lang('q2apro_sceditor_lang/contact'), array( 
							'^1' => '<a target="_blank" href="https://www.q2apro.com/plugins/sceditor">',
							'^2' => '</a>'
						  )).'</span>',
			);
			
			// finish him
			return array(           
				'ok' => ($ok && !isset($error)) ? $ok : null,
				'fields' => $fields,
				'buttons' => array(
					array(
						'label' => qa_lang_html('main/save_button'),
						'tags' => 'name="q2apro_sceditor_save"',
					),
				),
			);
			
		} // end admin form

		
		// numerical value indicating your viewer's ability to render the supplied $content in $format, as retrieved from Q2A's database. 
		// should return 1.0 to indicate perfect ability, and 0.0 for complete inability.
		function calc_quality($content, $format)
		{
			if ($format=='html')
				return 1.0;
			elseif ($format=='')
				return 0.8;
			else
				return 0;
		}

		// returns an HTML-based field for the editor
		function get_field(&$qa_content, $content, $format, $fieldname, $rows) 
		{
		
			// The $fieldname parameter contains the HTML element name that you should use - if your editor outputs multiple HTML elements, use $fieldname as a prefix
			// To output custom HTML for your editor, return array('type' => 'custom', 'html' => '[the html]')

			// sceditor css and script are loaded with the layer
			
			// language
			$editorlocale = qa_opt('q2apro_sceditor_editorlocale'); // 'lt'
			
			// xhtml or bbcode
			$editorplugin = qa_opt('q2apro_sceditor_editorplugin'); // 'xhtml'
			
			// available buttons: bold italic underline strike subscript superscript left center right justify font size color removeformat pastetext bulletlist orderedlist table code quote horizontalrule image email link unlink emoticon youtube date time ltr rtl print maximize source
			$editortoolbar = qa_opt('q2apro_sceditor_toolbar'); // 'bold,italic,underline|color|orderedlist,bulletlist|link,table';
			
			// for font selector
			$editorfonts = 'Arial,Arial Black,Comic Sans MS,Courier New,Georgia,Impact,Sans-serif,Serif,Times New Roman,Trebuchet MS,Verdana';
			
			$editorcolors = qa_opt('q2apro_sceditor_editorcolors'); // '#000|#F00|#11C11D|#00F|#B700B7|#FF8C00|#008080|#808080|#D3D3D3';
			
			// check if toolbar contains "emoticon" option
			$editoremoticons = (strpos($editortoolbar, 'emoticon')!==false) ? 'true' : 'false';
			
			$editorresizable = (qa_opt('q2apro_sceditor_editorresizable') == 1) ? 'true' : 'false'; // false or true

			// right to left for some languages
			$editorrtl = (qa_opt('q2apro_sceditor_editorrtl') == 1) ? 'true' : 'false'; 
			
			$editorexpand = 'false';
			
			$editorheight = qa_opt('q2apro_sceditor_editorheight'); // '350';
			
			// not as setting
			$editorwidth = qa_opt('q2apro_sceditor_editorwidth'); // '500'; // '100%'; 
			if( empty($editorwidth) ){
				$editorwidth = '\'100%\'';
			}
			// workaround for mobiles
			// sceditor bug: https://github.com/samclarke/SCEditor/issues/315
			if(qa_is_mobile_probably()) {
				$editorwidth = 330;
			}
			
			// important: IF format is not set i.e. text, not HTML, convert the line breaks to BRs
			// otherwise all text will be on one line
			if(empty($format)) {
				$content = nl2br($content);
			}
			// sceditor bug: https://github.com/samclarke/SCEditor/issues/330
			// escape &lt; character ampersand, open issue 
			$content = str_replace('&lt;', '&amp;lt;', $content);
			
			// set textarea for content
			$html = '<textarea name="'.$fieldname.'" id="'.$fieldname.'" rows="12" cols="40" class="qa-form-tall-text">'.$content.'</textarea>';
			
			// image upload
			if(qa_opt('q2apro_sceditor_upload_enabled')) {
			
				$uploadlabel = qa_lang('q2apro_sceditor_lang/docs_and_image_upload_label');
				if(qa_opt('q2apro_sceditor_upload_images') && !qa_opt('q2apro_sceditor_upload_docs')) {
					$uploadlabel = qa_lang('q2apro_sceditor_lang/image_upload_label');
				}
				else if(!qa_opt('q2apro_sceditor_upload_images') && qa_opt('q2apro_sceditor_upload_docs')) {
					$uploadlabel = qa_lang('q2apro_sceditor_lang/docs_upload_label');
				}
				
				// image upload
				$html .= '
					<div id="html5image" enctype="multipart/form-data">
						<p style="margin-top:15px;">'.$uploadlabel.' 
							<input id="scupload_image_'.$fieldname.'" name="imgfile" type="file" style="border:0;" /> 
						</p>
						<input id="scupload_imgbtn" type="button" value="Upload" style="display:none;" />
					</div>
					<progress style="display:none;"></progress>
				';
			}
			
			// preview
			if(qa_opt('q2apro_sceditor_preview_enabled')) {
				$html .= '
					<p class="textpreview_label" style="font-size:14px;margin:20px 0 5px 0;">'.qa_lang('q2apro_sceditor_lang/preview').'</p>
					<div id="textpreview_'.$fieldname.'" class="previewfield"></div>
					';
			}
			
			$html .= "
			<script type=\"text/javascript\">
				$(document).ready(function(){
					// in case plugin could not be loaded, wrong path?
					if(typeof $.fn.sceditor == 'undefined') {
						return;
					}
					
					$('textarea[name=\"".$fieldname."\"]').sceditor({
						plugins: '".$editorplugin."',
						style: '".$this->urltoroot."minified/jquery.sceditor.default.min.css',
						locale: '".$editorlocale."',
						toolbar: '".$editortoolbar."',
						fonts: '".$editorfonts."',
						colors: '".$editorcolors."',
						resizeEnabled: ".$editorresizable.",
						autoExpand: ".$editorexpand.",
						width: ".$editorwidth.",
						height: ".$editorheight.",
						emoticonsRoot: '" . qa_path_absolute(sprintf('%s/%s/', basename(QA_PLUGIN_DIR), basename(__DIR__))) . "',
						emoticonsEnabled: ".$editoremoticons.",
						rtl: ".$editorrtl.",
						autoUpdate: false,
					});
					
					// shortcut ctrl+enter for submitting the form
					$('textarea[name=\"".$fieldname."\"]').sceditor('instance').addShortcut('ctrl+enter', function () {
						submitform_sc($('textarea[name=\"".$fieldname."\"]').get(0).form);
					});
					function submitform_sc(form) {
						$(form).submit();
					}
					
					// save this editor instance so the iframe can access it
					window.sceditorInstance_".$fieldname." = $('textarea[name=\"".$fieldname."\"]').sceditor('instance');
					
					// define custom shortcuts
					$('textarea[name=\"".$fieldname."\"]').sceditor('instance').addShortcut('ctrl+l', 'link');
					
					$('#scupload_image_".$fieldname."').change( function() {
						// clear file dialog input because Chrome/Safari do not upload same filename twice
						uploadimgfile();
						$('.html5image input[type=\"file\"]').val(null);
					});
					
					// upload after user has chosen the image
					function uploadimgfile() {
						console.log('submitting file via html5 ajax');
						
						// check for maximal image size
						var maximgsize = ".qa_opt('q2apro_sceditor_upload_max_size').";
						var imgsize = $('#scupload_image_".$fieldname."')[0].files[0].size;
						// console.log(maximgsize + ' | ' + imgsize);
						if(imgsize > maximgsize) { 
							var img_size = (Math.round((imgsize/1024/1024) * 100) / 100);
							var maximg_size = (Math.round((maximgsize / 1024 / 1024) * 100) / 100);
							var errormsg = ('".qa_lang('main/max_upload_size_x')."').replace('^', maximg_size+' Mb');
							alert(errormsg);
							return;
						}

						var imgdata = new FormData();
						// append file to object
						imgdata.append('imgfile', $('#scupload_image_".$fieldname."')[0].files[0] );

						$.ajax({
							url: '".qa_path('sceditor-upload')."', // server script to process data
							type: 'POST',
							xhr: function() {  
								// custom XMLHttpRequest
								var myXhr = $.ajaxSettings.xhr();
								if(myXhr.upload){ 
									// check if upload property exists
									myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
								}
								return myXhr;
							},
							// ajax events
							beforeSend: beforeSendHandler,
							success: completeHandler,
							error: errorHandler,
							// form data
							data: imgdata,
							// options to tell jQuery not to process data or worry about content-type
							cache: false,
							contentType: false,
							processData: false
						});
					};
					function beforeSendHandler(e) {
						$('progress').show();
					}
					function progressHandlingFunction(e){
						if(e.lengthComputable){
							// html5 progressbar
							$('progress').attr({value:e.loaded,max:e.total});
						}
					}
					function completeHandler(e) {
						parent.window.sceditorInstance_".$fieldname.".wysiwygEditorInsertHtml(e);
						$('progress').hide();
						".(qa_opt('q2apro_sceditor_preview_enabled') ? 'doPreview();' : '').
						"
					}
					function errorHandler(e) {
						parent.window.sceditorInstance_".$fieldname.".wysiwygEditorInsertHtml('Upload failed: '+e);
					}

					var edcontent = '';
					var edcontent_former = '';
					var ignoreKeys = new Array('Up', 'Down', 'Right', 'Left', 'ArrowUp', 'ArrowDown', 'ArrowRight', 'ArrowLeft', 'Alt', 'Shift', 'Control');
					var texloaded = false;
					var jaxloaded = false;
					";
			if(qa_opt('q2apro_sceditor_preview_enabled')) {
				$html .= "
					// updating the preview after text has entered
					$('textarea[name=\"".$fieldname."\"]').sceditor('instance').keyUp(function(e) {
						// ignore navigation keys, problem when marking by shift+cursors that marking gets lost by doPreview triggered after 500ms
						/*
						if($.inArray(e.key, ignoreKeys)!=-1) {
							return;
						}
						*/
						
						doPreview();
					});
					
					// jquery keyup() delay to not update on each keystroke (performance)
					var delay = ( function() {
						var timer = 0;
						return function(callback, ms) {
							clearTimeout (timer);
							timer = setTimeout(callback, ms);
						};
					})();
					
					function doPreview() { 
						delay(function(){
							edcontent = $('textarea[name=\"".$fieldname."\"]').sceditor('instance').val();
							// only render if value has changed
							if(edcontent == edcontent_former) {
								return;
							}
							edcontent_former = edcontent;
							
							// assign to preview
							$('#textpreview_".$fieldname."').html( edcontent );
							
							".
							(qa_opt('q2apro_sceditor_mathjax') ? "
							// mathjax tex stuff
							if( !texloaded && (edcontent.indexOf('$$')!=-1 || edcontent.indexOf('\\\(')!=-1) ) {
								texloaded = true;
								// insert mathjax-config for linebreak option
								$('head').append(
								'<script type=\"text/x-mathjax-config\"> MathJax.Hub.Config({ \"HTML-CSS\": { scale:100, linebreaks: { automatic: true } }, SVG: { linebreaks: { automatic: true } }, displayAlign: \"left\" }); </'+'script>'
								);
								var mjaxURL = 'https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS_HTML&locale=de';
								// as soon mathjax is loaded, assign it to preview
								$.getScript(mjaxURL, function() {
									jaxloaded = true;
									// mathjax loaded, call it
									MathJax.Hub.Queue(['Typeset', MathJax.Hub, edcontent]);
								});
							}
							else if(jaxloaded) {
								// tex loaded already, update tex in preview
								MathJax.Hub.Queue([\"Typeset\", MathJax.Hub, 'textpreview_".$fieldname."']); // or instead edcontent 'textpreview'
							}
							" : "")
							."
						}, 600 );
					} // end doPreview
					";
			} // end q2apro_sceditor_preview_enabled
			
			$html .= "
				}); // end jquery ready
			</script>
			";
			
			return array(
				'type' => 'custom', 
				'html' => $html
			);
		}
	
		// javascript after finishing loading the editor, triggered on reveal
		function load_script($fieldname) 
		{
			return 'console.log("sceditor loaded")';
		}

		// javascript that brings the editor field into focus
		function focus_script($fieldname) 
		{
			return "window.sceditorInstance_".$fieldname." = $('textarea[name=\"".$fieldname."\"]').sceditor('instance');
					window.sceditorInstance_".$fieldname.".focus();
					console.log('sceditor focused')";
			// return 'parent.window.sceditorInstance_'.$fieldname.'.focus();';					
		}
		
		// Javascript that prepares the editor content for submission via the enclosing form 
		function update_script($fieldname) 
		{
			// write html text from sceditor-iframe to textarea - important!
			$jscode = "$('textarea[name=\'".$fieldname."\']').val( $('textarea[name=\'".$fieldname."\']').data('sceditor').val() );";
			// debugging:
			// $jscode .= "console.log( 'Write into textfield: '+( $('textarea[name=\'".$fieldname."\']').data('sceditor').val() ) ); return false;";
			return $jscode;
		}
	
		
		// retrieves the content from your editor, convert it for storage in Q2A's database, see function qa_get_post_content() in qa-app-format.php
		function read_post($fieldname) 
		{
			// incoming POST as string
			$html = qa_post_text($fieldname);
			
			// always return HTML
			/*
			return array(
				'format' => 'html',
				'content' => qa_sanitize_html($html, false, true), // qa_sanitize_html() is ESSENTIAL for security
			);
			*/
			
			// remove <p>, <br>, etc... since those are OK in text
			$ishtmlformat = preg_replace('/<\s*\/?\s*(br|p)\s*\/?\s*>/i', '', $html);
			if (preg_match('/<.+>/', $ishtmlformat)) {
				return array(
					'format' => 'html',
					'content' => qa_sanitize_html($html, false, true), // qa_sanitize_html() is ESSENTIAL for security
				);
			}
			else {
				// only text - convert to text
				$viewer = qa_load_module('viewer', '');
				return array(
					'format' => '',
					'content' => $viewer->get_text($html, 'html', array()),
				);
			}
		} // end read_post
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/