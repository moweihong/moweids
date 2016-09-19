<?php
/**
 * 图片处理类
 * **/
namespace  Common\library;
class thumb {
	// 图片类型
	var $type;
	// 实际宽度
	var $width;
	// 实际高度
	var $height;
	// 改变后的宽度
	var $resize_width;
	// 改变后的高度
	var $resize_height;
	// 是否裁图
	var $cut;
	// 源图象
	var $srcimg;
	// 目标图象地址
	var $dstimg;
	// 临时创建的图象
	var $im;
	function resizeimage($img, $wid, $hei, $c, $dstpath) {
		if (! $img) {
			return FALSE;
		}
		$this->srcimg = $img;
		$this->resize_width = $wid;
		$this->resize_height = $hei;
		$this->cut = $c;
		// 图片的类型
		
		$this->type = strtolower ( substr ( strrchr ( $this->srcimg, "." ), 1 ) );
		
		// 初始化图象
		$this->initi_img ();
		// 目标图象地址
		$this->dst_img ( $dstpath );
		// --
		$this->width = imagesx ( $this->im );
		$this->height = imagesy ( $this->im );
		// 生成图象
		$this->newimg ();
		ImageDestroy ( $this->im );
	}
	function newimg() {
		// 改变后的图象的比例
		$resize_ratio = ($this->resize_width) / ($this->resize_height);
		// 实际图象的比例
		$ratio = ($this->width) / ($this->height);
		if (($this->cut) == "1") 
		// 裁图
		{
			if ($ratio >= $resize_ratio) 
			// 高度优先
			{
				$newimg = imagecreatetruecolor ( $this->resize_width, $this->resize_height );
				imagecopyresampled ( $newimg, $this->im, 0, 0, 0, 0, $this->resize_width, $this->resize_height, (($this->height) * $resize_ratio), $this->height );
				ImageJpeg ( $newimg, $this->dstimg );
			}
			if ($ratio < $resize_ratio) 
			// 宽度优先
			{
				$newimg = imagecreatetruecolor ( $this->resize_width, $this->resize_height );
				imagecopyresampled ( $newimg, $this->im, 0, 0, 0, 0, $this->resize_width, $this->resize_height, $this->width, (($this->width) / $resize_ratio) );
				ImageJpeg ( $newimg, $this->dstimg );
			}
		} else // 不裁图
{
			if ($ratio >= $resize_ratio) {
				$newimg = imagecreatetruecolor ( $this->resize_width, ($this->resize_width) / $ratio );
				imagecopyresampled ( $newimg, $this->im, 0, 0, 0, 0, $this->resize_width, ($this->resize_width) / $ratio, $this->width, $this->height );
				ImageJpeg ( $newimg, $this->dstimg );
			}
			if ($ratio < $resize_ratio) {
				$newimg = imagecreatetruecolor ( ($this->resize_height) * $ratio, $this->resize_height );
				imagecopyresampled ( $newimg, $this->im, 0, 0, 0, 0, ($this->resize_height) * $ratio, $this->resize_height, $this->width, $this->height );
				ImageJpeg ( $newimg, $this->dstimg );
			}
		}
	}
	// 初始化图象
	function initi_img() {
		if ($this->type == "jpg") {
			$this->im = imagecreatefromjpeg ( $this->srcimg );
		}
		if ($this->type == "gif") {
			$this->im = imagecreatefromgif ( $this->srcimg );
		}
		if ($this->type == "png") {
			$this->im = imagecreatefrompng ( $this->srcimg );
		}
	}
	// 图象目标地址
	function dst_img($dstpath) {
		$full_length = strlen ( $this->srcimg );
		$type_length = strlen ( $this->type );
		$name_length = $full_length - $type_length;
		$name = substr ( $this->srcimg, 0, $name_length - 1 );
		$this->dstimg = $dstpath;
	}
	
	/**
	 * 图片添加水印
	 * &$dst_im 得到原始图片引用
	 * $src_im 水印图像
	 * $dst_info 得到原始图片信息
	 * $src_info 水印图像信息
	 * $pos_x 水印图片在x轴的位置
	 * $pos_y 水印图片在y轴的位置
	 */
	public function ImageMerge(&$dst_im, $src_im, $dst_info, $src_info, $pos_x, $pos_y) {
		// 水印透明度
		$alpha = 100;
		// 合并水印图片
		imagecopymerge ( $dst_im, $src_im, $pos_x, $pos_y, 0, 0, $src_info [0], $src_info [1], 100 );
	}
	
