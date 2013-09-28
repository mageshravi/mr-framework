<?php

/**
 * Description of Sql2ModelController
 *
 * @author Magesh Ravi
 */
class Sql2modelController extends \com\BaseController {
    
    public function init() {
        ;
    }
    
    public function indexAction() {
        $this->log->debug("Inside " . __CLASS__ . " " . __FUNCTION__ . "()...");
        
        //SET TITLE
        $this->template->title = "Sql2Model Converter - MR Framework";

        $writable = TRUE;
        if(!is_writable('../model')) {
            $writable = FALSE;
            $this->template->err_model_permissions = 'Model directory is NOT writable!';
        }
        
        if ($writable && !empty($_POST['sql_script'])) {
            $query = $_POST['sql_script'];
            
            $pattern_createTableQuery = '/'
                    .'CREATE[ ]+TABLE[ ]+'
                    .'(?:IF[ ]+NOT[ ]+EXISTS[ ]+)*'
                    .'[`\w_]*[.]*'  // database name
                    .'[`][A-Za-z][\w_]+[`]' // table name
                    .'[^;]+'
                    .'/s';
            
            $arr_createTableMatches = array();
            
            if(preg_match_all($pattern_createTableQuery, $query, $arr_createTableMatches)) {
                // create table syntax found
                $pattern_tableName = '/'.'[`\w_]*[.]*'.'[`]([A-Za-z][\w_]+)[`]/';
                
                $pattern_field = '/'
                        .'[`][\w]+[`][ ]*'    // field name
                        .'(?:[A-Z]+|ENUM\([\w\d \',]+\))' // data type
                        .'(?:[\(][\d\,]+[\)])*[ ]*'   // length
                        .'(?:NOT[ ]*NULL|NULL)' // either NOT NULL or NULL. also to avoid PRIMARY KEY, INDEX, FOREIGN KEY & REFERENCES from interpreted as field names
                        .'/s';
                
                $pattern_foreignKeys = '/FOREIGN KEY \([ ]*`(\w+)`[ ]*\)/s';
                
                $pattern_fieldName = '/[`]([\w]+)[`]/';
                $pattern_fieldLength = '/[(]([\d\,]+)[)]/';
                $pattern_nullConstraint = '/NOT[ ]*NULL|NULL/';
                
                $arr_result = array();
                $i=0;
                foreach($arr_createTableMatches[0] as $tableSyntax) {
                    
                    // table names
                    $arr_tableNameMatches = array();
                    if(preg_match($pattern_tableName, $tableSyntax, $arr_tableNameMatches)) {
                        // table names parsed successfully
                        $arr_result[$i]['tableName'] = $arr_tableNameMatches[1];
                    }
                    
                    // foreign keys
                    $arr_fkMatches = array(); $arr_foreignKeyFields = array();
                    if(preg_match_all($pattern_foreignKeys, $tableSyntax, $arr_fkMatches)) {
                        // foreign keys found
                        $arr_foreignKeyFields = $arr_fkMatches[1];
                    } else
                        echo 'no foreign keys found!';
                    
                    // fields
                    $arr_fieldMatches = array();
                    if(preg_match_all($pattern_field, $tableSyntax, $arr_fieldMatches)) {
                        // fields parsed successfully
                        foreach($arr_fieldMatches as $arr_field) {
                            $arr_resultFields = array();
                            foreach($arr_field as $field) {
                                $arr_fieldNameMatches = array();
                                if(!preg_match($pattern_fieldName, $field, $arr_fieldNameMatches)) {
                                    die('Field name not found!');
                                }
                                
                                $arr_fieldLengthMatches = array();
                                $fieldLength = null;
                                if(preg_match($pattern_fieldLength, $field, $arr_fieldLengthMatches))
                                    $fieldLength = $arr_fieldLengthMatches[1];
                                
                                $arr_fieldNullMatches = array();
                                $notNull = FALSE;
                                if(preg_match($pattern_nullConstraint, $field, $arr_fieldNullMatches)) {
                                    if($arr_fieldNullMatches[0] != 'NULL')
                                        $notNull = TRUE;
                                }
                                $arr_resultFields[] = array('sql'=>$field, 'name'=>$arr_fieldNameMatches[1], 'length'=>$fieldLength, 'notNull'=>$notNull);
                            }
                            $arr_result[$i]['fields'] = $arr_resultFields;
                        }
                    }
                    
                    $i++;
                }
            }
            
            foreach($arr_result as $result) {
                // Class names should be singular
                // xs_have_ys should be made x_has_y
                $tableName = ucfirst(preg_replace('/s_have_/', '_has_', preg_replace('/s$/', '', $result['tableName'])));
                
                ob_start();
                echo <<<classFile
<?php \n
class $tableName {\n\n
classFile;
                // field names
                foreach($result['fields'] as $assc_field) {
                    echo <<<classFile
    private \${$assc_field['name']};\n
classFile;
                }
                
                echo "\n";
                
                // getters and setters
                foreach($result['fields'] as $assc_field) {
                    $ucFieldName = ucfirst($assc_field['name']);
                    echo <<<classFile
    public function get$ucFieldName() {
        return \$this->{$assc_field['name']};
    }
    public function set$ucFieldName(\${$assc_field['name']}) {
        \$this->{$assc_field['name']} = \${$assc_field['name']};
        return \$this;
    }\n\n
classFile;
                }
                
                // validators
                foreach($result['fields'] as $assc_field) {
                    $ucFieldName = ucfirst($assc_field['name']);
                    
                    // generate validators only for non-foreign-key fields
                    if(!in_array($assc_field['name'], $arr_foreignKeyFields)) {
                        echo <<<classFile
    public static function validate$ucFieldName(\${$assc_field['name']}) {
        // {$assc_field['sql']}\n
    }\n
classFile;
                    }
                }

                echo <<<classFile
}\n
?>
classFile;
                $output =  ob_get_contents();
                ob_end_clean();
                $path = "../model/$tableName.class.php";
                $fh = fopen($path, 'w+');
                $writeResult = fwrite($fh, $output);  
                fclose($fh);
                if($writeResult === FALSE) {
                    header('Location: /sql2model/index/status/failure');
                    exit();
                }
            }
            
            header('Location: /sql2model/index/status/success');
            exit();
        }
        
        //CALL THE VIEW FILE
        $this->template->show();

        $this->log->debug("Peak memory usage in " . __CLASS__ . " " . __FUNCTION__ . "() : " . (memory_get_peak_usage(TRUE) / 1024) . " KB");
    }
}

?>
