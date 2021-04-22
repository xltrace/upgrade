<?php
namespace XLtrace\Hades;

if(!function_exists('\XLtrace\Hades\backup')){function backup($file=NULL){
	# Create backup of local installation
	return FALSE;
}}
if(!function_exists('\XLtrace\Hades\restore')){function restore($file=NULL){
	# Restore backup
	return FALSE;
}}
if(!function_exists('\XLtrace\Hades\patch')){function patch(){
	# Single time run script
	return FALSE;
}}
function touch($file=NULL, $mode=NULL, $remote=NULL, $directory=NULL){
	switch($file){
		case 'composer.phar':
			if(!file_exists($file)){
				/*debug*/ \XLtrace\Hades\pcl('install '.$file."\n");
			}
			else{
				/*debug*/ \XLtrace\Hades\pcl('upgrade '.$file."\n");
			}
			return TRUE; break;
		case NULL: case '.':
			/*debug*/ \XLtrace\Hades\pcl('ingored '.$file."\n");
			return FALSE; break;
	}
	if(substr($file, -1) == '/'){
		switch($mode){
			case TRUE:
				if(is_dir($directory.$file)){
					/*debug*/ \XLtrace\Hades\pcl('empty '.$directory.$file."\n");
					#empty directory
				} //break;
			case NULL:
				if(!(file_exists($directory.$file) && is_dir($directory.$file))){
					/*debug*/ \XLtrace\Hades\pcl('mkdir '.$directory.$file."\n");
					mkdir($file);
				}
				break;
			default:
				/*debug*/ \XLtrace\Hades\pcl('mode unsupported for '.$directory.$file."\n + ".print_r($mode, TRUE)."\n");
		}
	} else {
		switch($mode){
			case NULL:
				if(file_exists($directory.$file)){
					/*debug*/ \XLtrace\Hades\pcl('ignored existing file '.$directory.$file."\n");
					return TRUE;
				}
			case TRUE:
				if($remote !== NULL){
					$raw = @file_get_contents($remote.$file);
					/*debug*/ \XLtrace\Hades\pcl('put '.$remote.$file.' >('.strlen($raw).')> '.$directory.$file."\n");
					if(strlen($raw)>0){ file_put_contents($directory.$file, $raw); return TRUE; }
				} else { return FALSE; }
				break;
			default:
				/*debug*/ \XLtrace\Hades\pcl('mode unsupported for '.$directory.$file."\n + ".print_r($mode, TRUE)."\n");
		}
	}
	return FALSE;
}
function upgrade_json($file=NULL, $wdefault=FALSE){
	/*fix*/if($file === NULL){ $file = 'upgrade.json'; $wdefault = ($wdefault===FALSE ? TRUE : $wdefault); }
	$json = array();
	if($wdefault !== FALSE){
		$json = (is_array($wdefault) ? $wdefault : array('.'=>'https://github.com/xltrace/upgrade/raw/main/'));
	}
	if(file_exists($file)){
		$raw = file_get_contents($file);
		$set = json_decode($raw, TRUE);
		//*debug*/ \XLtrace\Hades\pcl(print_r(array($file=>$raw, 'json'=>$set), TRUE));
		$json = array_merge($json, (is_array($set) ? $set : array()));
	}
	return $json;
}
function upgrade($file=NULL){
	$db = \XLtrace\Hades\upgrade_json($file);
	$base = ($file !== NULL ? dirname($file).'/' : NULL);

	/*debug*/ \XLtrace\Hades\pcl('UPGRADE ('.count($db).') '.$file."\n");
	//*debug*/ print_r($db); return NULL;
	foreach($db as $pointer=>$instruction){
		if(preg_match('#upgrade\.json$#', $pointer)){
			\XLtrace\Hades\upgrade($pointer);
		}
		else{ \XLtrace\Hades\touch($pointer, $instruction, (isset($db['.']) ? $db['.'] : FALSE), $base ); }
	}
	return TRUE;
}
if(!function_exists('\XLtrace\Hades\pcl')){function pcl($str=NULL, $force=FALSE){ /* print command line */
	if((isset($_SERVER['argv'][0]) && $_SERVER['argv'][0] == $_SERVER['PHP_SELF']) || $force === TRUE){ print $str; }
}}

if(in_array($_SERVER['PHP_SELF'], array('upgrade.php','/upgrade.php')) || $_SERVER['SCRIPT_FILENAME'] == __FILE__){
	$file = (isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : NULL);
	\XLtrace\Hades\backup();
	\XLtrace\Hades\upgrade($file);
	if(isset($_GET['all']) && function_exists('\XLtrace\Hades\run_slaves')){ \XLtrace\Hades\run_slaves('upgrade.php'); }
	\XLtrace\Hades\patch();
}
?>