	/**
	 * 图片添加文字水印
	 * &$im 图片引用
	 * $text 水印文字
	 */
	public function ImageWithText(&$im, $text,$fontsize,$position) {
		$white = imagecolorallocate ( $im, 0xFF, 0xFF, 0xFF );
		imagecolortransparent ( $im, $white );
		$x = imagesx ( $im );
		$y = imagesy ( $im );
		//计算字符串的像素坐标
		$fontarea = ImageTTFBBox($fontsize,0,"C:\windows\Fonts\simsun.ttc",$text);
		$pixlen=$fontarea[2]-$fontarea[0];
		$black = imagecolorallocate ( $im, 255, 255, 255 );
		switch($position){
			case 1:
				$pos_x=$x / 2 - $pixlen / 2;
				$pos_y= $y / 2 + 30;
				break;
			case 2:
				$pos_x=$x / 2 - $pixlen / 2;
				$pos_y= $y / 2 +90;
				break;
			case 3:
				$pos_x=$x / 2 - $pixlen / 2;
				$pos_y= $y / 2 +692;
				break;
				case 4:
					$pos_x=$x / 2 - $pixlen / 2;
					$pos_y= $y / 2 +745;
					break;
			default:
				$pos_x=$x/2;
				$pos_y=$y/2;
				break;
		}
		imagettftext ( $im,$fontsize,0,$pos_x ,$pos_y, $black, "C:\windows\Fonts\simhei.ttf", $text ); // 字体设置部分linux和windows的路径可能不同选择自己字库中有的字体
	}
	/**
	 * 判断图片的格式
	 * $url 图片的路径
	 */
	public function ImageType($url) {
		$type = getimagesize ( $url );
		if (strpos ( $type ['mime'], 'png' )) {
			return imagecreatefrompng ( $url );
		}
		if (strpos ( $type ['mime'], 'jp' )) {
			return imagecreatefromjpeg ( $url );
		}
		if (strpos ( $type ['mime'], 'gif' )) {
			return imagecreatefromgif ( $url );
		}
		return false;
	}


	/***
	 * 图片等比压缩
	 * $im 图片资源
	 * $maxwidth 最大宽度
	 * $maxheight 最大高度
	 */

	function resizeImages($im,$maxwidth,$maxheight)
	{
		$pic_width = imagesx($im);
		$pic_height = imagesy($im);

		if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight))
		{
			if($maxwidth && $pic_width>$maxwidth)
			{
				$widthratio = $maxwidth/$pic_width;
				$resizewidth_tag = true;
			}

			if($maxheight && $pic_height>$maxheight)
			{
				$heightratio = $maxheight/$pic_height;
				$resizeheight_tag = true;
			}

			if($resizewidth_tag && $resizeheight_tag)
			{
				if($widthratio<$heightratio)
					$ratio = $widthratio;
				else
					$ratio = $heightratio;
			}

			if($resizewidth_tag && !$resizeheight_tag)
				$ratio = $widthratio;
			if($resizeheight_tag && !$resizewidth_tag)
				$ratio = $heightratio;

			$newwidth = $pic_width * $ratio;
			$newheight = $pic_height * $ratio;

			if(function_exists("imagecopyresampled"))
			{
				$newim = imagecreatetruecolor($newwidth,$newheight);//PHP系统函数
				imagecopyresampled($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);//PHP系统函数
			}
			else
			{
				$newim = imagecreate($newwidth,$newheight);
				imagecopyresized($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
			}
			return $newim;
		}
		else
		{
		    return $im;
		}
	}


	/**
	 * 图片圆角处理
	 * &$image 图片引用
	 * $img_info 图片信息
	 * $radius 弧度
	 */
	function ImageCorner(&$image, $img_info, $radius = 20) {
		$corner_radius = $radius; // 弧度
		$topleft = true; // 左上角是否圆角
		$bottomleft = true; // 左下角是否圆角
		$bottomright = true; // 右下角是否圆角
		$topright = true; // 右上角是否圆角
		$startsize = $corner_radius * 3 - 1;
		$arcsize = $startsize * 2 + 1;
		$size = $img_info;
		// Top-left corner
		$background = imagecreatetruecolor ( $size [0], $size [1] );
		imagecopymerge ( $background, $image, 0, 0, 0, 0, $size [0], $size [1], 100 );
		$startx = $size [0];
		$starty = $size [1];
		$im_temp = imagecreatetruecolor ( $startx, $starty );
		imagecopyresampled ( $im_temp, $background, 0, 0, 0, 0, $startx, $starty, $size [0], $size [1] );
		$bg = imagecolorallocate ( $im_temp, 8, 8, 8 );
		$fg = imagecolorallocate ( $im_temp, 0, 0, 0 );
		imageColorTransparent ( $image, $bg ); // 设置背景色为透明
		if ($topleft == true) {
			imagearc ( $im_temp, $startsize, $startsize, $arcsize, $arcsize, 180, 270, $bg );
			imagefilltoborder ( $im_temp, 0, 0, $bg, $bg );
		}
		// Bottom-left corner
		if ($bottomleft == true) {
			imagearc ( $im_temp, $startsize, $starty - $startsize, $arcsize, $arcsize, 90, 180, $bg );
			imagefilltoborder ( $im_temp, 0, $starty, $bg, $bg );
		}
		// Bottom-right corner
		if ($bottomright == true) {
			imagearc ( $im_temp, $startx - $startsize, $starty - $startsize, $arcsize, $arcsize, 0, 90, $bg );
			imagefilltoborder ( $im_temp, $startx, $starty, $bg, $bg );
		}
		// Top-right corner
		if ($topright == true) {
			imagearc ( $im_temp, $startx - $startsize, $startsize, $arcsize, $arcsize, 270, 360, $bg );
			imagefilltoborder ( $im_temp, $startx, 0, $bg, $bg );
		}
		$newimage = imagecreatetruecolor ( $size [0], $size [1] );
		imagecopyresampled ( $image, $im_temp, 0, 0, 0, 0, $size [0], $size [1], $startx, $starty );
	}
}
?>
