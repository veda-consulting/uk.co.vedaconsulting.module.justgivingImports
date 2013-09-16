<?php

class CRM_Finance_Utils_DataExchange {
    const STATUS_IMPORT_STARTED = 0;
    const STATUS_IMPORT_FINISHED = 1;
    
    private $tableName = 'veda_civicrm_financial_import';
    private $defaultCsvToDbParams = array(
        'fieldsTerminatedBy' => ',',
        'ignoreLines' => 1,
        'linesTerminatedBy' => '\n',
        'optionallyEnclosedBy' => '"',
        'characterSet' => 'utf8',
    );
    private $defaultDatToDbParams = array(
        'ignoreLines' => 3,
        'ignoreLinesEnd' => 1,
        'characterSet' => 'latin1',
    );
    private $alwaysCreateFields = array();
    
    public function setAlwaysCreateFields(array $fields) {
        $this->alwaysCreateFields = $fields;
    }
    
    public function importCsvToDb($source, $fileName, $csvFields, $params = array()) {
        //make sure file is readable at least
        if(!is_file($fileName)) {
            throw new Exception("Can't read file '$fileName'");
        }
        
        //matusz: http://dev.mysql.com/doc/refman/5.1/en/load-data.html
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $fileName = str_replace('\\', '\\\\', $fileName);
        }
        
        $params = array_merge($this->defaultCsvToDbParams, $params);
        
        $setFields = array();
        $columnFields = array();
        $fieldsCreated = array();
        
        foreach($csvFields as $field) {
            $columnField = "@dummy";
            if($field !== null) {
                $setFields[] = "$field = @$field";
                $columnField = "@$field";
                
                $fieldsCreated[] = $field;
            }
            
            $columnFields[] = $columnField;
        }
        
        $startRet = $this->startProcess('CsvToDb', $source, $fieldsCreated);
        
        $processId = $startRet['processId'];
        $tableName = $startRet['tableName'];
        
        $setFieldsSql = implode(', ', $setFields);
        $columnFieldsSql = implode(', ', $columnFields);
        
        CRM_Core_DAO::executeQuery("SET GLOBAL local_infile = 1");
        $sql = "LOAD DATA LOCAL INFILE '$fileName' INTO TABLE $tableName
            CHARACTER SET {$params['characterSet']}
            FIELDS TERMINATED BY '{$params['fieldsTerminatedBy']}'
                OPTIONALLY ENCLOSED BY '{$params['optionallyEnclosedBy']}'
            LINES TERMINATED BY '{$params['linesTerminatedBy']}'
            IGNORE {$params['ignoreLines']} LINES
            ($columnFieldsSql) SET status = 0, {$setFieldsSql}";
        
        CRM_Core_DAO::executeQuery($sql);
        
        $this->finishProcess($startRet);
        
        $process = $this->getProcessById($processId);
        
