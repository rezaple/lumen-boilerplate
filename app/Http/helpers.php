<?php

use Carbon\Carbon;

if (! function_exists('check_convinience')) {
    function check_convinience($number){
        if(abs($number) > 250 || abs($number) < 50 ){
            return -1 * rand(50, 250);
        }
        return -1 * abs($number);
    }
}

function set_discount_type($type, $value)
{
    if($type=='percent')
        return "{$value}%";
    elseif($type=='fix')
        return "Rp. {$value}";
    else
        return "No Discount";
}

if (! function_exists('move_file')) {
    function move_file($file, $type='avatar', $withWatermark = false)
    {
        // Grab all variables
        $destinationPath = config('variables.'.$type.'.folder');
        $width           = config('variables.' . $type . '.width');
        $height          = config('variables.' . $type . '.height');
        $full_name       = str_random(16) . '.' . $file->getClientOriginalExtension();

        if ($width == null && $height == null) { // Just move the file
            $file->storeAs($destinationPath, $full_name);
            return $full_name;
        }


        // Create the Image
        $image  = Image::make($file->getRealPath());

        if ($width == null || $height == null) {
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        }else{
            $image->fit($width, $height);
        }

        if ($withWatermark) {
            $watermark = Image::make(public_path() . '/img/watermark.png')->resize($width * 0.5, null);

            $image->insert($watermark, 'center');
        }

        Storage::put($destinationPath . '/' . $full_name, (string) $image->encode());

        return $full_name;
    }
}

if (! function_exists('set_currency')) {
	function set_currency($number, $currency="idr"){
		if(empty($number)){
			return 0.0;
		}
		$currencyFormat=['idr'=>'Rp ','usd'=> '$ '];
		return $currencyFormat[$currency].number_format($number,0,",",".").',-';
	}
}


if (! function_exists('set_image')) {
	function set_image($path){
		if(empty($path)){
			return null;
		}
		return url($path);
	}
}

function set_phone($phone, $code=null)
{
	$default_country_code  = ($code==null)?'62':preg_replace("/[^0-9]/", "", $code);

	//Remove any parentheses and the numbers they contain:
	$phoneResult = preg_replace("/\([0-9]+?\)/", "", $phone);

	//Strip spaces and non-numeric characters:
	$phoneResult = preg_replace("/[^0-9]/", "", $phoneResult);

	//Strip out leading zeros:
	$phoneResult = ltrim($phoneResult, '0');

	//Look up the country dialling code for this number:
	$pfx = $default_country_code;

	//Check if the number doesn't already start with the correct dialling code:
	if ( !preg_match('/^'.$pfx.'/', $phoneResult)  ) {
		$phoneResult = $pfx.$phoneResult;
	}

	//return the converted number:
	return $phoneResult;
}

function count_date($dates)
{
	$arr = ['weekday'=>0,'weekend'=>0,'peakseason'=>0,];
	foreach ($dates as $key => $value) {
		$arr[$value] +=1;
	}
	return $arr;
}


