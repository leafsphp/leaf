<?php if(!class_exists('Leaf\Veins\Template')){exit;}?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars( $title, ENT_COMPAT, 'UTF-8', FALSE ); ?></title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le styles -->
	<link rel="stylesheet/less" type="text/css" href="<?php echo static::$conf['base_url']; ?>veins/lib/bootstrap.less"></link>
	<script src="<?php echo static::$conf['base_url']; ?>veins/js/less-1.1.5.min.js"></script>
    <style type="text/css">
      body {
        padding-top: 60px;
      }
    </style>

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?php echo static::$conf['base_url']; ?>veins/images/favicon.ico">
    <link rel="apple-touch-icon" href="<?php echo static::$conf['base_url']; ?>veins/images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo static::$conf['base_url']; ?>veins/images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo static::$conf['base_url']; ?>veins/images/apple-touch-icon-114x114.png">
  </head>

  <body>

    <div class="topbar">
      <div class="fill">
        <div class="container">
          <a class="brand" href="<?php echo static::$conf['base_url']; ?>#"><?php echo htmlspecialchars( $pageTitle, ENT_COMPAT, 'UTF-8', FALSE ); ?></a>
          <ul class="nav">
            <?php $counter1=-1;  if( isset($headerLinks) && ( is_array($headerLinks) || $headerLinks instanceof Traversable ) && sizeof($headerLinks) ) foreach( $headerLinks as $key1 => $value1 ){ $counter1++; ?>
              <li>
                <a href="<?php echo static::$conf['base_url']; ?>#"><?php echo htmlspecialchars( $value1, ENT_COMPAT, 'UTF-8', FALSE ); ?></a>
              </li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </div>

    <div class="container">

      <!-- Main hero unit for a primary marketing message or call to action -->
      <div class="hero-unit">
        <h1>Hello, world!</h1>
        <p>Vestibulum id ligula porta felis euismod semper. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.</p>
        <p><a class="btn primary large">Learn more &raquo;</a></p>
      </div>

      <!-- Example row of columns -->
      <div class="row">
        <?php $counter1=-1;  if( isset($articles) && ( is_array($articles) || $articles instanceof Traversable ) && sizeof($articles) ) foreach( $articles as $key1 => $value1 ){ $counter1++; ?>
          <div class="span-one-third">
            <h2><?php echo htmlspecialchars( $value1['title'], ENT_COMPAT, 'UTF-8', FALSE ); ?></h2>
            <p><?php echo htmlspecialchars( $value1['body'], ENT_COMPAT, 'UTF-8', FALSE ); ?></p>
            <p><a class="btn" href="/article/<?php echo htmlspecialchars( $value1['id'], ENT_COMPAT, 'UTF-8', FALSE ); ?>">View details &raquo;</a></p>
          </div>
        <?php } ?>
      </div>

      <footer>
        <p>&copy; Company 2011</p>
      </footer>

    </div> <!-- /container -->

  </body>
</html>