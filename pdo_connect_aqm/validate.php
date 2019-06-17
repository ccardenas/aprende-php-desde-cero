<?php

$url = 'wwww.komunitasweb.com/';
if (!preg_match('/http(s?)\:\/\//i', $url)) {
    echo 'Your url is ok.';
} else {
    echo 'Wrong url.';
}
