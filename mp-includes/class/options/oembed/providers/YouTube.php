<?php
if (!extension_loaded('gd')) return;

class MP_oembed_provider_YouTube extends MP_oembed_provider_
{
	public $id = 'YouTube';

	function data2html( $html, $data, $url )
	{

		$yu = explode('/', $data->thumbnail_url);
/* thumbnail */	$yt = array_pop($yu); 
/* quality   */ $yq = substr($yt, 0, 2); if (!in_array($yq, array('hq','mq'))) $yq = 'mq';
/* ref video */ $yr = array_pop($yu);
/* new thumb */ $p = "tmp/{$this->id}_{$yr}.png"; $ynf = MP_ABSPATH . $p; $ynu = MP_PATH . $p;

// modifying image
		$image = imagecreatefromjpeg($data->thumbnail_url);

// if high quality, we create a new canvas without the black bars
		if ($yq == 'hq')
		{
			$canvas = imagecreatetruecolor(480, 270);
			imagecopy($canvas, $image, 0, 0, 0, 45, 480, 360);
			$image = $canvas;
		}

		$yt_w = imagesx($image);
		$yt_h = imagesy($image);

// add the button
		$yb = dirname(__FILE__) . '/' . $this->id . '.png';
		$ytb = imagecreatefrompng( $yb );

		imagealphablending($ytb, true);

		$ytb_w = imagesx($ytb);
		$ytb_h = imagesy($ytb);

// center the button
		$left = round( $yt_w / 2) - round( $ytb_w / 2);
		$top  = round( $yt_h / 2) - round( $ytb_h / 2);

// convert to png
		imagecopy( $image, $ytb , $left, $top, 0, 0, $ytb_w, $ytb_h);
		imagepng( $image, $ynf, 9);

		$data->thumbnail_url    = $ynu;
		$data->thumbnail_width  = $yt_w;
		$data->thumbnail_height = $yt_h;

		return $html;
	}
}
new MP_oembed_provider_YouTube();
