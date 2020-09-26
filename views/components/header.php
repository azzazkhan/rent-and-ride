<?php
  $title = "Rent and Ride &mdash; Chalo Safar Karain";
  if (isset($this)) { // Prevent errors for direct rendering function
    if (is_string($this->title))
      $title = $this->title;
  }
?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?= $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="content-language" content="en-US" />
    <meta name="description" content="" />
    <meta name="robots" content="index,follow" />
    <meta
      http-equiv="Cache-Control"
      content="no-cache, no-store, must-revalidate"
    />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="/assets/css/normalize.min.css" />
    <link rel="stylesheet" href="/assets/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= hash("crc32b", microtime());?>" />
    <script src="/assets/js/jquery.min.js"></script>
  </head>
  <body>
