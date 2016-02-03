<?php
class Image_Handler
{
	const THUMBNAILS_DIRECTORY = 'thumbs';

	protected static $_image_types = array
	(
		// extension => mime image type
		'jpg' => 'jpeg',
		'jpeg' => 'jpeg',
		'png' => 'png'
	);

	protected static function getImagesDir()
	{
		return Project_Navigator::get('images_dir');
	}

	protected static function getImageNotAvailable()
	{
		return Project_Navigator::get('image_not_available');
	}

	protected static function getImageNotFound()
	{
		return Project_Navigator::get('image_not_found');
	}

	protected static function getRealFilename($filename)
	{
		if ($filename[0] == '/')
			$filename = substr($filename, 1);
		return '.' . self::getImagesDir() . $filename;
	}

	protected static function renderFile($filename)
	{
		$path_parts = pathinfo($filename);

		if (!array_key_exists($path_parts['extension'], self::$_image_types))
			throw new Unsupported_Feature_Exception('Automatic thumbnails not supported for this image type');

		$image_type = self::$_image_types[$path_parts['extension']];
		header("Content-Type: image/$image_type");
		readfile($filename);
	}

	protected static function renderImageFile($image)
	{
		$filename = self::getRealFilename($image);
		if (is_file($filename))
			self::renderFile($filename);
		else
		{
			$image_not_found = self::getImageNotFound();
			if ($image !== $image_not_found)
				self::renderImageFile($image_not_found);
			else
				throw new Data_Insufficient_Exception("image: $image_not_found");
		}
	}

	protected static function createThumb($image, $target, $size, $quality)
	{
		$source = self::getRealFilename($image);
		//ini_set('memory_limit', '128M');
		$thumb = new Thumbnail($source);
		if ($size)
		{
			$size_array = explode('x', $size);
			if (is_numeric($size_array[0]))
				$thumb->size_width($size_array[0]);
			if (is_numeric($size_array[1]))
				$thumb->size_height($size_array[1]);
		}
		
		if (is_numeric($quality))
			$thumb->quality = $quality;
		$thumb->process();
		if (!$thumb->save($target))
			throw new Image_Handler_Exception($image, $thumb->error_msg);
	}


	public static function renderImage($image, $size = '', $quality = '')
	{		
		if ($size === '' && $quality === '')
			self::renderImageFile($image);
		else
		{
			$path_parts = pathinfo($image);
			$dirname = $path_parts['dirname'] . '/' . self::THUMBNAILS_DIRECTORY . '/' . $size;
			if (substr($dirname, 0, 2) == './')
				$dirname = substr($dirname, 2);
			if ($quality !== '')
				$dirname .= '@' . $quality;
			$dirname = self::getRealFilename($dirname);
			if (is_dir($dirname))
			{
				$filename = "$dirname/{$path_parts['basename']}";
				if (!file_exists($filename))
					$status = self::createThumb($image, $filename, $size, $quality);
				self::renderFile($filename);
			}
			else
				self::renderImageFile(self::getImageNotAvailable());
		}	
	}

	public static function removeImage($image)
	{
		if ($image === '' || $image === null)
			throw new Data_Insufficient_Exception('image');
		$image_file = self::getRealFilename($image);
		@unlink($image_file);
		$path_parts = pathinfo($image);
		$thumbs_dir = $path_parts['dirname'] . '/' . self::THUMBNAILS_DIRECTORY . '/';
		if (substr($thumbs_dir, 0, 2) == './')
			$thumbs_dir = substr($thumbs_dir, 2);
		$real_thumbs_dir = self::getRealFilename($thumbs_dir);
		$glob_pattern = $real_thumbs_dir . '*/' . $path_parts['basename'];
		$thumbs_files = glob($glob_pattern, GLOB_NOSORT);
		foreach ($thumbs_files as $thumb_file)
			@unlink($thumb_file);
	}
}
?>