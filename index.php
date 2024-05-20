<?php
require 'vendor/autoload.php';
require 'utils/simple_html_dom.php';

use flight\Engine;

$app = new Engine();
 
include 'routes/api.php';

$app->start();