<?php
namespace com\appname\controller;
use com\appname\model\exceptions as exceptions;

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
        log_debug("Inside " . __CLASS__ . " " . __FUNCTION__ . "()...");
        
        //SET TITLE
        $this->template->title = "Sql2Model Converter - MR Framework";
        $this->template->css = array('sql2model/index');
        
        $writable = TRUE;
        if(!is_writable('../model')) {
            $writable = FALSE;
            $this->template->err_model_permissions = 'Model directory is NOT writable!';
        }
        
        if ($writable && !empty($_POST['sql_script']) && !empty($_POST['app_namespace'])) {
           
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
                $pattern_className = '/\_(\w+)/';
                $pattern_fieldType = '/[`] ([A-Z]+)/';
                $pattern_fieldLength = '/\(([\w\d \',]+)\)/';
                $pattern_nullConstraint = '/NOT[ ]*NULL|NULL/';
                
                $arr_result = array();
                $i=0;
                foreach($arr_createTableMatches[0] as $tableSyntax) {
                    
                    // namespace
                    $arr_result[$i]['app_namespace'] = strtolower($_POST['app_namespace']);
                    
                    // table names
                    $arr_tableNameMatches = array();
                    if(preg_match($pattern_tableName, $tableSyntax, $arr_tableNameMatches)) {
                        // table names parsed successfully
                        $arr_result[$i]['tableName'] = $arr_tableNameMatches[1];
                        
                        $arr_classNameMatches = array();
                        preg_match($pattern_className, $arr_tableNameMatches[1], $arr_classNameMatches);
                        $className = ucwords(
                                    preg_replace('/s have /', ' has ', 
                                        preg_replace('/s_|_|s$/', ' ',$arr_classNameMatches[1])
                                    )
                                );
                        $arr_result[$i]['className'] = preg_replace('/ /','', $className);
                    }
                    
                    // foreign keys
                    $arr_fkMatches = array(); $arr_foreignKeyFields = array();
                    if(preg_match_all($pattern_foreignKeys, $tableSyntax, $arr_fkMatches)) {
                        // foreign keys found
                        $arr_foreignKeyFields = $arr_fkMatches[1];
                    }
                    
                    // fields
                    $arr_fieldMatches = array();
                    if(preg_match_all($pattern_field, $tableSyntax, $arr_fieldMatches)) {
                        // fields parsed successfully
                        foreach($arr_fieldMatches as $arr_field) {
                            $arr_resultFields = array();
                            foreach($arr_field as $field) {
                                // field name
                                $arr_fieldNameMatches = array();
                                if(!preg_match($pattern_fieldName, $field, $arr_fieldNameMatches)) {
                                    die('Field name not found!');
                                }
                                
                                // field type
                                $arr_fieldTypeMatches = array();
                                $fieldType = '';
                                if(preg_match($pattern_fieldType, $field, $arr_fieldTypeMatches))
                                    $fieldType =  $arr_fieldTypeMatches[1];
                                
                                // field length
                                $arr_fieldLengthMatches = array();
                                $fieldLength = null;
                                
                                if(preg_match($pattern_fieldLength, $field, $arr_fieldLengthMatches))
                                    $fieldLength = $arr_fieldLengthMatches[1];
                                
                                // null check
                                $arr_fieldNullMatches = array();
                                $notNull = FALSE;
                                if(preg_match($pattern_nullConstraint, $field, $arr_fieldNullMatches)) {
                                    if($arr_fieldNullMatches[0] != 'NULL')
                                        $notNull = TRUE;
                                }
                                
                                $arr_resultFields[] = array(
                                    //'sql'=>$field, 
                                    'name'=>$arr_fieldNameMatches[1], 
                                    'type' => $fieldType,
                                    'length'=>$fieldLength, 
                                    'notNull'=>$notNull);
                            }
                            $arr_result[$i]['fields'] = $arr_resultFields;
                        }
                    }
                    
                    $i++;
                }
            }
            $this->template->arr_table = $arr_result;
        }
        
        //CALL THE VIEW FILE
        $this->template->show();

        log_debug("Peak memory usage in " . __CLASS__ . " " . __FUNCTION__ . "() : " . (memory_get_peak_usage(TRUE) / 1024) . " KB");
    }
    
    public function uploadcsvAction() {
        log_debug("Inside " . __CLASS__ . " " . __FUNCTION__ . "()...");

        $this->template->title = "Sql2Model Converter - MR Framework";
        $this->template->css = array('sql2model/index');

        if($this->request->method() == 'post') {
            try {
                if(!isset($_FILES['file']))
                    throw new exceptions\InvalidFormSubmissionException('File not selected!');

                $fileMimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
                if (!in_array($_FILES["file"]["type"], $fileMimes))
                        throw new exceptions\InvalidFormSubmissionException('Invalid file format!');

                if($_FILES["file"]["size"] > 131072)
                    throw new exceptions\InvalidFormSubmissionException('Files less than 1MB can only be uploaded!');

                if ($_FILES["file"]["error"])
                    throw new exceptions\InvalidFormSubmissionException('CSV file error!');

                $csv_temp_file = $_FILES["file"]["tmp_name"];

                $arr_data = array();
                // read excel row by row
                if (($handle = fopen($csv_temp_file, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, ",")) !== FALSE) {
                        $arr_data[] = $data;
                    }
                    fclose($handle);
                }

                // check csv column count
                if(count($arr_data[0]) != 12)
                    throw new exceptions\InvalidFormSubmissionException('CSV column count not matching!');

                $arr_results = array();
                // move to associative array, except first row(column heading)
                for($i=1; $i < count($arr_data); $i++) {
                    for($j=0;$j<count($arr_data[$i]); $j++) {
                        $arr_results[$i-1][$arr_data[0][$j]] = $arr_data[$i][$j];
                    }
                }

                $allowed = array(
                    'tableName',
                    'className',
                    'name',
                    'type',
                    'notNull',
                    'length',
                    'validation',
                    'validationType',
                    'minLength',
                    'exceptionNamespace',
                    'exceptionCode',
                    'exceptionMsg'
                    );
                
                if($allowed != array_keys($arr_results[0]))
                    throw new exceptions\InvalidFormSubmissionException('Column missing');
                
                $tableName = '';
                $i = 0;
                $j = -1;
                $arr_tables = array();
                foreach($arr_results as $row) {
                    if($tableName != $row['tableName']) {
                        $tableName = $row['tableName'];
                        $j++;
                        
                        $arr_namespaceMatches = array();
                        if(preg_match('/([\/a-z]+)[A-Z]/', $row['className'], $arr_namespaceMatches)) {
                            $appNamespace = preg_replace('/\//', '\\', 
                                    substr($arr_namespaceMatches[1], 0, (strlen($arr_namespaceMatches[1])-1)));
                        }
                        
                        // class name without namespace
                        $arr_classNameMatches = array();
                        if(preg_match('/\/([A-Z][a-zA-Z]+)$/', $row['className'], $arr_classNameMatches))
                            $arr_tables[$j]['className'] =  $arr_classNameMatches[1];
                        
                        $i=0;
                    }
                    $arr_tables[$j]['fields'][$i]['name'] = $row['name'];
                    $arr_tables[$j]['fields'][$i]['type'] = $row['type'];
                    $arr_tables[$j]['fields'][$i]['notNull'] = $row['notNull'];
                    $arr_tables[$j]['fields'][$i]['length'] = $row['length'];
                    $arr_tables[$j]['fields'][$i]['validation'] = $row['validation'];
                    $arr_tables[$j]['fields'][$i]['validationType'] = $row['validationType'];
                    $arr_tables[$j]['fields'][$i]['minLength'] = $row['minLength'];
                    $arr_tables[$j]['fields'][$i]['exceptionNamespace'] = $row['exceptionNamespace'];
                    $arr_tables[$j]['fields'][$i]['exceptionCode'] = $row['exceptionCode'];
                    $arr_tables[$j]['fields'][$i]['exceptionMsg'] = $row['exceptionMsg'];
                    
                    $i++;
                }
                
                foreach ($arr_tables as $table) {
                    ob_start();
                    echo <<<classFile
<?php
namespace {$appNamespace}; \n
class {$table['className']} {\n\n
classFile;
                    // field names
                    foreach($table['fields'] as $field) {
                        echo <<<classFile
    private \${$field['name']};\n
classFile;
                    }
                    echo "\n";

                    // getters and setters
                    foreach($table['fields'] as $field) {
                        $ucFieldName = ucfirst($field['name']);
                        echo <<<classFile
    public function get$ucFieldName() {
        return \$this->{$field['name']};
    }
    public function set$ucFieldName(\${$field['name']}) {
        \$this->{$field['name']} = \${$field['name']};
        return \$this;
    }\n\n
classFile;
                    }
                
                    
                    // validators
                    foreach($table['fields'] as $field) {
                        $ucFieldName = ucfirst($field['name']);

                        if($field['validation']) {
                            $exceptionNamespace = $field['exceptionNamespace'];
                            if(strlen(trim($exceptionNamespace))!= 0) {
                                $exceptionNamespace = preg_replace('/\//', '\\', $exceptionNamespace);
                                $exceptionNamespace = '\\'.$exceptionNamespace;
                            }
                            
                            echo <<<classFile
    public static function validate$ucFieldName(\${$field['name']}) {\n
        
classFile;
                            switch ($field['validationType']) {
                                case 'numeric':
                                    echo <<<classFile
if(!preg_match('/^\d+$/', \${$field['name']}))
            throw new exceptions{$exceptionNamespace}\Invalid{$ucFieldName}Exception('$ucFieldName not a number!');\n
classFile;
                                    break;
                                case 'alpha':
                                    echo <<<classFile
if(strlen(\${$field['name']}) < {$field['minLength']})
            throw new exceptions{$exceptionNamespace}\Invalid{$ucFieldName}Exception('Should be minimum {$field['minLength']} characters long!');\n
        if(strlen(\${$field['name']}) > {$field['length']})
            throw new exceptions{$exceptionNamespace}\Invalid{$ucFieldName}Exception('Cannot exceed {$field['length']} characters!');\n
        if(!preg_match('/^[a-z]+$/i', \${$field['name']}))
            throw new exceptions{$exceptionNamespace}\Invalid{$ucFieldName}Exception('Only alphabets allowed!');\n
classFile;
                                    break;
                                case 'alphanumeric':
                                    echo <<<classFile
if(strlen(\${$field['name']}) < {$field['minLength']})
            throw new exceptions{$exceptionNamespace}\Invalid{$ucFieldName}Exception('Should be minimum {$field['minLength']} characters long!');\n
        if(strlen(\${$field['name']}) > {$field['length']})
            throw new exceptions{$exceptionNamespace}\Invalid{$ucFieldName}Exception('Cannot exceed {$field['length']} characters!');\n
        if(!preg_match('/^[a-z0-9 ]+$/i', \${$field['name']}))
            throw new exceptions{$exceptionNamespace}\Invalid{$ucFieldName}Exception('Only alphabets numbers and whitespaces allowed!');\n
classFile;
                                    break;
                                case 'email':
                                    echo <<<classFile
if(!filter_var(\${$field['name']}, FILTER_VALIDATE_EMAIL))
            throw new exceptions{$exceptionNamespace}\Invalid{$ucFieldName}Exception('Not a valid e-mail address!');\n
classFile;
                                    break;
                                case 'datetime':
                                    echo <<<classFile
if(strtotime(\${$field['name']}) === FALSE)
            throw new exceptions{$exceptionNamespace}\Invalid{$ucFieldName}Exception('Not a valid date!');\n
classFile;
                                    break;
                                default:
                                    break;
                            }
                            echo <<<classFile
    }\n\n
classFile;
                        }
                    } 

                    echo <<<classFile
}\n
classFile;
                    $output =  ob_get_contents();
                    ob_end_clean();
                    $path = "../model/{$table['className']}.class.php";
                    $fh = fopen($path, 'w+');
                    $writeResult = fwrite($fh, $output);  
                    fclose($fh);
                    if($writeResult === FALSE) {
                        header('Location: /sql2model/index?status=failure');
                        exit();
                    }
                }
                
                // create exception classes
                $exceptionConstFileString = "<?php
namespace {$appNamespace}\exceptions;\n
class MRException extends \Exception { \n";
                foreach($arr_tables as $table) {
                    foreach($table['fields'] as $field) {
                        if($field['validation']) {
                            $exceptionNamespace = $field['exceptionNamespace'];
                            if(strlen(trim($exceptionNamespace))!= 0) {
                                $exceptionNamespace = preg_replace('/\//', '\\', $exceptionNamespace);
                                $exceptionNamespace = '\\'.$exceptionNamespace;
                                $excConstName = preg_replace('/\//', '_', $field['exceptionNamespace']);
                            }
                            $exceptionClassName = 'Invalid'.ucfirst($field['name']).'Exception';
                            $exceptionConstName = 'INVALID_'.strtoupper("{$excConstName}_{$field['name']}");
                            
                            $exceptionConstFileString .="
    const ". $exceptionConstName." = ". $field['exceptionCode'].";";
                            
                            ob_start();
                            echo <<<classFile
<?php
namespace {$appNamespace}\exceptions{$exceptionNamespace};
class {$exceptionClassName} extends {$appNamespace}\exceptions\MRException {\n
    public function __construct(\$message="{$field['exceptionMsg']}") {\n
        parent::__construct(\$message, self::{$exceptionConstName});\n
    }\n
    public function __toString() {\n
        return __CLASS__.':['.\$this->code.']:'.\$this->message;\n
    }\n
}\n
classFile;
                            $output =  ob_get_contents();
                            ob_clean();
                            $dir_path = "../model/exceptions/{$field['exceptionNamespace']}";

                            if (!file_exists($dir_path)) {
                                mkdir($dir_path, 0777, TRUE);
                            }

                            $path = "../model/exceptions/"; 
                            if(strlen(trim($field['exceptionNamespace'])) > 0)
                                $path .= $field['exceptionNamespace']."/";
                            $path .= "{$exceptionClassName}.class.php";
                            log_debug($path);
                            $fh = fopen($path, 'w+');
                            $writeResult = fwrite($fh, $output);  
                            fclose($fh);
                            if($writeResult === FALSE) {
                                header('Location: /sql2model/index?status=failure');
                                exit();
                            }
                        }
                    }
                }
                
                $exceptionConstFileString .= "
}";
                $mrException_fp = fopen("../model/exceptions/MRException.class.php", 'w+');
                fwrite($mrException_fp, $exceptionConstFileString);
                fclose($mrException_fp);
                
                header('Location: /sql2model/index?status=success');
                exit();

            } catch (exceptions\InvalidFormSubmissionException $e) {
                log_debug($e->getMessage());
                $this->template->exc = $e->getMessage();
            } catch (\Exception $e) {
                log_debug($e->getMessage());
                $this->template->exc = $e->getMessage();
            }
        } else {
            $this->template->exc = 'Invalid form submission';
        }
        $this->template->show();
    }
}