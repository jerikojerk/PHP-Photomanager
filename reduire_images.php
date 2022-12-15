<?php
include_once 'images.inc.php';

$width=1280;//largeur maximale de l'image => si l'image est en paysage et non portrait, ça va échanger la largeur max en hauteur max.
$height=0;//hauteur max de l'image. zero pour taille automatique afin de conserver le ratio

$pwd=getcwd();
echo "Hello, working on pictures in path :",$pwd, PHP_EOL;
$dir = new DirectoryIterator( $pwd  );

$toDirectory = $pwd.DIRECTORY_SEPARATOR.'resized-'.ImageResize::size2text($width).'x'.ImageResize::size2text($height).'-'.date('Y-m-d_His');


$out= new SplFileInfo($toDirectory);
echo "output is in ", $toDirectory,PHP_EOL;
if ( !$out->isDir()){
	mkdir( $toDirectory );
}


$options = new ImageResize_Options( ImageResize::OUTPUT_SAME|ImageResize::PORTRAIT_RATIO_INVERSE|ImageResize::DISALLOW_OVERWRITE|ImageResize::DISALLOW_OVERSIZE );
$allowed=$options->getSupportedFormat();


foreach (  $dir as $myfile ){
	if($myfile->isDot() or $myfile->isDir()) continue;
	echo 'found ',$myfile->getBasename(),PHP_EOL;
	
	if (in_array( strtolower($myfile->getExtension()),$allowed ) ){
		$i= new ImageResize($myfile,$options );
		$i->resizeGD($out,$width,0);
		unset($i);
	}
}


