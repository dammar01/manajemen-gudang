<?php
require_once 'utils/Session.php';

Session::start();
Session::destroy();

header("Location: /login.php");
exit;
