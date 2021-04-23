<?php
namespace XLtrace\Hades;

if(file_exists('settings.php')){ require_once('settings.php'); }

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
if(!function_exists('\XLtrace\Hades\slaves_file')){function slaves_file(){ return __DIR__.'/slaves.json'; }}
if(!function_exists('\XLtrace\Hades\run_slaves')){function run_slaves($action=NULL, $list=array()){ //herhaps the naming is politically incorrect; should be changed!
  if(!is_array($list) || count($list) == 0){
    if(!file_exists(\XLtrace\Hades\slaves_file())){ return FALSE; }
    $list = \XLtrace\Hades\file_get_json(\XLtrace\Hades\slaves_file(), TRUE, array());
  }
  $bool = TRUE; $json = array();
  foreach($list as $i=>$url){
    $pu = parse_url($url);
    if($pu !== FALSE && is_array($pu)){
      switch(strtolower($action)){
        case 'upgrade': case 'upgrade.php':
          $pu['path'] = $pu['path'].(substr($pu['path'], -1) == '/' ? NULL : '/').strtolower($action);
          $buffer = file_get_contents(\XLtrace\Hades\build_url($pu));
          break;
        default:
          $bool = FALSE;
      }
    }
  }
  return (count($json) == 0 ? $bool : $json);
}}
if(!function_exists('\XLtrace\Hades\build_url')){function build_url($ar=array()){
    // $ar is assumed to be a valid result of parse_url()
    $url = NULL;
    $url .= (isset($ar['scheme']) ? $ar['scheme'].'://' : NULL);
    if(isset($ar['user'])){ $url .= $ar['user'].(isset($ar['pass']) ? ':'.$ar['pass'] : NULL).'@'; }
    $url .= $ar['host'].(isset($ar['port']) ? ':'.$ar['port'] : NULL);
    $url .= ((isset($ar['query']) || isset($ar['fragment']) || isset($ar['path'])) ? (isset($ar['path']) ? (substr($ar['path'], 0, 1) != '/' ? '/' : NULL) : '/') : NULL);
    $url .= (isset($ar['path']) ? $ar['path'] : NULL);
    $url .= (isset($ar['query']) ? '?'.(is_array($ar['query']) ? http_build_query($ar['query']) : $ar['query']) : NULL);
    $url .= (isset($ar['fragment']) ? '#'.$ar['fragment'] : NULL);
    return $url;
}}
if(!function_exists('\XLtrace\Hades\current_URI')){function current_URI($el=NULL, $pl=NULL, $set=array()){
  $uri = array(
    'scheme'=>((
      (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME']=='https') ||
      (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] == '443') ||
      (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ||
      (isset($_SERVER['SCRIPT_URI']) && substr($_SERVER['SCRIPT_URI'],0,5)=='https')
    ) ? 'https' : 'http'),
    'host'=>(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'));
  if($el !== NULL){
    if(is_array($el)){ $uri['query'] = $el; if($pl !== NULL){ $uri['path'] = $pl; } }
    else{ $uri['path'] = $el; if(is_array($pl)){ $uri['query'] = $pl; } }
  }
  /*fix*/ if(is_array($set)){ $uri = array_merge($uri, $set); }
  /*fix*/ if(isset($uri['query']['for']) && (!isset($uri['path']) || strlen($uri['path']) < 1)){ $uri['path'] = $uri['query']['for']; unset($uri['query']['for']); }
  /*fix*/ if(FALSE){foreach(array('module','mapper') as $k){if(isset($_GET[$k]) && !isset($uri['query'][$k])){ $uri['query'][$k] = $_GET[$k]; }}}
  return \XLtrace\Hades\build_url($uri);
}}
if(function_exists('\XLtrace\Hades\file_get_json')){function file_get_json($file, $as_array=TRUE, $def=FALSE){
  /*fix*/ if(preg_match("#[\n]#", $file)){ $file = explode("\n", $file); }
  if(is_array($file)){
    $set = FALSE;
    foreach($file as $i=>$f){
      $buffer = \XLtrace\Hades\file_get_json($f, $as_array, $def);
      if($buffer !== $def && ($as_array === TRUE ? is_array($buffer) : TRUE)){
        $set = array_merge(($as_array !== TRUE ? array($buffer) : $buffer), (!is_array($set) ? array() : $set));
      }
    }
    return $set;
  }
  $puf = parse_url($file);
  if((is_array($puf) && !isset($puf['schema']) && !isset($puf['host']) ? file_exists($file) : $puf !== FALSE )){
    $raw = file_get_contents($file);
    $json = json_decode($raw, (is_bool($as_array) ? $as_array : TRUE));
    if(!is_bool($as_array)){
      if(isset($json[$as_array])){ return $json[$as_array]; }
      else{ return $def; }
    }
    else{
      return $json;
    }
  }
  return $def;
}}
if(!function_exists('\XLtrace\Hades\file_put_json')){function file_put_json($file, $set=array()){
  if(class_exists('JSONplus')){
    $jsonstr = \JSONplus::encode($set);
  }
  else{
    $jsonstr = json_encode($set);
  }
  return file_put_contents($file, $jsonstr);
}}
if(!function_exists('\XLtrace\Hades\composer')){function composer($action=NULL, $output=NULL){
	if(!file_exists(__DIR__.'/composer.phar') || $action == 'composer-setup'){
        $bool = FALSE;
		copy('https://getcomposer.org/installer', 'composer-setup.php');
        if (hash_file('sha384', 'composer-setup.php') === file_get_contents('https://composer.github.io/installer.sig')) { \XLtrace\Hades\pcl('Installer verified'."\n"); require('composer-setup.php'); $bool = TRUE; } else { \XLtrace\Hades\pcl('Installer corrupt'."\n"); }
        unlink('composer-setup.php');
		return $bool;
	}
	if(!class_exists('\Composer\Console\Application')){
		require_once('phar://'.__DIR__.'/composer.phar/vendor/autoload.php');
	}
	//if(!class_exists('\Composer\Console\Application')){ return FALSE; }
	$cli_args = is_string($action) && !empty($action) ? new \Symfony\Component\Console\Input\StringInput($action) : null;
	//if (preg_match('/self-?update/', $cli_args)) { $_SERVER['argv'][0] = __DIR__.'/composer.phar'; }
	$c = new \Composer\Console\Application();
	$c->setAutoExit(FALSE);
	$exitcode = $c->run($cli_args, $output);
	return $output;
}}
if(!function_exists('\XLtrace\Hades\touch')){function touch($file=NULL, $mode=NULL, $remote=NULL, $directory=NULL){
	switch($file){
		case 'composer.phar':
			if(!file_exists($file)){
				/*debug*/ \XLtrace\Hades\pcl('install '.$file."\n");
				\XLtrace\Hades\composer('composer-setup');
			}
			else{
				/*debug*/ \XLtrace\Hades\pcl('self update '.$file."\n");
				\XLtrace\Hades\composer('self-update');
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
}}
if(!function_exists('\XLtrace\Hades\upgrade_json')){function upgrade_json($file=NULL, $wdefault=FALSE){
	/*fix*/if($file === NULL){ $file = 'upgrade.json'; $wdefault = ($wdefault===FALSE ? TRUE : $wdefault); }
	$json = array();
	if($wdefault !== FALSE){
		$json = (is_array($wdefault) ? $wdefault : array('.'=>'https://github.com/xltrace/upgrade/raw/main/'));
	}
	if(is_array($file)){ $json = array_merge($json, $file); }
	elseif(file_exists($file)){
		$set = \XLtrace\Hades\file_get_json($file, TRUE, array());
		//*debug*/ \XLtrace\Hades\pcl(print_r(array('file'=>$file, 'json'=>$set), TRUE));
		$json = array_merge($json, $set);
	}
	return $json;
}}
if(!function_exists('\XLtrace\Hades\upgrade')){function upgrade($file=NULL){
	$db = \XLtrace\Hades\upgrade_json($file);
	$base = ($file !== NULL && file_exists($file) ? dirname($file).'/' : NULL);

	/*debug*/ \XLtrace\Hades\pcl('UPGRADE ('.count($db).') '.print_r($file, TRUE)."\n");
	//*debug*/ print_r($db); return NULL;
	foreach($db as $pointer=>$instruction){
		if(preg_match('#upgrade\.json$#', $pointer)){
			\XLtrace\Hades\upgrade($pointer);
		}
		else{ \XLtrace\Hades\touch($pointer, $instruction, (isset($db['.']) ? $db['.'] : FALSE), $base ); }
	}
	if(file_exists('composer.phar') && file_exists('composer.json')){ \XLtrace\Hades\composer('install'); }
	return TRUE;
}}
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
