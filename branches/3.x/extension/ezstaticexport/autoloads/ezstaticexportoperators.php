<?php
class eZStaticExportOperators
{
    /*!
        Constructor
    */
    function eZStaticExportOperators()
    {
        $this->Operators = array( 'ezstaticexport_tokenispresent' );
    }

    /*!
        Returns the operators in this class.
    */
    function &operatorList()
    {
        return $this->Operators;
    }

    /*!
        return true to tell the template engine that the parameter list
        exists per operator type, this is needed for operator classes
        that have multiple operators.
    */
    function namedParameterPerOperator()
    {
        return true;
    }

    /*!
        The first operator has two parameters, the other has none.
        See eZTemplateOperator::namedParameterList()
    */
    function namedParameterList()
    {
        return array( 'ezstaticexport_tokenispresent' => array() );
    }

    /*!
        Executes the needed operator(s).
        Checks operator names, and calls the appropriate functions.
    */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace,&$currentNamespace, &$operatorValue, &$namedParameters )
    {
        switch ( $operatorName )
        {
            case 'ezstaticexport_tokenispresent':
            {
                $operatorValue = $this->eZStaticExportTokenIsPresent();
            } break;
        }

    }

    /*!
     returns true or false wether the token is present or not
     */
    function eZStaticExportTokenIsPresent()
    {
        include_once( 'extension/ezstaticexport/classes/ezstaticexportexport.php' );
        include_once( 'extension/ezstaticexport/classes/ezstaticexporttoken.php' );
        return eZStaticExportToken::exists();
    }

   /// privatesection

   var $Operators;

}

?>
