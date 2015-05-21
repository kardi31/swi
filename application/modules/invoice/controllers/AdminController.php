<?php

/**
 * Invoice_AdminController
 *
 * @author Michał Folga <michalfolga@gmail.com>
 */
class Invoice_AdminController extends MF_Controller_Action {
    
    public function listInvoiceAction() {
        if($dashboardTime = $this->_helper->user->get('dashboard_time')) {
            if(isset($dashboardTime['new_invoices'])) {
                $dashboardTime['new_invoices'] = time();
                $this->_helper->user->set('dashboard_time', $dashboardTime);
            }
        }
    }
    
    public function listInvoiceDataAction() {
        $table = Doctrine_Core::getTable('Invoice_Model_Doctrine_Invoice');
        $dataTables = Default_DataTables_Factory::factory(array(
            'request' => $this->getRequest(), 
            'table' => $table, 
            'class' => 'Invoice_DataTables_Invoice', 
            'columns' => array('CONCAT_WS(" ", u.first_name, u.last_name)', 'x.name', 'x.created_at', 'p.paid_at', 'p.status'),
            'searchFields' => array('CONCAT_WS(" ", u.first_name, u.last_name)')
        ));
        
        $results = $dataTables->getResult();
        
        $rows = array();
        foreach($results as $result) {
            $row = array();
            $row['DT_RowId'] = $result['id'];
            if(MF_Code::STATUS_NEW == $result['Payment']['status']) {
                $row['DT_RowClass'] = 'success';
            } elseif(MF_Code::STATUS_REJECTED == $result['Payment']['status']) {
                $row['DT_RowClass'] = 'inactive';
            }
            $row[] = $result['User']['first_name'] . ' ' . $result['User']['last_name'];
            $row[] = isset($result['name']) ? $result['name'] : '';
            $row[] = MF_Text::timeFormat($result['created_at'], 'H:i d/m/Y');
            $row[] = strlen($result['Payment']['paid_at']) ? MF_Text::timeFormat($result['Payment']['paid_at'], 'H:i d/m/Y') : '';
            $row[] = $result['Payment']['status'];
            $options = '<a href="' . $this->view->adminUrl('show-invoice', 'invoice', array('id' => $result['id'])) . '" title="' . $this->view->translate('Edit') . '"><span class="icon16 icomoon-icon-license"></span></a>';
            $row[] = $options;
            $rows[] = $row;
        }

        $response = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $dataTables->getDisplayTotal(),
            "iTotalDisplayRecords" => $dataTables->getTotal(),
            "aaData" => $rows
        );

        $this->_helper->json($response);
    }
    
    public function showInvoiceAction() {
        $invoiceService = $this->_service->getService('Invoice_Service_Invoice');
        
        if(!$invoice = $invoiceService->getFullInvoice((int) $this->getRequest()->getParam('id'))) {
            throw new Zend_Controller_Action_Exception('Invoice not found');
        }
        
        $paymentTypes = Invoice_Model_Doctrine_Payment::$paymentTypes;
        $this->view->assign('paymentTypes', $paymentTypes);
        
        $this->view->assign('invoice', $invoice);
    }
}

