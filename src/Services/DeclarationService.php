<?php

namespace PhpTwinfield\Services;

use PhpTwinfield\Response\Response;

class DeclarationService extends BaseService
{

    /*
     * https://accounting.twinfield.com/webservices/documentation/#/ApiReference/Miscellaneous/Declaration
     * https://api.accounting2.twinfield.com/webservices/declarations.asmx
     */


    /*
     * This function keeps the auth method (openID or sessionId) intact but overrides the office code
     */
    public function setSoapHeadersFromArray($headerData)
    {
        // Define the namespace for the Header element
        $namespace = 'http://www.twinfield.com/';

        $innerHeaders = [];

        foreach ($headerData as $key => $value) {
            $innerHeaders[] = new \SoapVar(
                new \SoapVar($value, XSD_STRING, null, null, $key, $namespace),
                SOAP_ENC_OBJECT
            );
        }
        // Wrap the inner headers in the Header element
        $headerContainer = new \SoapVar(
            new \SoapVar($innerHeaders, SOAP_ENC_OBJECT), SOAP_ENC_OBJECT, null,
            null,
            'Header',
            $namespace
        );

        $soapHeader = new \SoapHeader($namespace, 'Header', $headerContainer);

        $this->__setSoapHeaders([$soapHeader]);
    }

    protected function WSDL(): string
    {
        return '/webservices/declarations.asmx?wsdl';
    }

    public function declarationCount($officeCode): \stdClass
    {
        // note $this->{function_name} is the SOAP function that should exist in the WSDL

        return $this->GetNumberOfDeclarations([
            'companyCode' => $officeCode,
        ]);
    }

    /**
     * Return als the declarations for a given year, no filtering possible
     * @param $officeCode
     * @param $declarationYear
     * @return \stdClass
     */
    public function summaries($officeCode, $declarationYear = null): \stdClass
    {
        return $this->GetAllSummaries([
            'companyCode' => $officeCode,
            'declarationYear' => $declarationYear,
        ]);
    }

    public function getDeclarationsByYearAndSinceModified($officeCode, \DateTime $modifiedSince, $declarationYear = null, int $skip = 0, int $take = 50): \stdClass
    {
        return $this->GetRangeOfSummariesByDeclarationYearAndSinceModified([
            'companyCode' => $officeCode,
            'declarationYear' => $declarationYear,
            'startIndex' => $skip,
            'count' => $take,
            'modifiedSince' => $modifiedSince->format('Y-m-d\TH:i:s'),
        ]);
    }

    public function getDeclarationsSinceModified($officeCode, \DateTime $modifiedSince, int $skip = 0, int $take = 50): \stdClass
    {
        return $this->GetRangeOfSummariesSinceModified([
            'companyCode' => $officeCode,
            'startIndex' => $skip,
            'count' => $take,
            'modifiedSince' => $modifiedSince->format('Y-m-d\TH:i:s'),
        ]);
    }

    public function vatReturnXbrl($documentId)
    {
        // note $this->{function_name} is the SOAP function that should exist in the WSDL

        return $this->GetVatReturnAsXbrl([
            'documentId' => $documentId,
            'isMessageIdRequired' => true // If set to True the MessageReferenceSupplierVAT element will be filled in. Which is required for sending the item to logius
        ]);
    }

    public function icpReturnXbrl($documentId)
    {
        // note $this->{function_name} is the SOAP function that should exist in the WSDL

        return $this->GetIctReturnAsXbrl([ // is this a typo? should it be icpReturnXbrl?
            'documentId' => $documentId,
            'isMessageIdRequired' => true // If set to True the MessageReferenceSupplierVAT element will be filled in. Which is required for sending the item to logius
        ]);
    }

    public function setApproved($documentId)
    {
        // note $this->{function_name} is the SOAP function that should exist in the WSDL

        return $this->SetToApproved([
            'documentId' => $documentId
        ]);
    }

    public function setRejected($documentId, $reason)
    {
        // note $this->{function_name} is the SOAP function that should exist in the WSDL

        return $this->SetToSent([
            'DocumentId' => $documentId,
            'Reason' => $reason
        ]);
    }

    public function setSent($documentId)
    {
        // note $this->{function_name} is the SOAP function that should exist in the WSDL

        return $this->SetToSent([
            'documentId' => $documentId
        ]);
    }
}
