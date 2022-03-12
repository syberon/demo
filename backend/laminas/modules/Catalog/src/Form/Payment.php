<?php
/**
 * Copyright (c) 2018.
 *
 * @author Syber
 */

namespace MtModules\Catalog\Form;

use MtLib\Form\Form\AbstractForm;
use Laminas\Form\Element\Hidden;

class Payment extends AbstractForm
{

    public function __construct()
    {
        parent::__construct();
        $this->setAttributes([
            'method' => 'get',
            'id'     => 'payment-form',
            'ref'    => 'paymentForm'
        ]);

        $this->add(
            [
                'name' => 'MerchantLogin',
                'type' => Hidden::class
            ]
        );

        $this->add(
            [
                'name' => 'OutSum',
                'type' => Hidden::class
            ]
        );

        $this->add(
            [
                'name' => 'InvId',
                'type' => Hidden::class
            ]
        );

        $this->add(
            [
                'name' => 'InvDesc',
                'type' => Hidden::class
            ]
        );

        $this->add(
            [
                'name' => 'SignatureValue',
                'type' => Hidden::class
            ]
        );
        $this->add(
            [
                'name' => 'IsTest',
                'type' => Hidden::class
            ]
        );
    }
}