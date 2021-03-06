<?php

class ApiController extends Controller
{
    // {{{ *** Members ***
    /**
     * Key which has to be in HTTP USERNAME and PASSWORD headers 
     */
    Const APPLICATION_ID = 'ASCCPE';

    private $format = 'json';
    

    public function actionIndex()
    {
        echo CJSON::encode(array(1, 2, 3));
    } 



    public function actionList()
    { 
        $this->_checkAuth();
        switch($_GET['model'])
        {
            case 'product': // {{{ 
                $models = Product::model()->findAll();
                break; // }}} 
            default: // {{{ 
                $this->_sendResponse(501, sprintf('Error: Mode <b>list</b> is not implemented for model <b>%s</b>',$_GET['model']) );
                exit; // }}} 
        }
        if(is_null($models)) {
            $this->_sendResponse(200, sprintf('No items where found for model <b>%s</b>', $_GET['model']) );
        } else {
            $rows = array();
            foreach($models as $model)
                $rows[] = $model->attributes;

            $this->_sendResponse(200, CJSON::encode($rows));
        } 
    } 

    public function actionView()
    {
        $this->_checkAuth();
        // Check if id was submitted via GET
        if(!isset($_GET['id']))
            $this->_sendResponse(500, 'Error: Parameter <b>id</b> is missing' );

        switch($_GET['model'])
        {
            // Find respective model    
            case 'posts': // {{{ 
                $model = Post::model()->findByPk($_GET['id']);
                break; // }}} 
            default: // {{{ 
                $this->_sendResponse(501, sprintf('Mode <b>view</b> is not implemented for model <b>%s</b>',$_GET['model']) );
                exit; // }}} 
        }
        if(is_null($model)) {
            $this->_sendResponse(404, 'No Item found with id '.$_GET['id']);
        } else {
            $this->_sendResponse(200, $this->_getObjectEncoded($_GET['model'], $model->attributes));
        }
    } 

    public function actionUpdatelastproduct()
    {
        $this->_checkAuth();
        $put_vars = json_decode(file_get_contents('php://input'));
        //print_r($put_vars); die;
        $criteria=new CDbCriteria;
        $criteria->compare('rid',$put_vars->id);
        $criteria->order='id DESC';
        if(!isset($put_vars->id))
            $this->_sendResponse(500, 'Error: Parameter <b>id</b> is missing' );

        if($put_vars->id)
        {
            $model = Receipt::model()->find($criteria);
            $model->product_total = $put_vars->price;
            $model->total_price = ($put_vars->price + (($put_vars->price) * $model->vat) / 100);
            $model->save();
              
        }
        
        if(is_null($model)) {
            $this->_sendResponse(404, 'No Item found with id '. $put_vars->id);
        } else {
            $this->_sendResponse(200, $this->_getObjectEncoded('receipt', $model->attributes));
        }
    } 



    public function actionCreate()
    {
        $this->_checkAuth();

        switch($_GET['model'])
        {
            // Get an instance of the respective model
            case 'product': // {{{ 
                $model = new Product;                    
                break; // }}} 
            default: // {{{ 
                $this->_sendResponse(501, sprintf('Mode <b>create</b> is not implemented for model <b>%s</b>',$_GET['model']) );
                exit; // }}} 
        }
        // Try to assign POST values to attributes

        foreach($_POST as $var=>$value) {
            // Does the model have this attribute?
            if($model->hasAttribute($var)) {
                $model->$var = $value;
            } else {
                // No, raise an error
                $this->_sendResponse(500, sprintf('Parameter <b>%s</b> is not allowed for model <b>%s</b>', $var, $_GET['model']) );
            }
        }
        // Try to save the model
        if($model->save()) {
            // Saving was OK
            $this->_sendResponse(200, $this->_getObjectEncoded($_GET['model'], $model->attributes) );
        } else {
            // Errors occurred
            $msg = "<h1>Error</h1>";
            $msg .= sprintf("Couldn't create model <b>%s</b>", $_GET['model']);
            $msg .= "<ul>";
            foreach($model->errors as $attribute=>$attr_errors) {
                $msg .= "<li>Attribute: $attribute</li>";
                $msg .= "<ul>";
                foreach($attr_errors as $attr_error) {
                    $msg .= "<li>$attr_error</li>";
                }        
                $msg .= "</ul>";
            }
            $msg .= "</ul>";
            $this->_sendResponse(500, $msg );
        }

        var_dump($_REQUEST);
    } 

