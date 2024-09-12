<?php
namespace FedEx\ShipService\ComplexType;

use FedEx\AbstractComplexType;

/**
 * Data required to complete the Destionation Control Statement for US exports.
 *
 * @author      Jeremy Dunn <jeremy@jsdunn.info>
 * @package     PHP FedEx API wrapper
 * @subpackage  Ship Service
 *
 * @property \FedEx\ShipService\SimpleType\DestinationControlStatementType|string[] $StatementTypes
 * @property string $DestinationCountries
 * @property string $EndUser

 */
class DestinationControlDetail extends AbstractComplexType
{
    /**
     * Name of this complex type
     *
     * @var string
     */
    protected $name = 'DestinationControlDetail';

    /**
     * List of applicable Statment types.
     *
     * @param \FedEx\ShipService\SimpleType\DestinationControlStatementType[]|string[] $statementTypes
     * @return $this
     */
    public function setStatementTypes(array $statementTypes)
    {
        $this->values['StatementTypes'] = $statementTypes;
        return $this;
    }

    /**
     * Comma-separated list of up to four country codes, required for DEPARTMENT_OF_STATE statement.
     *
     * @param string $destinationCountries
     * @return $this
     */
    public function setDestinationCountries($destinationCountries)
    {
        $this->values['DestinationCountries'] = $destinationCountries;
        return $this;
    }

    /**
     * Name of end user, required for DEPARTMENT_OF_STATE statement.
     *
     * @param string $endUser
     * @return $this
     */
    public function setEndUser($endUser)
    {
        $this->values['EndUser'] = $endUser;
        return $this;
    }
}