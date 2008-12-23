<?php
/**
 * Post-It Plugin
 * 
 * This plugin has one purpose: be the solution to all BASIC needs of image uploading, resizing, and 
 * thumbnail generation. It can do a lot of things, and should help you if you do not need anything too 
 * specific.
 *
 * I created this plugin because there's no other image plugin or component out there able to do what 
 * I needed it to do. In some pages I need to upload, in other page I need to upload and resize, and 
 * in a third page I need to upload, resize and generate a thumbnail. No other plugin allows me to not
 * resize, or not to generate a thumbnail.
 *
 * The main interface to pass parameters to this plugin is incommon in PHP, but very used in the Ruby
 * world. Parameters are passed through chained methods, like this:
 * $postit->image("pic")->size("300x400")->save_to("my_pictures")->upload();
 *
 * To insert this plugin's component in your controller, declare it like this:
 * var $components = array('Postit.Postit');
 *
 * @author Leonardo Bighi
 * @package post-it
 * @version 1.0.1
 * @link http://www.leonardobighi.com/post-it
 */

/*
	NOTE:
Througout this file there are some method aliases. They are here to allow shorter method calls, and 
method calls in portuguese.
*/
 
class PostitComponent extends Object {
	// name of the field (from the form) that holds the uploaded image
	var $pic_field = "image";
	
	// this will hold file extension
	var $filetype = "";
	
	// main picture data
	var $pic_w = 0;
	var $pic_h = 0;
	var $pic_folder = "img";
	var $pic_name = null;
	var $pic_max = false;
	
	// thumbnail data
	var $thumb_w = 0;
	var $thumb_h = 0;
	var $thumb_folder = "img/mini";
	var $thumb_name = null;
	var $thumb_max = false;
	var $thumb = false; // flag that indicates width and height should always expand to max possible size
	
	// another flag that indicates if methods are going to handle main picture or thumbnail
	// possible values: 'pic' or 'thumb'
	var $img = 'pic';
	
	// this method defines main picture name
	function image($form_field) {
		$this->pic_field = $form_field;
		return $this;
	}
	
	function picture($form_field) { return $this->image($form_field); }
	function imagem($form_field) { return $this->image($form_field); }	// in portuguese
	
	// this method defines main picture size. It expects Width and Height, separated by an 'x' like ('200x300')
	// if any of the dimensions passed is 0, that size will not change. Example: ('0x150') will not change width.
	// is one of the dimensions passes is -1, it will automatically be resized to keep original image scale
	function size($size) {
		list($this->pic_w, $this->pic_h) = explode('x', $size);
		$this->img = 'pic';
		return $this;
	}
	
	function tamanho($size) { return $this->size($size); }
	
	// image maximization: if width or height is smaller than required size, it will not get bigger by default.
	// is maximization is activated, width and height will always expand to fill required size

	// This activates maximization of image dimensions. This method can affect main picture or thumbnail
	function maximize() {
		if($this->img == "pic") {
			return $this->maximize_picture();
		} elseif($this->img == "thumb") {
			return $this->maximize_thumbnail();
		}
	}
	
	function max() { return $this->maximize(); }
	function maximiza() { return $this->maximize(); }			// in portuguese
	
	// Activates only the main picture maximization. It is preferable to call mazimize() instead
	// this method is automatically called by mazimize()
	function maximize_picture() {
		$this->pic_max = true;
		$this->img = "pic";
		return $this;
	}
	
	function max_picture() { return $this->maximize(); }
	function max_pic() { return $this->maximize(); }
	function maximiza_imagem() { return $this->maximize(); } 	// in portuguese
	function max_imagem() { return $this->maximize(); }			// in portuguese
	
	// activate only thumbnail maximization. It is preferable to call mazimize() instead
	// this method is automatically called by mazimize()
	function maximize_thumbnail() {
		$this->thumb_max = true;
		$this->img = "thumb";
		return $this;
	}
	
	function max_thumb() { return $this->maximize_thumbnail(); }
	function max_thumbnail() { return $this->maximize_thumbnail(); }
	function maximiza_miniatura() { return $this->maximize_thumbnail(); } // in portuguese
	