    public function actionCreatereceipt()
    {
        $this->_checkAuth();

       
        $model = new Receipt;                    
        
        
         $pro = Product::model()->find('barcode = "'.$_POST['barcode'].'"');
       
         if(!empty($pro)){
            $model->rid = 0;
            $model->product_id = $pro->id; 
            $model->product_total = $pro->cost;
            $model->product_discount =  0;
            $model->vat = $pro->vat_class;
            $model->total_price = ($pro->cost + (($pro->cost * $pro->vat_class) / 100));
         }else{
             $this->_sendResponse(500, sprintf('Data not found', '', 'Receipt') );
         }       

      
        // Try to save the model
        if($model->save()) {
            // Saving was OK
            $this->_sendResponse(200, $this->_getObjectEncoded('receipt', $model->attributes) );
        } else {
            // Errors occurred
            $msg = "<h1>Error</h1>";
            $msg .= sprintf("Couldn't create model <b>%s</b>", 'Receipt');
            $msg .= "<ul>";
            foreach($model->errors as $attribute=>$attr_errors) {
                $msg .= "<li>Attribute: $attribute</li>";
                $msg .= "<ul>";
                foreach($attr_errors as $attr_error) {
                    $msg .= "<li>$attr_error</li>";
                }        
                $msg .= "</ul>";
            }
            $msg .= "</ul>";
            $this->_sendResponse(500, $msg );
        }

        var_dump($_REQUEST);
    } 
   
