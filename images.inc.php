images.inc.php

<?php


Class Exif_Wrapper{
	
		
 //int exif_imagetype ( string $filename )
	static function imagetype($filename){
		return exif_imagetype($filename);
	}
 
 //array exif_read_data ( string $filename [, string $sections = NULL [, bool $arrays = false [, bool $thumbnail = false ]]] )
	function read_data( $filename, $sections = NULL , $arrays = false , $thumbnail = false ){
		return exif_read_data($filename,$sections, $arrays, $thumbnail);
	}
 
 //string exif_tagname ( int $index )
	static function tagname($index){
		return exif_tagname (  $index );
	}
 //string exif_thumbnail ( string $filename [, int &$width [, int &$height [, int &$imagetype ]]] )
 
	static function thumbnail( $filename, &$width, &$height, &$imagetype ){
		return exif_thumbnail($filename, $width, $height, $imagetype);
	}
	
	static function isImage( $fn ){
		$tmp=@self::imagetype($fn);
		return ($tmp===false)?false:true;
	} 
}





Abstract Class ImageResize_Parent {
	Const OUTPUT_SAME =0;
	const OUTPUT_FORCE_JPG=1;
	const OUTPUT_FORCE_PNG=2;
	const OUTPUT_FORCE_GIF=3;
	const OUTPUT_FORCE_XBM=4;
	const OUTPUT_FORCE_XPM=5;
	const OUTPUT_FORCE_BMP=6;
	
	const PNG_NO_ALPHABLENDING=0;
	const PNG_ALPHABLENDING=32;
	
	const ALLOW_OVERWRITE=64;
	const DISALLOW_OVERWRITE=0;
	
	const PORTRAIT_RATIO_INVERSE=128;
	const PORTRAIT_RATIO_IGNORE=0;
	
	
	const USE_GD=0;
	const USE_IMAGEMAGICK=256;
	const USE_FUTURELIB1=512;
	
	const ALLOW_OVERSIZE=1024;
	const DISALLOW_OVERSIZE=0;
	
	protected static $handlers;

	
	protected static function assetImageAction($mixed,$msg){
		if (false===$mixed ){
			throw new RuntimeException($msg);
		}
		return $mixed;
	}

	protected static function registerHandlers(){
		self::$handlers=array();
		self::registerHandlersGD();
	}

	protected static function registerHandlersGD(){
		self::$handlers['gd']=array();
		$gd=&self::$handlers['gd'];
		//first 3 are supported form the begin of time.
		$gd['jpg'][0]=function( $img){ 
			return self::assetImageAction(imagecreatefromjpeg($img),'Can not read jpeg file:'.$img);
		};
		$gd['jpg'][1]=function(&$out,$filename,ImageResize_Options &$option){ 
		
			return self::assetImageAction(imagejpeg($out,$filename,$option->jpg_quality),'Can not write jpg file:'.$filename);
		};
		$gd['jpeg']=&$gd['jpg'];
		
		
		$gd['png'][0]=function( $img ){ 
			return self::assetImageAction(imagecreatefrompng($img),'Can not read png file:'.$img);
		};
		$gd['png'][1]=function(&$out,$filename,ImageResize_Options &$option){ 
			return self::assetImageAction(imagepng($out,$filename,$option->png_compression),'Can not write png file:'.$filename);;
		};	
		
		
		$gd['gif'][0]=function( $img ){ 
			return self::assetImageAction(imagecreatefromgif($img),'Can not read gif file:'.$img);
			};
		$gd['gif'][1]=function(&$out,$filename,ImageResize_Options &$option){ 
			return self::assetImageAction(imagegif($out,$filename),'Can not write gif file:'.$filename);;
		};
		
		
		//those are quite newer
		if (  function_exists( 'imagexbm' ) ){
			$gd['xbm'][0]=function( $img ){ 
				return self::assetImageAction(imagecreatefromxbm($img),'Can not read xbm file:'.$img);
			};
			$gd['xbm'][1]=function(&$out,$filename,ImageResize_Options &$option){ 
				//$foreground_color = imagecolorallocate($out, 255, 255, 255);
				return self::assetImageAction(imagexbm($out,$filename,$option->backgroundcolor),'Can not write xbm file:'.$filename);
			};
		}//if xbm 
		if (  function_exists( 'imagexpm' ) ){
			$gd['xpm'][0]=function( $img ){ 
				return self::assetImageAction(imagecreatefromxpm($img),'Can not read xpm file:'.$img);
			};
			$gd['xpm'][1]=function(&$out,$filename,ImageResize_Options &$option){ 
				//$foreground_color = imagecolorallocate($out, 255, 255, 255);
				return self::assetImageAction(imagexpm($out,$filename,$option->backgroundcolor),'Can not write xpm file:'.$filename);
			};
		}//if xpm
		if (  function_exists( 'imagebmp' ) ){
			$gd['bmp'][0]=function( $img ){ 
				return self::assetImageAction(imagecreatefrombmp($img),'Can not read bmp file:'.$img);
			};
			$gd['bmp'][1]=function(&$out,$filename,ImageResize_Options &$option){ 
				//$foreground_color = imagecolorallocate($out, 255, 255, 255);
				return self::assetImageAction( imagebmp($out,$filename,true),'Can not write bmp file:'.$filename);
			};
		}//if bmp
	}	


	
}


 
Class ImageResize_Options extends ImageResize_Parent {
	
	private $alphablending;
	private $overwrite;
	private $portrait_ratio;
	private $graphiclib;	
	private $outputFormat;
	private $no_oversize;
	private $backgroundcolor=IMG_COLOR_TRANSPARENT;
	private $jpg_quality=95;
	private $png_compression=7;
	
	
	
	function __construct( $options ){
		$options=intval($options);
		if (!is_array(self::$handlers)){
			self::registerHandlers();
		}
		
		$tmp=31&$options;
		
		if ( $tmp === self::OUTPUT_FORCE_PNG ){
			$this->outputFormat='png';
		}
		elseif ( $tmp === self::OUTPUT_FORCE_JPG ){
			$this->outputFormat='jpg';
		}
		elseif ( $tmp === self::OUTPUT_FORCE_GIF ){
			$this->outputFormat='gif';
		}
		elseif ( $tmp === self::OUTPUT_FORCE_XBM and isset(self::$handlers['gd']['xbm']) ){
			$this->outputFormat='xbm';
		}
		elseif ( $tmp === self::OUTPUT_FORCE_XPM and isset(self::$handlers['gd']['xpm']) ){
			$this->outputFormat='xpm';
		}
		elseif ( $tmp === self::OUTPUT_FORCE_BMP and isset(self::$handlers['gd']['bmp']) ){
			$this->outputFormat='bmp';
		}
		else {
			$this->outputFormat='';
		}
		
		$options=$options>>5;
		$this->alphablending=1&$options;
		$options=$options>>1;
		$this->overwrite=1&$options;
		$options=$options>>1;
		$this->portrait_ratio=1&$options;
		$options=$options>>1;
		$this->graphiclib=$this->selectUserGraphicLibrary(3&$options);
		$options=$options>>2;
		$this->no_oversize=!(1&$options);		
	}
	
	public function __get($name){
		if (property_exists($this,$name)){
			return $this->$name;
		}
		else {
			throw new BadMethodCallException('Calling inexistant options');
		}
	}
	
	
	public function getSupportedFormat(){
		//echo $this->graphiclib, PHP_EOL;
		//print_r(array_keys(self::$handlers[$this->graphiclib]));
		return array_keys(self::$handlers[$this->graphiclib]);
	}
	
	
	public function isSameFormat(){
		return empty($this->outputFormat);
	}
	
	
	protected function selectUserGraphicLibrary($options){
		switch( $options ){
			case 0: return 'gd';
			case 1: return 'im';
			default:
				throw new BadMethodCallException('Trying to use unsupported library');
		}
	}
	
	
	
	
}