	// activate maximization of main picture AND thumbnail
	function maximize_both() {
		$this->maximize_picture();
		$this->maximize_thumbnail();
		return $this;
	}
	
	function max_both() { return $this->mazimize_both(); }
	function maximiza_ambas() { return $this->mazimize_both(); }		// in portuguese
	function max_ambas() { return $this->mazimize_both(); }			// in portuguese
	
	// This method defines image height. It can affect main picture or thumbnail.
	function height($h) {
		// if $h is invalid, raise exception
		if( !is_numeric($h) || empty($h) || ($h < 1) ) {
			throw new Exception("Parâmetro inválido");
		}
		
		if($this->img == "pic") {
			$this->pic_h = $h;
		} elseif ($this->img == "thumb") {
			$this->thumb_h = $h;
		}
		return $this;
	}
	
	function h($h) { return $this->height($h); }
	function altura($h) { return $this->height($h); }		// in portuguese
	
	// This method defines image width. It can affect main picture or thumbnail.
	function width($w) {
		// if $w is invalid, raise exception
		if( !is_numeric($w) || empty($w) || ($w < 1) ) {
			throw new Exception("Parâmetro inválido");
		}
		
		if($this->img == "pic") {
			$this->pic_w = $w;
		} elseif ($this->img == "thumb") {
			$this->thumb_w = $w;
		}
		return $this;
	}
	
	function w($w) { return $this->width($w); }
	function largura($w) { return $this->width($w); } 		// in portuguese
	
	// Defines the folder where image will be saved. This can affect main picture or thumbnail.
	// it is not necessary to add 'img/' at the begining
	function to_folder($folder) {
		
		if($this->img == "pic") {
			return $this->picture_to_folder($folder);
		} elseif ($this->img == "thumb") {
			return $this->thumbnail_to_folder($folder);
		}
	}
	
	function folder($folder) { return $this->to_folder($folder); }
	function dir($folder) { return $this->to_folder($folder); }
	function save_to($folder) { return $this->to_folder($folder); }
	function na_pasta($folder) { return $this->to_folder($folder); }	// in portuguese
	
	// defines the folder where main picture will be saved. It is more elegant to call to_folder instead.
	function picture_to_folder($folder) {
		// if there's no 'img/' at the begining, add it
		$folder = $this->add_img($folder);
		
		$this->pic_folder = $folder;
		$this->img = "pic";
		return $this;
	}
	
	function pic_folder($folder) { return $this->picture_to_folder($folder); }
	function pic_dir($folder) { return $this->picture_to_folder($folder); }
	function imagem_na_pasta($folder) { return $this->picture_to_folder($folder); }	// in portuguese
	
	// defines the folder where thumbnail will be saved. It is more elegant to call to_folder instead.
	function thumbnail_to_folder($folder) {
		// if there's no 'img/' at the begining, add it
		$folder = $this->add_img($folder);
		
		$this->thumb_folder = $folder;
		$this->img = "thumb";
		return $this;
	}
	
	function thumb_folder($folder) { return $this->thumbnail_to_folder($folder); }	
	function thumb_dir($folder) { return $this->thumbnail_to_folder($folder); }	
	function miniatura_na_pasta($folder) { return $this->thumbnail_to_folder($folder); }	// in portuguese
		
	// set both main picture AND thumbnail to the designed folder
	function both_to_folder($folder) {
		$folder = $this->add_img($folder);
		
		$this->pic_folder = $folder;
		$this->thumb_folder = $folder;
		
		return $this;
	}
	
	function save_both_to($folder) { return $this->both_to_folder($folder); }
	function both_dir($folder) { return $this->both_to_folder($folder); }
	function ambas_na_pasta($folder) { return $this->both_to_folder($folder); } // in portuguese
	
