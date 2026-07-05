#!/bin/sh
set -e
php -r '$r=@file_get_contents("http://127.0.0.1:8080/health"); exit($r!==false&&str_contains($r,"ok")?0:1);'
