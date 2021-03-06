<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Yii Api Demo',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'defaultController'=>'post',

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// 'db'=>array(
		// 	'connectionString' => 'sqlite:protected/data/blog.db',
		// 	'tablePrefix' => 'tbl_',
		// ),
		// uncomment the following to use a MySQL database
		
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=demoapi',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
			'tablePrefix' => 'tbl_',
		),
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
        'urlManager'=>array(
        	'urlFormat'=>'path',
        	'rules'=>array(
                        'post/<id:\d+>/<title:.*?>'=>'post/view',
                        'posts/<tag:.*?>'=>'post/index',
                        // REST patterns
                        array('api/create', 'pattern'=>'api/pro/<model:\w+>', 'verb'=>'POST'), // Create
                        array('api/createreceipt', 'pattern'=>'api/receipt/create', 'verb'=>'POST'), // Create
                        array('api/finalreceipt', 'pattern'=>'api/receipt/final', 'verb'=>'GET'), // Create
                        array('api/removereceiptpro', 'pattern'=>'api/receipt/remove/product', 'verb'=>'DELETE'), // Create
                        array('api/createpdf', 'pattern'=>'api/receipt/pdf', 'verb'=>'GET'), // Create
                        array('api/updatelastproduct', 'pattern'=>'api/receipt/updatelast', 'verb'=>'PUT'), // Create
                        array('api/list', 'pattern'=>'api/<model:\w+>', 'verb'=>'GET'),
                      
                        array('api/view', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'GET'),
                        
                     
                        '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
        	),
        ),
      
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>require(dirname(__FILE__).'/params.php'),
);