	// This methods let the plugin know that a thumbnail should be generated. Is the parameter is passed, 
	// it also defines thumbnail width and height.
	// $size should be width and height, separated by 'x', like with_thumbnail('200x200')
	function with_thumbnail($size = null) {
		$this->thumb = true;
		
		// is $size was defined, set thumb width and height
		if(!empty($size)) {
			list($this->thumb_w, $this->thumb_h) = explode('x', $size);
		}
		
		$this->img = "thumb";
		return $this;
	}
	
	function thumb($size = null) { return $this->with_thumbnail($size); }
	function with_thumb($size = null) { return $this->with_thumbnail($size); }
	function copy($size = null) { return $this->with_thumbnail($size); } 		
	function com_miniatura($size = null) { return $this->with_thumbnail($size); } 	//in portuguese
	function miniatura($size = null) { return $this->with_thumbnail($size); } 		//in portuguese
	function copia($size = null) { return $this->with_thumbnail($size); } 		//in portuguese
	
	// This set the name that the image will get when moved to fhe final directory
	// This method can affect picture or thumbnail
	function name($name) {
		if($this->img == "pic") {
			return $this->picture_name($name);
		} elseif($this->img == "thumb") {
			return $this->thumbnail_name($name);
		}
	}
	
	function nome($name) { return $this->name($name); }	// in portuguese
	
	// this method defines main picture name. It is more elegant to call name() instead.
	function picture_name($name) {
		$this->pic_name = $name;
		$this->img = "pic";
		return $this;
	}
	
	function pic_name($name) { return $this->picture_name($name); }
	function imagem_nome($name) { return $this->picture_name($name); } // in portuguese
	
	// this method defines thumbnail name. It is more elegant to call name() instead.
	function thumbnail_name($name) {
		$this->thumb_name = $name;
		$this->img = "thumb";
		return $this;
	}
	
	function thumb_name($name) { return $this->thumbnail_name($name); }
	function miniatura_nome($name) { return $this->thumbnail_name($name); } // in portuguese
	
	//TODO remover essa função depois que terminar de desenvolver
	function miolos() {
		debug($this);
	}
	
	// Most important method of this plugin. This method takes all parameteres passed, and 'do the stuff'
	// (thumbnail generation is handled by rezise_img method, but you don't need to call it)
	function upload() {
		$tempfolder = "/img/temp";
		
		// Check if directories exist. If not, then create them.
		if(!is_dir($this->pic_folder)) mkdir($this->pic_folder, 0777, true);
		if(!is_dir($this->thumb_folder)) mkdir($this->thumb_folder, 0777, true);
		if(!is_dir($tempfolder)) mkdir($tempfolder, 0777, true);
		
		// Find image file extension, and lowercase it.
		$this->filetype = $this->get_file_extension($this->pic_field['name']);
		$this->filetype = low($this->filetype);
		
		// defines valid extensions, and check image file extension is valid
		$extensions = array('jpeg', 'jpg', 'gif', 'png');
		if(array_search($this->filetype, $extensions) !== false) {
			// find image size
			$img_size = getimagesize($this->pic_field['tmp_name']);
		} else {
			// if invalid extension, rais exception
			throw new Exception("Extensão inválida na imagem");
			return;
		}
		
		// if image name is not defined, generate one from system clock
		if( empty($this->pic_name) ) {
			$this->pic_name = str_replace(".", "", strtotime("now"));
		}
		
		// if thumb should be generated, but has no name, then name it like main picture, but begining with thumb_
		if( empty($this->thumb_name) ) {
			$this->thumb_name = "thumb_" . $this->pic_name;
		}
		
		// define final names, with file extension
		$this->pic_name = $this->pic_name . "." . $this->filetype;
		$this->thumb_name = $this->thumb_name . "." . $this->filetype;
		
		// defines final file directories (file path and filename)
		$tempfile = $tempfolder . "/" . $this->pic_name;
		$imagefile = $this->pic_folder . "/" . $this->pic_name;
		$thumbfile = $this->thumb_folder . "/" . $this->thumb_name;
		
		if(is_uploaded_file($this->pic_field['tmp_name'])) {
			// copy image to temporary directory
			if( !copy($this->pic_field['tmp_name'], $tempfile) ) {
				// if something goes wrong
				throw new Exception("Erro ao fazer upload de arquivo.");
				die("PERIGO PERIGO PERIGO");
			} else {
				// resize (if needed) and move to desired folder
				$this->resize_img($tempfile, $this->pic_w, $this->pic_h, $imagefile);
				
				// if thumb is needed, resize temp picture and move thumb to desired folder
				if($this->thumb === true) {
					$this->resize_img($tempfile, $this->thumb_w, $this->thumb_h, $thumbfile);
				}
				
				// delete temporary file
				unlink($tempfile);
			}
		}
		
		// image was correctly uploaded, then return main picture filename
		return $this->pic_name;
	} // end of upload()
		
