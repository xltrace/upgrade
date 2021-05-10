<?php
namespace XLtrace\Hades;

if(file_exists('settings.php')){ require_once('settings.php'); }

if(!function_exists('\XLtrace\Hades\backup')){function backup($file=NULL, $mode=TRUE){
	$mode = \XLtrace\Hades\backup_conf($file, $mode); $file = $mode['file'];
	/*debug*/ print_r($mode);
	# Create backup of local installation
	$zip = new \ZipArchive();
	if(isset($mode['file']) && !is_bool($file) && preg_match('#\.zip$#', $file)){ $tempfile = (isset($mode['backup-dir']) ? $mode['backup-dir'] : __DIR__).'/'.$mode['file']; }
	else{ $resource = tmpfile(); $tempfile = stream_get_meta_data($resource)['uri']; }
	/*debug*/ print 'zip archive file: '.$tempfile."\n";
	if($zip->open($tempfile, \ZipArchive::CREATE)!==TRUE){ return FALSE; }
	foreach($mode['select'] as $i=>$f){
		$zip->addFile(__DIR__.'/'.$f, $f);
	}
	/*debug*/ print_r($zip); var_dump($zip);
	$res = $zip->close();

	# handle $file existence
	if(!(strlen($file) > 4)){ $file = FALSE; $raw = file_get_contents($tempfile); unlink($tempfile); }
	return ($file === FALSE ? $raw : $res);
}}
if(!function_exists('\XLtrace\Hades\ignorable_directories')){function ignorable_directories($set=FALSE){
	if(!is_array($set)){switch($set){
		case FALSE: $set = array('.git','vendor'); break;
		default: $set = array();
	}}
	return $set;
}}
if(!function_exists('\XLtrace\Hades\backup_conf')){function backup_conf($file=NULL, $mode=TRUE){
	# rearrange parameters
	if(is_bool($file) || is_array($file) || $file === NULL){ $mode = $file; $file = NULL; }
	if(!is_array($mode)){
		switch($mode){
			case TRUE: $mode = array("all"=>TRUE); break;
			case NULL: $mode = array("upgradable"=>TRUE); break;
			case FALSE: $mode = array("upgradable"=>FALSE); break;
			default: $mode = array();
		}
	}
	if(isset($mode['file'])){ $file = $mode['file']; } $mode['file'] =& $file;
	if(!isset($mode['select'])){ $mode['select'] = array(); }
	if(is_string($mode['select']) || isset($mode['by'])){
		if(preg_match('#upgrade\.json$#', $mode['select']) && file_exists($mode['select'])){
			$mode['by'] = $mode['select'];
		}
		$list = \XLtrace\Hades\file_get_json($mode['by'], TRUE, array());
		$mode['select'] = array();
		foreach($list as $k=>$v){ if(!in_array($k, array('.','..')) /* && file_exists($k) */){ $mode['select'][] = $k;} }
	}
	$d = (isset($mode['ignore']) ? $mode['ignore'] : FALSE);
	# run shortcuts
	if(isset($mode['all']) && $mode['all'] == TRUE){
		$mode['select'] = \XLtrace\Hades\scanAllDir(__DIR__, \XLtrace\Hades\ignorable_directories($d));
	}
	elseif(isset($mode['upgradable']) && is_bool($mode['upgradable'])){
		$list = \XLtrace\Hades\scanAllDir(__DIR__, \XLtrace\Hades\ignorable_directories($d));
		$u = array();
		foreach($list as $k){ if(preg_match('#upgrade\.json$#', $k)){ $u[] = $k; } }
		$v = array_keys( \XLtrace\Hades\file_get_json($u, TRUE, array()) );
		/*debug*/ if(isset($mode['debug']) && $mode['debug'] === TRUE){ $mode['upgrade.json'] = $u; $mode['upgrade-files'] = $v; }
		foreach($list as $k){ if( ($mode['upgradable'] ? in_array($k, $v) : !in_array($k, $v)) ){ $mode['select'][] = $k; } }
	}
	# ensure filename of archive is valid
	if($file !== NULL){
		/*security fix*/ $file = basename($file);
		/*extention fix*/ if(!preg_match('#\.(zip)$#i', $file)){ $file = $file.'.zip'; }
	} else { $file = FALSE; }

	return $mode;
}}
if(!function_exists('\XLtrace\Hades\restore')){function restore($file=NULL){
	# Restore backup
	return FALSE;
}}
if(!function_exists('\XLtrace\Hades\patch')){function patch(){
	# Single time run script
	return FALSE;
}}
if(!function_exists('\XLtrace\Hades\scanAllDir')){function scanAllDir($dir, $exclude=array()){
  $result = array();
  foreach(scandir($dir) as $filename){
		if(in_array($filename, array('.','..'))) continue; //if($filename[0] === '.') continue;
		if(in_array($filename, $exclude)) continue;
    $filepath = $dir.'/'.$filename;
    if(is_dir($filepath)){
      foreach(scanAllDir($filepath) as $childfilename){
        $result[] = $filename.'/'.$childfilename;
      }
    }
    else{
      $result[] = $filename;
    }
  }
  return $result;
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
if(!function_exists('\XLtrace\Hades\file_get_json')){function file_get_json($file, $as_array=TRUE, $def=FALSE){
  /*fix*/ if(is_string($file) && preg_match("#[\n]#", $file)){ $file = explode("\n", $file); }
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
	$cli_args = (is_string($action) && !empty($action) ? new \Symfony\Component\Console\Input\StringInput($action) : null);
	//if (preg_match('/self-?update/', $cli_args)) { $_SERVER['argv'][0] = __DIR__.'/composer.phar'; }
	$c = new \Composer\Console\Application();
	$c->setAutoExit(FALSE);
	$exitcode = $c->run($cli_args, $output);
	return $output;
}}
if(!function_exists('\XLtrace\Hades\touch')){function touch($file=NULL, $mode=NULL, $remote=NULL, $directory=NULL){
	if(is_bool($mode) || $mode === NULL){
		switch($mode){
			case TRUE: $mode = array("create"=>TRUE,"clear"=>TRUE,"update"=>TRUE); break;
			case NULL: $mode = array("create"=>TRUE,"clear"=>FALSE,"update"=>FALSE); break;
			case FALSE: $mode = array("delete"=>TRUE); break;
		}
	}
	switch($file){
		case 'composer.phar':
			if(!file_exists($file) && (!isset($mode['create']) && !isset($mode['delete']) || $mode['create'] === TRUE)){
				/*debug*/ \XLtrace\Hades\pcl('install '.$file."\n");
				\XLtrace\Hades\composer('composer-setup');
			}
			/*security fix*/ if(file_exists('composer-setup.php')){ unlink('composer-setup.php'); }
			if(file_exists($file) && (isset($mode['upgrade']) && $mode['upgrade'] === TRUE)){
				/*debug*/ \XLtrace\Hades\pcl('self update '.$file."\n");
				\XLtrace\Hades\composer('self-update');
			}
			if(file_exists($file) && (isset($mode['update']) && $mode['update'] === TRUE)){
				/*debug*/ \XLtrace\Hades\pcl('update repository with'.$file."\n");
				\XLtrace\Hades\composer((file_exists('composer.lock') ? 'update' : 'install'));
			}
			return TRUE; break;
		case NULL: case '.':
			/*debug*/ \XLtrace\Hades\pcl('ingored '.$file."\n");
			return FALSE; break;
	}
	if(substr($file, -1) == '/'){ //# $file is a directory
		if(is_dir($directory.$file) && isset($mode['clear']) && $mode['clear'] === TRUE){
			/*debug*/ \XLtrace\Hades\pcl('empty '.$directory.$file."\n");
			#empty directory
		}
		if(!(file_exists($directory.$file) && is_dir($directory.$file))
			&& ((!isset($mode['create']) && !isset($mode['delete'])) || (isset($mode['create']) && $mode['create'] === TRUE)) ){
			/*debug*/ \XLtrace\Hades\pcl('mkdir '.$directory.$file."\n");
			mkdir($file);
		}
		if((file_exists($directory.$file) && is_dir($directory.$file)) && isset($mode['delete']) && $mode['delete'] === TRUE){
			/*debug*/ \XLtrace\Hades\pcl('mkdir '.$directory.$file."\n");
			rmdir($file);
		}
		if(isset($mode['chmod']) && is_dir($directory.$file)){ \chmod($directory.$file, (is_string($mode['chmod']) && preg_match('#^[0-9]+$#', $mode['chmod']) ? (int) base_convert($mode['chmod'], 8, 10) : $mode['chmod'])); }
		if(isset($mode['mtime']) && is_dir($directory.$file)){ \touch($directory.$file, $mode['mtime']); }
	} else { //# $file is a file
		$raw = FALSE;
		if($remote !== NULL){
			$raw = @file_get_contents($remote.$file);
			/*debug*/ \XLtrace\Hades\pcl('able to put '.$remote.$file.' >('.strlen($raw).')> '.$directory.$file."\n");
		}
		if(!is_bool($raw) && is_string($raw)
			&& ((!isset($mode['create']) && !isset($mode['delete'])) || (isset($mode['create']) && $mode['create'] === TRUE))
			&& (!isset($mode['clear']) || $mode['clear'] == TRUE) ){
			file_put_contents($directory.$file, $raw);
			/*debug*/ \XLtrace\Hades\pcl('saved '.$remote.$file.' to '.$directory.$file."\n");
		}
		if(isset($mode['delete']) && $mode['delete'] === TRUE && file_exists($directory.$file)){
			unlink($directory.$file);
			/*debug*/ \XLtrace\Hades\pcl('delete '.$directory.$file."\n");
		}
		if(isset($mode['chmod']) && file_exists($directory.$file)){ \chmod($directory.$file, (is_string($mode['chmod']) && preg_match('#^[0-9]+$#', $mode['chmod']) ? (int) base_convert($mode['chmod'], 8, 10) : $mode['chmod'])); }
		if(isset($mode['mtime']) && file_exists($directory.$file)){ \touch($directory.$file, $mode['mtime']); }
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
	//if(file_exists('composer.phar') && file_exists('composer.json')){ \XLtrace\Hades\composer('install'); }
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
