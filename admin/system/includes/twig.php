<?php require_once __DIR__ . "/../../../vendor/autoload.php";

// Register Twig
$TwigLoader = new Twig_Loader_Filesystem();

// Check twig cache directories, if not exists, create
if(debug_mode){
    $twigDirs = [
        __DIR__ . '/../../../cache/',
        __DIR__ . '/../../../cache/twig'
    ];

    foreach($twigDirs as $dir){
        if(!is_dir($dir)){
            mkdir($dir);
        }
    }
}

$Twig = new Twig_Environment($TwigLoader, array(
    'cache' => __DIR__ . '/../../../cache/twig',
));


if(debug_mode){
    $Twig->clearCacheFiles();
}


if(in_admin){
    $TwigLoader->addPath(__DIR__ . "/../../view/");
}
else{

}