    public function actionFinalreceipt()
    {
        $this->_checkAuth();
        
        $pro = Receipt::model()->findAll('rid = 0');
        $ridno = rand(1,5);
        $i = 1;
        if(!empty($pro)){
            $total_price = 0;
            $total_disc = 0;
            $total  = 0;
             $data = "<table border=1><tr><th colspan='5'>Receipt No : ONECLICK".$ridno."</th></tr><tr><th>product name</th><th>Price</th><th>discount</th><th>vat</th><th>Total Price</th></tr>";
            foreach ($pro as $key => $value) {
                $prod = Product::model()->find('id = '.$value->product_id);

                if($i == 3){
                    $value->product_discount = $value->product_total;
                    $value->save();
                }

                if(!empty($prod)){
                    $total_price = $value->total_price + $total_price;
                    $total_disc = $value->product_discount + $total_disc;
                    $total = ($total_price - $total_disc);
                    $data .= "<tr><td>".$prod->name."</td>
                             <td>".$value->product_total."</td>
                              <td>".$value->product_discount."</td>
                               <td>".$value->vat."</td>
                               <td>".$value->total_price."</td></tr>";
                }

                # code...
                $i++;
                $value->rid = $ridno;
                $value->save();
            }
               $data .=" <tr>
                        <td colspan='4'>Total Price</td>
                        <td>".$total_price."</td>
                      </tr> <tr>
                        <td colspan='4'>Total discount</td>
                        <td>".$total_disc."</td>
                      </tr> <tr>
                        <td colspan='4'>Total </td>
                        <td>".$total."</td>
                      </tr></table>";
            $this->_sendResponse(200, $data );
         }else{
             $this->_sendResponse(500, sprintf('Data not found', '', 'Receipt') );
         }       

        var_dump($_REQUEST);
    } 
   


    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {

        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        // set the status
        header($status_header);
        // set the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if($body != '')
        {
            // send the body
            echo json_encode(array('HTTP_CODE' => 200, 'DATA'=>$body));
            exit;
        }
        // we need to create the body if none is passed
        else
        {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch($status)
            {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templatized in a real-world solution
            $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                        <html>
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                                <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
                            </head>
                            <body>
                                <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
                                <p>' . $message . '</p>
                                <hr />
                                <address>' . $signature . '</address>
                            </body>
                        </html>';

            echo $body;
            exit;
        }
    } 


    public function actionRemovereceiptpro()
    {
        $this->_checkAuth();
        $put_vars = json_decode(file_get_contents('php://input'));
        
         $pro = Receipt::model()->find('product_id = "'.$put_vars->productId.'" AND rid=0');
       
               

        if(empty($pro)){
            $this->_sendResponse(500, sprintf("Error: Couldn't delete model <b>%s</b> withs ID <b>%s</b>.","receipt",$put_vars->productId) );
            exit;
        }
        $num = $pro->delete();
        if($num>0){
                  $this->_sendResponse(200, sprintf("Model <b>%s</b> with ID <b>%s</b> has been deleted.",'receipt', $pro->id) );
        }else{
            $this->_sendResponse(500, sprintf("Error: Couldn't delete model <b>%s</b> with ID <b>%s</b>.","receipt",0) );
        }
        
       
    } 

    public function actionCreatepdf(){
         require('phpToPDF.php');

        $this->_checkAuth();
            $data = "";
            $pro = Receipt::model()->findAll('rid = '.$_GET['rid']);
            $i = 1;
            if(!empty($pro)){
                $total_price = 0;
                $total_disc = 0;
                $total  = 0;
                 
                foreach ($pro as $key => $value) {
                    $prod = Product::model()->find('id = '.$value->product_id);

                    if(!empty($prod)){
                        $total_price = $value->total_price + $total_price;
                        $total_disc = $value->product_discount + $total_disc;
                        $total = ($total_price - $total_disc);
                        $data .= "<tr><td>".$prod->name."</td>
                                 <td>".$value->product_total."</td>
                                  <td>".$value->product_discount."</td>
                                   <td>".$value->vat."</td>
                                   <td>".$value->total_price."</td></tr>";
                    }

                    # code...
                    $i++;
                    $ridno = $value->rid;
                }
                $data = "<table border=1><tr><th colspan='5'>Receipt No : ONECLICK".$ridno."</th></tr><tr><th>product name</th><th>Price</th><th>discount</th><th>vat</th><th>Total Price</th></tr>".$data;
                   $data .=" <tr>
                            <td colspan='4'>Total Price</td>
                            <td>".$total_price."</td>
                          </tr> <tr>
                            <td colspan='4'>Total discount</td>
                            <td>".$total_disc."</td>
                          </tr> <tr>
                            <td colspan='4'>Total </td>
                            <td>".$total."</td>
                          </tr></table>";
                 $pdf_options = array(
                      "source_type" => 'html',
                      "source" => $data,
                      "action" => 'save',
                      //"save_directory" => "data",
                      "file_name"=>'ONECLICK'.$ridno.'.pdf');
                      phptopdf($pdf_options);
                $retur = array("ReceiptLink" => Yii::app()->params['hostname'].$pdf_options['file_name']);
                $this->_sendResponse(200, $retur );
             }else{
                 $this->_sendResponse(500, sprintf('Not able to create PDF', '', 'Receipt') );
             }       
   
     }

    private function _getStatusCodeMessage($status)
    {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    } 


    private function _checkAuth()
    {
        
        // Check if we have the USERNAME and PASSWORD HTTP headers set?
        if(!(isset($_SERVER['HTTP_USERNAME']) and isset($_SERVER['HTTP_USERPASSWORD']))) {
            // Error: Unauthorized
            $this->_sendResponse(401);
        }
        $username = $_SERVER['HTTP_USERNAME'];
        $password = $_SERVER['HTTP_USERPASSWORD'];
       
        $user=User::model()->find('LOWER(username)=?',array(strtolower($username)));
        if($user===null) {
            // Error: Unauthorized
            $this->_sendResponse(401, 'Error: User Name is invalid');
        } else if(!$user->validatePassword($password)) {
            // Error: Unauthorized
            $this->_sendResponse(401, 'Error: User Password is invalid');
        }
    } 

    private function _getObjectEncoded($model, $array)
    {
        if(isset($_GET['format']))
            $this->format = $_GET['format'];

        if($this->format=='json')
        {
            return CJSON::encode($array);
        }
        elseif($this->format=='xml')
        {
            $result = '<?xml version="1.0">';
            $result .= "\n<$model>\n";
            foreach($array as $key=>$value)
                $result .= "    <$key>".utf8_encode($value)."</$key>\n"; 
            $result .= '</'.$model.'>';
            return $result;
        }
        else
        {
            return;
        }
    } 
}

?>