        return $process;
    }
    
    public function importDatToDb($source, $fileName, $fields, $params = array()) {
        //make sure file is readable at least
        if(!is_file($fileName)) {
            throw new Exception("Can't read file '$fileName'");
        }
        
        //matusz: http://dev.mysql.com/doc/refman/5.1/en/load-data.html
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $fileName = str_replace('\\', '\\\\', $fileName);
        }
        
        $params = array_merge($this->defaultDatToDbParams, $params);
        
        $setFields = array();
        $fieldsCreated = array();
        
        foreach($fields as $field => $fieldParams) {
            $fieldsCreated[] = $field;
            list($from, $len) = $fieldParams;
            //$len = $to - $from + 1;
            $setFields[] = "$field = SUBSTR(@var, {$from}, {$len})";
        }
        
        $startRet = $this->startProcess('DatToDb', $source, $fieldsCreated);
        $processId = $startRet['processId'];
        $tableName = $startRet['tableName'];
        
        $setFieldsSql = implode(', ', $setFields);
        
        CRM_Core_DAO::executeQuery("SET GLOBAL local_infile = 1");
        $sql = "LOAD DATA LOCAL INFILE '$fileName' INTO TABLE $tableName
            CHARACTER SET {$params['characterSet']}
            IGNORE {$params['ignoreLines']} LINES
            (@var) SET status = 0, {$setFieldsSql}";
        
        CRM_Core_DAO::executeQuery($sql);
        
        //delete lines from the end
        if($params['ignoreLinesEnd'] > 0) {
            $sql = "SELECT id FROM $tableName ORDER BY id DESC LIMIT %0";
            $ret = CRM_Core_DAO::executeQuery($sql, array(
                array($params['ignoreLinesEnd'], 'Int')
            ));
            
            $ids = array();
            while($ret->fetch()) {
                $ids[] = $ret->id;
            }
            
            $str = implode(', ', $ids);
            CRM_Core_DAO::executeQuery("DELETE FROM $tableName WHERE id IN ($str)");
        }
        
        $this->finishProcess($startRet);
        
        $process = $this->getProcessById($processId);
        return $process;
    }
    
    public function importArrayToDb($source, array $data, array $fields) {
        $startRet =  $this->startProcess('ArrayToDb', $source, $fields);
        $processId = $startRet['processId'];
        $tableName = $startRet['tableName'];
        
        $fieldCount = count($fields);
        for($i = 0; $i < $fieldCount; $i++) {
            $placeholders[] = "%$i";
        }
        $placeholdersSql = implode(', ', $placeholders);
        $insertFieldsSql = implode(', ', $fields);
        
        $sql = "INSERT INTO $tableName (status, $insertFieldsSql) VALUES (0, $placeholdersSql)";
        foreach($data as $rec) {
            $params = array();
            foreach($rec as $value) {
                $params[] = array(strval($value), 'String');
            }
            
            $ret = CRM_Core_DAO::executeQuery($sql, $params);
        }
        
        $this->finishProcess($startRet);
        
        $process = $this->getProcessById($processId);
        return $process;
    }
    
    public function findProcessIds(array $filter) {
        $params  = array();
        $sqlWhere  = array();
        if(isset($filter['source'])) {
            $sqlWhere[] = "source = %0";
            $params[0] = array($filter['source'], 'String');
        }
        
        if(isset($filter['count'])) {
            $sqlWhere[] = "count = %1";
            $params[1] = array($filter['count'], 'Int');
        }
        
        $sql = "SELECT id FROM $this->tableName WHERE " . implode(' AND ', $sqlWhere);
        $ret = CRM_Core_DAO::executeQuery($sql, $params);
        $rets = array();
        while($ret->fetch()) {
            $rets[] = $ret->toValue('id');
        }
        
        return $rets;
    }
    
    public function getProcessById($id) {
        $sql = "SELECT * FROM $this->tableName WHERE id = %0";
//        print($sql); 
        $dao = CRM_Core_DAO::executeQuery($sql, array(
            array($id, 'Int')
        ));

        //TODO test return value and throw exception if not available
        $dao->fetch();
        
        $process = $dao->toArray();
        $x = unserialize($process['fields']);

        $process['fields'] = $x['fields'];
//        $process['fieldsImported'] = $x['fieldsImported']; TBD?? Why was it the wrongway round?? How could it ever work?
        $process['importedFields'] = $x['importedFields'];
        $process['data'] = @unserialize($process['data']);
        if($process['data'] === false) {
            $process['data'] = array();
        }
        
        return $process;
    }
    
    public function updateData($processId, $id, array $data) {
        if(array_key_exists('status', $data)) {
            if(is_null($data['status'])) {
                throw new Exception("Status code invalid - provide valid integer number");
            }
        }
        
        $process = $this->getProcessById($processId);
        $dataTable = $process['handle'];
        
        
        $setArr = array();
        $paramsArr = array(
            array($id, 'Int')
        );
        
        //we count from 1 as %0 represents process ID in WHERE condition
        $i = 1;
        foreach($data as $field => $value) {
            $setArr[] = "$field = %$i";
            $paramsArr[] = array($value, 'String');
            $i++;
        }
        
        $setSql = implode(', ', $setArr);

        $sql = "UPDATE $dataTable SET $setSql WHERE id = %0";
        CRM_Core_DAO::executeQuery($sql, $paramsArr);
        
        //Associate Contact with Financial Import Reference ID
/*        
        $getReferenceSql  = " SELECT FundraiserUserId ";
        $getReferenceSql .= " FROM $dataTable ";
        $getReferenceSql .= " WHERE id = %0";
        
        $getReferenceParams = array( 1 => array( $id, 'Integer' ) );

        $daoGetReference = CRM_Core_DAO::executeQuery( $getReferenceSql, $getReferenceParams );
  
        if ($daoGetReference->fetch()) {  
            $fundraiserUserId = $daoGetReference->FundraiserUserId;
        }        

 */    
        return true;
    }
    
    public function retrieveData($processId, $id) {
        $process = $this->getProcessById($processId);
        $dataTable = $process['handle'];
        
        $setArr = array();
        $paramsArr = array(
            array($id, 'Int')
        );
        
        $sql = "SELECT * FROM $dataTable WHERE id = %0";
        $data = CRM_Core_DAO::executeQuery($sql, $paramsArr);
        if($data->N > 0) {
            $data->fetch();
            return $data->toArray();
        }
        
        throw new Exception('Not found');
    }
    
    public function executeDataDeleteQuery($processId, $whereQuery, array $params) {
        $process = $this->getProcessById($processId);
        $dataTable = $process['handle'];
        
        $sql = "DELETE FROM $dataTable WHERE $whereQuery";
        $data = CRM_Core_DAO::executeQuery($sql, $params);
        
        $count = $this->getTotalCount($processId);
         
        $sql = "UPDATE {$this->tableName} SET `count` = %1 WHERE id = %0";
        $ret = CRM_Core_DAO::executeQuery($sql, array(
            array($processId, 'Int'),
            array($count, 'Int'),
        ));
        
        return $this->getProcessById($processId);;
    }
    
    public function updateProcessData($processId, array $data) {
        $sql = "UPDATE $this->tableName SET data = %1 WHERE id = %0";
        CRM_Core_DAO::executeQuery($sql, array(
            array($processId, 'Int'),
            array(serialize($data), 'String'),
        ));
    }
    
    public function setStatus($processId, $status) {
        $status = intval($status);
        if($status < 10) {
            throw new InvalidArgumentException("Can't use internal status codes (int less than 10)");
        }
        $sql = "UPDATE {$this->tableName} SET status = %1 WHERE id = %0";
        $ret = CRM_Core_DAO::executeQuery($sql, array(
            array($processId, 'Int'),
            array($status, 'Int'),
        ));
        
        return true;
    }
    
    public function getDataDAO($processId, $params = array()) {
        $process = $this->getProcessById($processId);
        $dataTable = $process['handle'];
        
        $limitSql = '';
        $orderBySql = '';
        
        if(isset($params['limitCount'])) {
            //matusz: TODO validation
            if($params['limitOffset']) {
                $limitSql .= "{$params['limitOffset']}, ";
            }
            $limitSql .= $params['limitCount'];
            $limitSql = "LIMIT $limitSql";
        }

        if(isset($params['orderBy'])) {
            //matusz: TODO validation
            $orderBySql .= $params['orderBy'];
            $orderBySql = "ORDER BY $orderBySql";
        }

        $whereSql = $this->getDataWhereSql($params);        
        $sql = "SELECT * FROM $dataTable $whereSql $orderBySql $limitSql";
        watchdog('getDataDAO', $sql);

        $dao = CRM_Core_DAO::executeQuery($sql);
        
        return $dao;
    }
    
    public function getTotalCount($processId, $params = array()) {
        $process = $this->getProcessById($processId);
        $dataTable = $process['handle'];
        
        $whereSql = $this->getDataWhereSql($params);
        
        $sql = "SELECT COUNT(id) FROM $dataTable $whereSql";
        $ret = CRM_Core_DAO::singleValueQuery($sql);
        return $ret;
    }
    
    private function getDataWhereSql(array $params) {
        $wheres = array();
        if(isset($params['status'])) {
            //matusz: TODO ESCAPE ME please!
            $statuses = (array)$params['status'];
            $wheres = array('status IN (' . implode(',', $params['status']) . ')');
        }
        
        if(isset($params['statusLess'])) {
            //matusz: TODO ESCAPE ME please!
            $wheres = array("status < '{$params['statusLess']}'");
        }
        
        if(isset($params['statusMoreAndEq'])) {
            //matusz: TODO ESCAPE ME please!
            $wheres = array("status >= '{$params['statusMoreAndEq']}'");
        }
        
        $whereSql = '';
        if(count($wheres) > 0) {
            $whereSql = "WHERE " . implode(' AND ', $wheres);
        }
        
        return $whereSql;
    }
    
    protected function startProcess($type, $source, array $fields) {
        $allFields = array_unique(array_merge($this->alwaysCreateFields, $fields));
        
        $sql = "INSERT INTO {$this->tableName} (type, process_start, fields, status, source, created_by_id) VALUES (%0, %1, %2, %3, %4, %5)";
        $ret = CRM_Core_DAO::executeQuery($sql, array(
            array($type, 'String'),
            array(date('YmdHis'), 'Timestamp'),
            array(serialize(array('fields' => $allFields, 'importedFields' => $fields)), 'String'),
            array(CRM_Finance_Utils_DataExchange::STATUS_IMPORT_STARTED, 'Int'),
            array($source, 'String'),
            array($this->getCurrentUserId(), 'Int'),
        ));
        
        //TODO XXX matusz: how to get DAO autogen ID in Civi way?
        $lastId = CRM_Core_DAO::singleValueQuery('SELECT LAST_INSERT_ID()');
        
        $tableName = $this->createImportTable($lastId, $allFields);
        
        $sql = "UPDATE {$this->tableName} SET handle = %1 WHERE id = %0";
        $ret = CRM_Core_DAO::executeQuery($sql, array(
            array($lastId, 'Int'),
            array($tableName, 'String'),
        ));
        
        return array(
            'processId' => $lastId,
            'tableName' => $tableName,
        );
    }
    
    /**
     * @param string $process whatever startProcess returns
     */
    protected function finishProcess($process) {
        //process is ID of last row for now
        $id = $process['processId'];
        $tableName = $process['tableName'];
        
        $count = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM $tableName");
        
        //matusz: TODO what do we do with status?
        $sql = "UPDATE {$this->tableName} SET process_end = %1, `count` = %2, status = %3 WHERE id = %0";
        $ret = CRM_Core_DAO::executeQuery($sql, array(
            array($id, 'Int'),
            array(date('YmdHis'), 'Timestamp'),
            array($count, 'Int'),
            array(CRM_Finance_Utils_DataExchange::STATUS_IMPORT_FINISHED, 'Int'),
        ));
    }
    
    private function createImportTable($processId, $fields) {
        //matusz: process is variable returned from startProcess() - it's process ID at the momement
        $createFields = array();
        
        foreach($fields as $field) {
            $createFields[] = "`$field` TEXT NULL";
        }
        
        $tableName = "{$this->tableName}_$processId";
        
        $sql = "DROP TABLE IF EXISTS {$tableName}";
        CRM_Core_DAO::executeQuery($sql);
        
        $createFieldsSql = implode(', ', $createFields);
        $sql = "CREATE TABLE {$tableName} (
            `id` INT NOT NULL AUTO_INCREMENT,
            `status` INT NOT NULL,
            {$createFieldsSql}, PRIMARY KEY (`id`))";
        CRM_Core_DAO::executeQuery($sql);
        
        return $tableName;
    }
    
    private function getCurrentUserId() {
        //global $user;
        //return $user->uid;
        
        //matusz: copied from 
        $session = CRM_Core_Session::singleton();
        return $session->get( 'userID' );
    }
}