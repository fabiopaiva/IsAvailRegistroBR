<?php

/**
 * IsAvail
 * @file IsAvail.php
 * @date 15/12/2014
 * @author Fábio Paiva <paiva.fabiofelipe@gmail.com>
 * @project registro
 */

namespace IsAvailRegistroBR\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class IsAvail extends Form {

    public function __construct($name = null, $options = array()) {
        parent::__construct($name, $options);
        $this
                ->setAttribute('class', 'form-inline')
                ->setAttribute('role', 'form')
                ->add(array(
                    'name' => 'dominio',
                    'type' => 'text',
                    'options' => array(
                        'label' => 'Domínio'
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm',
                        'size' => '50'
                    )
                ))
                ->add(array(
                    'name' => 'enviar',
                    'type' => 'button',
                    'options' => array(
                        'label_options' => array(
                            'disable_html_escape' => true,
                        ),
                        'label' => '<span class="glyphicon glyphicon-search"></span> Verificar disponibilidade'
                    ),
                    'attributes' => array(
                        'type' => 'submit',
                        'class' => 'btn btn-sm btn-primary',
                    )
                ))
        ;

        $filter = new InputFilter();
        $filter->add(array(
            'name' => 'dominio',
            'required' => true
        ));

        $this->setInputFilter($filter);
    }

}