if ( ! function_exists('config_path'))
{
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if (!function_exists('public_path')) {
    function public_path($path = null)
    {
      	return rtrim(app()->basePath('public' . $path), '/');
       //return realpath(__DIR__.'/../../../harvard/public/api/').'/'.$path;
    }
}

if (!function_exists('get_item_list')) {
    function get_item_list($items)
    {
      	$itemArr=[];
      	foreach ($items as $item) {
      		$data =[
      			'id' => $item->id,
      			'name' => $item->item_name,
      			'visit_date' => $item->visit_date,
      			'end_date' => $item->end_date,
      			'package_hour' => $item->itemable->package,
      			'package_hour' => $item->itemable->package,
      		];
      	}
    }
}

if (!function_exists('change_date')) {
	function change_date($date)
	{
		$dateFormat = \DateTime::createFromFormat('d/m/Y H:i:s',$date);
		return date_format($dateFormat, 'Y-m-d H:i:s');
	}
}

if (!function_exists('get_base64_string')) {
	function get_base64_string($base64)
	{
		 $arrFormat = array(
            "data:image/png;base64," => "",
            "data:image/jpg;base64," => "",
            "data:image/jpeg;base64," => "",
            "data:image/gif;base64," => "",
            "data:image/svg+xml;base64," => "",);

        $file = strtr($base64,$arrFormat);
        return $file;
	}
}

if(!function_exists('mime2ext')){
	function mime2ext($mime){
    $all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp",
		    "image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp",
		    "image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp",
		    "application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg",
		    "image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],
		    "wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],
		    "ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg",
		    "video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],
		    "kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],
		    "rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application",
		    "application\/x-jar"],"zip":["application\/x-zip","application\/zip",
		    "application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],
		    "7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],
		    "svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],
		    "mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],
		    "webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],
		    "pdf":["application\/pdf","application\/octet-stream"],
		    "pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],
		    "ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office",
		    "application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],
		    "xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],
		    "xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel",
		    "application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],
		    "xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo",
		    "video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],
		    "log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],
		    "wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],
		    "tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop",
		    "image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],
		    "mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar",
		    "application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40",
		    "application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],
		    "cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary",
		    "application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],
		    "ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],
		    "wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],
		    "dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php",
		    "application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],
		    "swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],
		    "mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],
		    "rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],
		    "jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],
		    "eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],
		    "p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],
		    "p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';
		    $all_mimes = json_decode($all_mimes,true);
		    foreach ($all_mimes as $key => $value) {
		        if(array_search($mime,$value) !== false) return $key;
		    }
		    return false;
		}
}

if (! function_exists('date_range')) {
	function date_range( $first, $last, $step = '+1 day', $format = 'Y-m-d' ) {
	    $dates = [];

	    if(check_date($first)==false || check_date($last)==false){
			return $dates;
		}

	    $current = strtotime( $first );
	    $last = strtotime( $last );

	    while( $current <= $last ) {
	        $dates[] = date( $format, $current );
	        $current = strtotime( $step, $current );
	    }

	    return $dates;
	}
}

if(! function_exists('check_date')){
	function check_date($date) {
	  $tempDate = explode('-', $date);
	  return checkdate($tempDate[1], $tempDate[2], $tempDate[0]);
	}
}

if(!function_exists('convert_to_hours_mins')){
	function convert_to_hours_mins($time, $format = '%02d:%02d') {
	    if ($time < 1) {
	        return;
	    }
	    $hours = floor($time / 60);
	    $minutes = ($time % 60);
	    return sprintf($format, $hours, $minutes);
	}
}

if(! function_exists('get_minutes')){
	function get_minutes($start, $end)
	{
		$dateStart = Carbon::parse($start);
		$dateFinish = Carbon::parse($end);
		return $dateFinish->diffInMinutes($dateStart, false);
	}
}

if (! function_exists('storeToJson')) {
	function storeToJson($dataSetting)
	{
	    $settings = [];
	    foreach ($dataSetting as $val) {
	        $settings[$val->key] = serialize($val->value);
	    }
	    file_put_contents(storage_path('settings.json'), json_encode($settings));
	}
}

if (! function_exists('getAllSettings')) {
	function getAllSettings()
	{
	    $values = json_decode(file_get_contents(storage_path('settings.json')), true);
	    foreach ($values as $key => $value) {
	        $values[$key] = unserialize($value);
	    }
	    return $values;
	}
}

if (! function_exists('getSetting')) {
	function getSetting($key, $default = null)
	{
	    $settings=getAllSettings();
	    return (array_key_exists($key, $settings) ? $settings[$key] : $default);
	}
}

if (! function_exists('array2json')) {
	function array2json($arr) {
	    if(function_exists('json_encode')) return json_encode($arr); //Lastest versions of PHP already has this functionality.
	    $parts = array();
	    $is_list = false;

	    //Find out if the given array is a numerical array
	    $keys = array_keys($arr);
	    $max_length = count($arr)-1;
	    if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
	        $is_list = true;
	        for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
	            if($i != $keys[$i]) { //A key fails at position check.
	                $is_list = false; //It is an associative array.
	                break;
	            }
	        }
	    }

	    foreach($arr as $key=>$value) {
	        if(is_array($value)) { //Custom handling for arrays
	            if($is_list) $parts[] = array2json($value); /* :RECURSION: */
	            else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
	        } else {
	            $str = '';
	            if(!$is_list) $str = '"' . $key . '":';

	            //Custom handling for multiple data types
	            if(is_numeric($value)) $str .= $value; //Numbers
	            elseif($value === false) $str .= 'false'; //The booleans
	            elseif($value === true) $str .= 'true';
	            else $str .= '"' . addslashes($value) . '"'; //All other things
	            // :TODO: Is there any more datatype we should be in the lookout for? (Object?)

	            $parts[] = $str;
	        }
	    }
	    $json = implode(',',$parts);

	    if($is_list) return '[' . $json . ']';//Return numerical JSON
	    return '{' . $json . '}';//Return associative JSON
	}
}
