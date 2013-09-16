<?php
require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';
require_once 'CRM/Finance/Utils/DataExchange.php';
require_once 'CRM/Finance/BAO/Import/SourceAbstract.php';

class CRM_Finance_Selector_Import extends CRM_Core_Selector_Base implements CRM_Core_Selector_API {
    /**
     * @var array
     */
    private $importData;
    /**
     *
     * @var CRM_Finance_Utils_DataExchange 
     */
    private $dataExchange;
    /**
     *
     * @var CRM_Finance_BAO_Import_SourceAbstract
     */
    private $bao;
    private $status;
    
    public function __construct($importId, $params = array()) {
        $this->dataExchange = new CRM_Finance_Utils_DataExchange();
        $this->importData = $this->dataExchange->getProcessById($importId);
        $this->params = array();
        require_once 'CRM/Finance/BAO/Import/Source.php';
        $this->bao = CRM_Finance_BAO_Import_Source::factory($this->importData['source']);
        
        if(isset($params['status'])) {
            switch($params['status']) {
                case 'error':
                    $this->params['statusMoreAndEq'] = 10;
                    break;
                case 'ok':
                    $this->params['statusLess'] = 10;
                    break;
            }
        }
    }
    
    /**
     * Based on the action, the GET variables and the session state
     * it adds various key => value pairs to the params array including
     * 
     *  status    - the status message to display. Modifiers will be defined
     *              to integrate the total count and the current state of the 
     *              page: e.g. Displaying Page 3 of 5
     *  csvString - The html string to display for export as csv
     *  rowCount  - the number of rows to be included
     *
     * @param string action the action being performed
     * @param array  params the array that the pagerParams will be inserted into
     * 
     * @return void
     *
     * @access public
     *
     */
    function getPagerParams( $action, &$params ) {
        return array(
            //'status' => 'Displaying Page %s of %s',
            //'csvString' => 'csvString???',
            //'rowCount' => 10,
        );
    }

    /**
     * returns the column headers as an array of tuples:
     * (name, sortName (key to the sort array))
     *
     * @param string $action the action being performed
     * @param enum   $type   what should the result set include (web/email/csv)
     *
     * @return array the column headers that need to be displayed
     * @access public
     */
    function &getColumnHeaders( $action = null, $type = null ) {
        $columns = array(
            array(
                'name' => 'id',
                'sort' => 0,
                'direction' => CRM_Utils_Sort::ASCENDING,
            ),
            array(
                'name' => 'status_desc',
                'sort' => 0,
                'direction' => CRM_Utils_Sort::ASCENDING,
            )
        );
        
        foreach($this->importData['fields'] as $field) {
            $columns[] = array(
                'name' => $field,
                'sort' => 0,
                'direction' => CRM_Utils_Sort::ASCENDING,
            );
        }
        
        return $columns;
    }
    
    /**
     * returns the number of rows for this action
     *
     * @param string action the action being performed
     *
     * @return int   the total number of rows for this action
     *
     * @access public
     *
     */
    function getTotalCount( $action ) {
        return $this->dataExchange->getTotalCount($this->importData['id'], $this->params);
    }   
    
    /**
     * returns all the rows in the given offset and rowCount
     *
     * @param enum   $action   the action being performed
     * @param int    $offset   the row number to start from
     * @param int    $rowCount the number of rows to return
     * @param string $sort     the sql string that describes the sort order
     * @param enum   $type     what should the result set include (web/email/csv)
     *
     * @return int   the total number of rows for this action
     * @access public
     */
    function &getRows( $action, $offset, $rowCount, $sort, $type = null ) {
        $id = $this->importData['id'];
        $params = array_merge($this->params, array(
            'limitOffset' => $offset,
            'limitCount' => $rowCount,
        ));
        $dao = $this->dataExchange->getDataDAO($id, $params);
        
        $msgs = $this->bao->getStatuses();
        
        $rows = array();
        while($dao->fetch()) {
            $row = $dao->toArray();
            $row['search_contact_name'] = $this->bao->getSearchContactName($row);
            
            $status = $row['status'];
            $row['error_status'] = CRM_Finance_BAO_Import_SourceAbstract::isErrorStatus($status);
            $row['status_desc'] = $msgs[$status];
            
            $rows[] = $row;
        }
        
        return $rows;
    }

    /**
     * return the template (.tpl) filename
     *
     * @param string $action the action being performed
     *
     * @return string 
     * @access public
     *
     */
    function getTemplateFileName( $action = null ) {
        throw new Exception("N/I");
    }

    /**
     * return the filename for the exported CSV
     *
     * @param string type   the type of export required: csv/xml/foaf etc
     *
     * @return string the fileName which we will munge to skip spaces and
     *                special characters to avoid various browser issues
     *
     */
    function getExportFileName( $type = 'csv' ) {
        throw new Exception("N/I");
    }
}