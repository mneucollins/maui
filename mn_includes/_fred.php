 <?php

//define('LOCALHOST', 'localhost' === $_SERVER['SERVER_NAME']);

//==========================================================================  
function fred($data='usage: fred("any type of data")', $data_name='missing_$data_name')
{
    $data_type = '';
    // $data objects do not display as an array so...
    if (is_object($data))
    {
        $data = get_object_vars($data); // returns with $data = array();
    }
    
    // maybe find the $data type
    if (empty($data))
    {
        $data_type     = "empty()";     
    }else{    
        switch($data)
        {
            case ('' == $data)     :
                $data_type     = "empty()";     
                break;
            case is_array($data)     :
                $data_count    = count($data);
                $data_type     = "array($data_count)";     
                break;
            case is_bool($data) : 
                $data_type    = 'boolean';
                $data                = $data ? 'TRUE' : 'FALSE';
                break;
            case is_numeric($data) : 
                $data_type    = 'numeric';
                break;
            case is_object($data) : 
                $data_type    = 'object'; 
                $data                =    get_object_vars($my_class);
                break;
            case is_resource($data) : 
                $data_type    = 'resource';
                $data_count    = mysql_num_rows($data);
                $tmp                = array();
                while ($row = mysql_fetch_assoc($data))
                {
                    $tmp[] = $row;
                }
                $data = $tmp;
                break;
            case is_string($data) : 
                $data_type    = 'string';
                $data_count    = strlen($data);
                break;
            default:       
                $data_type     = 'oddball';
                $data_value    = $data;
        }//end switch
    }//endif    
        
    // $data must now be an array or a string, numeric, or...
    $style = 'width:96%; margin:1em; overflow:auto;text-align:left; font-family:Courier; font-size:0.86em; background:#efe none; color:#000; text-align:left; border:solid 1px;padding:0.42em';
    echo "<fieldset style='$style'>";
            echo    '<legend>John&rsquo;s Data Determiner:</legend>';        
            echo    "<br /><b style='color:#f00'>Name &nbsp; ==> "    .$data_name .'</b>';
            echo    '<br /><b>Type &nbsp; ==> </b>'        .$data_type;
            if (isset($data_count))
            {
                echo    '<br /><b>Count&nbsp; ==> </b>'        .$data_count;
            }    
            echo    '<br /><b>Value &nbsp;==> </b>';
            echo    "<pre style='width:58.88%; margin:-1.2em 0 1em 9.0em;overflow:auto'>";
                print_r($data);
            echo '</pre>';
    echo '</fieldset>';        
}//endfunc  