<?php

/**
 * IsAvailController
 * @file IsAvailController.php
 * @date 15/12/2014
 * @author Fábio Paiva <paiva.fabiofelipe@gmail.com>
 * @project registro
 */
namespace IsAvailRegistroBR\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use IsAvailRegistroBR\Form\IsAvail as Form;

class IsAvailController extends AbstractActionController {
    
    public function indexAction() {
        $form = new Form('isAval');
        $result = null;
        if($this->getRequest()->isPost()){
            $form->setData($this->params()->fromPost());
            if($form->isValid()){
                $result = $this->checkAvail($this->params()->fromPost('dominio'));
            }
        }
        
        return array(
            'form' => $form,
            'result' => $result
        );
    }
}