	function send() { return $this->upload(); }
	function up() { return $this->upload(); }
	function envia() { return $this->upload(); } // in portuguese
	
	// This is the second most important method of the plugin. It handles image resizing and moving
	// It is automatically called by upload() method
	// $imgname is the name of the unresized image (the one on the temporary directory)
	// $w and $h stands for Width and Height
	// $filename should be the complete path, containg filename and extension too
	protected function resize_img($imgname, $w, $h, $filename)	{
		// generate basic image based on extension
		switch($this->filetype) {
			case "jpeg":
			case "jpg":
			$img_src = imagecreatefromjpeg($imgname);
			break;
			case "gif":
			$img_src = imagecreatefromgif($imgname);
			break;
			case "png":
			$img_src = imagecreatefrompng($imgname);
			break;
	  }

		// finds width and height from original picture
		$true_width = imagesx($img_src);
		$true_height = imagesy($img_src);
		
		// if $w or $h equals 0, original size is not changed
		if ($w == 0) $final_w = $true_width;
		if ($h == 0) $final_h == $true_height;
		
		// if $w is bigger than 0, then there's a size limit
		if ($w > 0) {
			// if original width is bigger than desired width, than resize it
			if ($true_width >= $w) $final_w = $w;
			
			// is original width is smaller then desired width, then it may be resized or not
			if ($true_width < $w) {
				if($this->pic_max === true) $final_w = $w;	// maximization is on, resize
				else $final_w = $true_width;						// maximization is off, don't resize
			}
			
			// se $h is -1, it is automatically resized to mantain scale
			if($w == -1) {
				$ratio = $true_width / $final_w;
				$final_h = $true_height / $ratio;
			}	
		}
		
		// same as before, but $h instead of $w
		if ($h > 0) {
			// if original height is bigger than desired height, than resize it
			if ($true_height >= $h) $final_h = $h;
			
			// is original height is smaller then desired height, then it may be resized or not
			if ($true_height < $h) {
				if($this->pic_max === true) $final_h = $h; 	// maximization is on, resize
				else $final_h = $true_height;						// maximization is off, don't resize
			}
			
			// se $w is -1, it is automatically resized to mantain scale
			if($w == -1) {
				$ratio = $true_height / $final_h;
				$final_w = $true_width / $ratio;
			}
		}
		
		$img_des = ImageCreateTrueColor($final_w,$final_h);
		imagecopyresampled ($img_des, $img_src, 0, 0, 0, 0, $final_w, $final_h, $true_width, $true_height);
		
		// Save the resized image
		switch($this->filetype)
		{
			case "jpeg":
			case "jpg":
			 imagejpeg($img_des,$filename,80);
			 break;
			 case "gif":
			 imagegif($img_des,$filename,80);
			 break;
			 case "png":
			 imagepng($img_des,$filename,80);
			 break;
		}
	}
	
	function get_file_extension($str) {
		$i = strrpos($str,".");
		if (!$i) { return ""; }
		$l = strlen($str) - $i;
		$ext = substr($str,$i+1,$l);
		return $ext;
	}
	
	// internal method
	// checks if given folder begins with 'img/'. If not, add img/ to it.
	protected function add_img($folder) {
		if(strpos($folder, "img/") !== 0) {
			$folder = "img/" . $folder;
		}
		return $folder;
	}
} 

?>
