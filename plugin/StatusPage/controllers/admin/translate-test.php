<?php

$ch = curl_init( 'https://translation.googleapis.com/language/translate/v2' );
$payload = json_encode( array(
  'key' => 'AIzaSyA-1umYLwJTexAsD_SwjLa7-cJZAjCijvc',
  'q' => array('Hello world', 'My name is Jeff'),
  'target' => 'de'
  ) );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$result = curl_exec($ch);
curl_close($ch);
echo "<pre>$result</pre>";
die(__LINE__.': '.__FILE__);
