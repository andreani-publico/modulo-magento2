<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ids\Andreani\Cron;

use Ids\Andreani\Helper\Data as AndreaniHelper;
use Symfony\Component\Config\Definition\Exception\Exception;

class BorrarPdf
{
    protected $_logger;

    /**
     * @var AndreaniHelper
     */
    protected $_andreaniHelper;

    /**
     * BorrarPdf constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param AndreaniHelper $andreaniHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        AndreaniHelper $andreaniHelper
    ) {
        $this->_logger          = $logger;
        $this->_andreaniHelper  = $andreaniHelper;
    }

    
    /**
     * Método que se ejecuta cuando corre el cron.
     */
    public function execute() {
        
        $path = $this->_andreaniHelper->getDirectoryPath('media')."/andreani/*";
        
        try{
            array_map('unlink', glob("$path.pdf"));
            $this->_logger->debug('Guías borradas correctamente');
            
        }catch (Exception $e)
        {
            $this->_logger->debug('Hubo un error al intentar borrar las guías '.$e);
        }
    
        return $this;
    }
}