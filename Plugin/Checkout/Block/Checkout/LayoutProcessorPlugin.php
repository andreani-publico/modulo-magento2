<?php

namespace Ids\Andreani\Plugin\Checkout\Block\Checkout;

class LayoutProcessorPlugin
{

    protected $logger;

    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface
    ) {
        $this->logger = $loggerInterface;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param $result
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, $result)
    {
        $paymentForms = $result['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children'];

        foreach ($paymentForms as $_paymentForm => $value)
        {
            if(isset($value['children']['form-fields']['children']['postcode']['config']['customScope']))
            {
                $scope = $value['children']['form-fields']['children']['postcode']['config']['customScope'];

                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
                ['children']['payments-list']['children'][$_paymentForm]['children']['form-fields']['children']['celular'] = [
                    'component'=>'Magento_Ui/js/form/element/abstract',
                    'dataScope' => $scope.'.celular',
                    'config' => [
                        'customScope' => $scope,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                        'tooltip'=>
                            [
                                'description' => __('Ingrese sólo numeros.')
                            ]
                    ],
                    'provider'=>'checkoutProvider',
                    'validation' => [
                        'min_text_length' => 7,
                        'max_text_length' => 20,
                        'validate-number' => true,
                        'validate-digits' => true
                    ],
                    'sortOrder' => '200',
                    'label'=> __('Celular')
                ];

                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
                ['children']['payments-list']['children'][$_paymentForm]['children']['form-fields']['children']['altura'] = [
                    'component'=>'Magento_Ui/js/form/element/abstract',
                    'dataScope' => $scope.'.altura',
                    'config' => [
                        'customScope' => $scope,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'provider'=>'checkoutProvider',
                    'validation' => [
                        'required-entry' => true,
                        'validate-number' => true
                    ],
                    'sortOrder' => '71',
                    'label'=> __('Altura')
                ];

                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
                ['children']['payments-list']['children'][$_paymentForm]['children']['form-fields']['children']['piso'] = [
                    'component'=>'Magento_Ui/js/form/element/abstract',
                    'dataScope' => $scope.'.piso',
                    'config' => [
                        'customScope' => $scope,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'provider'=>'checkoutProvider',
                    'validation' => [
                        'validate-digits' => true,
                        'max_text_length' => 2,
                        'validate-number' => true
                    ],
                    'sortOrder' => '72',
                    'label'=> __('Piso')
                ];

                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
                ['children']['payments-list']['children'][$_paymentForm]['children']['form-fields']['children']['departamento'] = [
                    'component'=>'Magento_Ui/js/form/element/abstract',
                    'dataScope' => $scope.'.departamento',
                    'config' => [
                        'customScope' => $scope,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'provider'=>'checkoutProvider',
                    'sortOrder' => '73',
                    'label'=> __('Departamento')
                ];

                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
                ['children']['payments-list']['children'][$_paymentForm]['children']['form-fields']['children']['dni'] = [
                    'component'=>'Magento_Ui/js/form/element/abstract',
                    'dataScope' => $scope.'.dni',
                    'config' => [
                        'customScope' => $scope,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                        'tooltip'=>
                            [
                                'description' => __('Ingrese sólo numeros.')
                            ]
                    ],
                    'provider'=>'checkoutProvider',
                    'sortOrder' => '52',
                    'label'=> __('Dni'),
                    'validation' => [
                        'validate-digits' => true,
                        'max_text_length' => 8,
                        'min_text_length' => 6,
                        'validate-number' => true,
                        'required-entry'  => true
                    ],
                ];

                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
                ['children']['payments-list']['children'][$_paymentForm]['children']['form-fields']['children']['observaciones'] = [
                    'component'=>'Magento_Ui/js/form/element/abstract',
                    'dataScope' => $scope.'.observaciones',
                    'config' => [
                        'customScope' => $scope,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/textarea',
                        'cols' => '15',
                        'rows' => '3'
                    ],
                    'provider'=>'checkoutProvider',
                    'sortOrder' => '250',
                    'label'=> __('Observaciones'),
                    'validation' => [
                        'max_text_length' => 300
                    ],
                ];

                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
                ['children']['payments-list']['children'][$_paymentForm]['children']['form-fields']['children']['telephone'] = [
                    'component'=>'Magento_Ui/js/form/element/abstract',
                    'dataScope' => $scope.'.telephone',
                    'config' => [
                        'customScope' => $scope,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                        'tooltip'=>
                            [
                                'description' => __('Ingrese sólo numeros.')
                            ]
                    ],
                    'provider'=>'checkoutProvider',
                    'label'=> __('Phone Number'),
                    'sortOrder' => '120',
                    'validation' => [
                        'min_text_length' => 7,
                        'max_text_length' => 20,
                        'validate-number' => true,
                        'validate-digits' => true,
                        'required-entry'  => true
                    ]
                ];
            }
        }

       return $result;
    }
}