Class ImageResize extends ImageResize_Parent {

	
	private $widthTarget;//x
	private $heightTarget;//y
	private $widthOrigin;
	private $heightOrigin;
	private $input_name;
	private $input_img;
	private $options;
	private $iptc;

	function __construct(SplFileInfo $img, ImageResize_Options $options ){
		// so we didn't get crap.
		if ( is_null($options) ){
			throw new InvalidArgumentException('Options are mandatory, it\'s missing.');
		}
		if ( is_null($img) ){
			throw new InvalidArgumentException('Img fileinfo is mandatory, it\'s missing.');
		}

		//maybe we don't have to check for this as it's done during the options setting.
//		if (!is_array(self::$handlers)){
//			self::registerHandlers();
//		}
	
		//yeah of course.
		if (!$img->isReadable()){
			throw new RuntimeException('can not read the provided file:'.$img->getPathname()); // et ça c'est balot
		}
		

		$this->input_name=$img;
		$this->options=&$options;
		
		//situation is good
		echo 'working on ',$img->getFilename(),PHP_EOL,
			'  script memory is      ',memory_get_usage(),PHP_EOL,
			'  source image size is  ', $img->getSize(),PHP_EOL;

		$dimensions = getimagesize($img->getPathname(),$this->iptc);

		$this->widthOrigin=$dimensions[0];
		$this->heightOrigin=$dimensions[1];
		echo '  source dimentions are ', $this->widthOrigin,'x',$this->heightOrigin,PHP_EOL;
		
		$this->input_img=$this->createGdRessource();
	}
	

	function getOutputFormat(){
		$tmp=$this->options->outputFormat;
		if ( empty($tmp) ){
			return strtolower($this->input_name->getExtension());
		}
		else {
			return $tmp;
		}
	}
	

	protected function calcResize( $widthRequested, $heightRequested ){
		
		if(  $this->options->portrait_ratio and $this-> heightOrigin> $this->widthOrigin ){
			$ratio= $this->heightOrigin / $this->widthOrigin;
			$swap=$widthRequested;
			$widthRequested=$heightRequested;
			$heightRequested=$swap;
		}
		$ratio= $this->widthOrigin / $this->heightOrigin;
		
		// Calcul des dimensions si 0 passé en paramètre
		if($widthRequested === 0 and $heightRequested === 0){
			$this->widthTarget = $this->widthOrigin;
			$this->heightTarget = $this->heightOrigin;
		}elseif($heightRequested === 0){
			$this->heightTarget = round($widthRequested / $ratio);
			$this->widthTarget = $widthRequested;
		}elseif ($widthRequested == 0){
			$this->widthTarget = round($heightRequested * $ratio);
			$this->heightTarget = $heightRequested;			
		}
		else {
			$control=$widthRequested/$heightRequested;
			if (  abs( $control - $ratio )>0.000001 ){
				echo '  Dimention will be altered.', PHP_EOL;
			}
			$this->widthTarget = $widthRequested;
			$this->heightTarget = $heightRequested;
		}
		
		if ( $this->options->no_oversize ){
			if ( $this->widthOrigin < $this->widthTarget or $this->heightOrigin < $this->heightTarget  ){
				$this->widthTarget=$this->widthOrigin;
				$this->heightTarget=$this->heightOrigin;
			}
		}
		
		
	}
	
	private function createGdRessource(  ){
		//$type=	mime_content_type( $img );
		$unsafe=strtolower($this->input_name->getExtension());

		if (  isset( self::$handlers['gd'][$unsafe]) ){
			$func=self::$handlers['gd'][$unsafe][0];
			$mixed=$func($this->input_name->getPathname());
		}
		else{
			throw new RangeException('Image format/extension not recognized');
		}
		$this->outputFormat=$unsafe;
		return $mixed;
	}

	
	private function writeGDRessource ( &$data, $filename , $quality ){
		$ext=$this->getOutputFormat();
		if ( isset(self::$handlers['gd'][$ext])){
			$func = self::$handlers['gd'][$ext][1];
			self::assetImageAction($func($data,$filename,$this->options),'Failed to write the image');
		}
		else {
			throw new RangeException('Not supported writing format ('.$ext.') with GD');
		}
	}//return void.

	
	private function manageOutputName(SplFileInfo &$out){
		//isWritable
		//echo "==>",__LINE__,':',$out->getPathname(),PHP_EOL;
		//var_dump($out);
		$tmp=$out->getPathname();
		
		if ( $out->isFile() ){
			if ( $this->options->overwrite ){
				unlink($tmp);
			}
			else {
				throw new UnexpectedValueException('Output file already exists. it won\'t be overwriten ');
			}
			return $tmp;
		}
		elseif ( $out->isDir() ){
			$out=new SplFileInfo( $tmp.DIRECTORY_SEPARATOR.$this->input_name->getBasename($this->input_name->getExtension()).'resized.'.$this->getOutputFormat()  );
			return $this->manageOutputName( $out );
		}
		elseif ( $out->isLink()){
			//if it's a link it sounds fishy
			throw new UnexpectedValueException('I dont overwrite links');
		}
		else {
			//if it's no file nor directory, we should try.
			return $tmp;
		}
	}
	
	
	protected function fixTimestamp($filename){
		touch( $filename, $this->input_name->getMTime());
	}
	

	protected function addIPTC( $filename ){
		$binary='';
		foreach( $this->iptc as $bin ){
			$binary.=$bin;
		}
		$content=iptcembed($binary, $filename );
		$fp = fopen($filename, "wb");
		fwrite($fp, $content);
		fclose($fp);
	}


	function resizeGD( SplFileInfo $out, $widthRequested=0, $heightRequested=0, $ifLossQuality=95 ){
		$this->calcResize( $widthRequested,$heightRequested );
		$target=$this->manageOutputName($out);

		if ( $this->options->isSameFormat() and $this->widthOrigin===$this->widthTarget and $this->heightOrigin===$this->heightTarget ){
			copy( $this->input_name->getPathname(), $target );
		}
		else {
			$dst = imagecreatetruecolor($this->widthTarget, $this->heightTarget);
			if ( imagecopyresampled( $dst,$this->input_img,0,0,0,0,$this->widthTarget, $this->heightTarget, $this->widthOrigin, $this->heightOrigin )){
				$this->writeGDRessource( $dst, $target, $ifLossQuality );
				$this->fixTimestamp($target);
			}
			else {
				echo '  imagecopyresampled failed', PHP_EOL;
			}
			echo '  resised, memory is    ',memory_get_usage(true), PHP_EOL;
			imagedestroy($dst);
		}
	}

	static function size2text($x){
		return ($x===0)?'AUTO':$x;
	}
	

	function __destruct(){
		imagedestroy($this->input_img);
		echo "Done. ", PHP_EOL, PHP_EOL;
	}
	
}//class




