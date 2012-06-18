<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 */

// @see Validations
require_once 'validations.php';

/** Implementación de Modelo
 * 
 * @category   Kumbia
 * @package    ActiveRecord
 * @copyright  Copyright (c) 2005-2010 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
abstract class KumbiaModel
{

    /**
     * 
     * @var Validations
     */
    protected $_validator = NULL;

    /**
     * Validadores
     * 
     * @return Validations
     */
    public function validations()
    {
        if (!$this->_validator instanceof Validations) {
            $this->_validator = new Validations();
        }
        return $this->_validator;
    }

    public function isValid(){

        if ( $this instanceof ActiveRecord2 ){
            $this->_initValidator();
        }

        // @see KumbiaModelValidator
        require_once 'kumbia_model_validator.php';

        // Ejecuta la validacion
        return KumbiaModelValidator::validateOnCreate($this) !== FALSE;
    }
}
