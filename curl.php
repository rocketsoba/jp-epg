<?php
  function curl($url,...$tail){
    $headers=array(
      /* "HTTP/1.1", */
      "Accept-Language: ja,en-US;q=0.7,en;q=0.3",
      "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0",
      "Proxy-Connection:"
    );
    $ch=curl_init();

    if(isset($tail[0])){
      /* curl_setopt($ch,CURLOPT_HTTPPROXYTUNNEL,true); */
      curl_setopt($ch,CURLOPT_PROXY,$tail[0]);
      /* preg_match("/.+:([0-9]+)/",$tail[0],$port); */
      /* var_dump($port); */
      /* curl_setopt($ch,CURLOPT_PROXYTYPE,CURLPROXY_HTTP);
	 curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"GET");
	 curl_setopt($ch,CURLOPT_PROXYPORT,$port[1]); */
      curl_setopt($ch,CURLOPT_HTTPGET,true);
    }
    if(isset($tail[1])){
      $headers[]="Accept: audio/webm,audio/ogg,audio/wav,audio/*;q=0.9,application/ogg;q=0.7,video/*;q=0.6,*/*;q=0.5";
      $headers[]=$tail[1];
    }
    else{
      $headers[]="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
      curl_setopt($ch,CURLOPT_ENCODING,"gzip,deflate");
    }

    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_COOKIEFILE,__DIR__."/mp3cookie.tmp");
    curl_setopt($ch,CURLOPT_COOKIEJAR,__DIR__."/mp3cookie.tmp");
    curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($ch,CURLINFO_HEADER_OUT,true);
    curl_setopt($ch,CURLOPT_HEADER,true);

    $result=curl_exec($ch);
    $reqhed=curl_getinfo($ch,CURLINFO_HEADER_OUT);
    $curlinfo=curl_getinfo($ch);
    $reshed=substr($result,0,$curlinfo["header_size"]);
    $body=substr($result,$curlinfo["header_size"]);
    curl_close($ch);
    return [$body,$reshed,$reqhed];
  }
?>