//var_dump($this);
/*
$tmp=($this->widthTarget / $this->heightTarget) * $this->heightOrigin;
if($this->widthOrigin > $tmp ){
	$dimY = $this->heightTarget;
	$dimX = round($this->heightTarget * $this->widthOrigin / $this->heightOrigin);
	$decalX = ($dimX - $this->widthTarget) / 2;
	$decalY = 0;
}
if($this->widthOrigin < $tmp ){
	$dimX = $this->widthTarget;
	$dimY = round($this->widthTarget * $this->heightOrigin / $this->widthOrigin);
	$decalY = ($dimY - $$this->heightTarget) / 2;
	$decalX = 0;
}
if($this->widthOrigin == $tmp ){
	$dimX = $this->widthTarget;
	$dimY = $$this->heightTarget;
	$decalX = 0;
	$decalY = 0;
}

class IPTC
{
	const OBJECT_NAME="005";
	const EDIT_STATUS="007";
	const PRIORITY="010";
	const CATEGORY="015";
	const SUPPLEMENTAL_CATEGORY="020";
	const FIXTURE_IDENTIFIER="022";
	const KEYWORDS="025";
	const RELEASE_DATE="030";
	const RELEASE_TIME="035";
	const SPECIAL_INSTRUCTIONS="040";
	const REFERENCE_SERVICE="045";
	const REFERENCE_DATE="047";
	const REFERENCE_NUMBER="050";
	const CREATED_DATE="055";
	const CREATED_TIME="060";
	const ORIGINATING_PROGRAM="065";
	const PROGRAM_VERSION="070";
	const OBJECT_CYCLE="075";
	const BYLINE="080";
	const BYLINE_TITLE="085";
	const CITY="090";
	const PROVINCE_STATE="095";
	const COUNTRY_CODE="100";
	const COUNTRY="101";
	const ORIGINAL_TRANSMISSION_REFERENCE="103";
	const HEADLINE="105";
	const CREDIT="110";
	const SOURCE="115";
	const COPYRIGHT_STRING="116";
	const CAPTION="120";
	const LOCAL_CAPTION="121";
	
	
    private $meta = [];
    private $file = null;

    function __construct($filename)
    {
        $info = null;

        $size = getimagesize($filename, $info);

        if(isset($info["APP13"])) $this->meta = iptcparse($info["APP13"]);

        $this->file = $filename;
		
		//var_dump($this->meta);
    }

    function getValue($tag)
    {
        return isset($this->meta["2#$tag"]) ? $this->meta["2#$tag"][0] : "";
    }

    function setValue($tag, $data)
    {
        $this->meta["2#$tag"] = [$data];

        $this->write();
    }

    private function write()
    {
        $mode = 0;

        $content = iptcembed($this->binary(), $this->file, $mode);   

        $filename = $this->file;

        if(file_exists($this->file)) unlink($this->file);

        $fp = fopen($this->file, "w");
        fwrite($fp, $content);
        fclose($fp);
    }         

    private function binary()
    {
        $data = "";

        foreach(array_keys($this->meta) as $key)
        {
            $tag = str_replace("2#", "", $key);
            $data .= $this->iptc_maketag(2, $tag, $this->meta[$key][0]);
        }       

        return $data;
    }

    function iptc_maketag($rec, $data, $value)
    {
        $length = strlen($value);
        $retval = chr(0x1C) . chr($rec) . chr($data);

        if($length < 0x8000)
        {
            $retval .= chr($length >> 8) .  chr($length & 0xFF);
        }
        else
        {
            $retval .= chr(0x80) . 
                       chr(0x04) . 
                       chr(($length >> 24) & 0xFF) . 
                       chr(($length >> 16) & 0xFF) . 
                       chr(($length >> 8) & 0xFF) . 
                       chr($length & 0xFF);
        }

        return $retval . $value;            
    }   

    function dump()
    {
        echo "<pre>";
        print_r($this->meta);
        echo "</pre>";
    }

    #requires GD library installed
    function removeAllTags()
    {
        $this->meta = [];
        $img = imagecreatefromstring(implode(file($this->file)));
        if(file_exists($this->file)) unlink($this->file);
        imagejpeg($img, $this->file, 100);
    }
}


*/
