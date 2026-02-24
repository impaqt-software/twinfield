<?php

namespace PhpTwinfield\ApiConnectors;

use PhpTwinfield\Mappers\DocumentMapper;
use stdClass;

class DeclarationsApiConnector extends BaseApiConnector
{
    public function setOffice($officeCode): void
    {
        $headers = $this->getConnection()->getSoapHeaders();

        $this->getDeclarationService()->setSoapHeadersFromArray([
            ...$headers->data,
            'CompanyCode' => $officeCode,
        ]);
    }

    public function declarationCount($officeCode): int
    {
        $response = $this->getDeclarationService()->declarationCount($officeCode);

        return $response->numberOfVatReturns ?? 0;
    }

    public function vatReturnXbrl($documentId): string|null
    {
        $response = $this->getDeclarationService()->vatReturnXbrl($documentId);

        $vatReturn = $this->getVatReturnResponse($response);

        return $vatReturn?->any ?? null;
    }

    public function icpReturnXBrl($documentId): string|null
    {
        $response = $this->getDeclarationService()->icpReturnXbrl($documentId);

        $vatReturn = $this->getVatReturnResponse($response);

        return $vatReturn?->any ?? null;
    }

    public function summaries($officeCode, $declarationYear = null): array
    {
        // this seems redundant, but it is necessary to set the office code
        $this->setOffice($officeCode);

        $response = $this->getDeclarationService()->summaries($officeCode, $declarationYear);

        return $this->processDeclarations($response);
    }

    public function getDeclarationsSinceModified($officeCode, \DateTime $modifiedSince, int $skip = 0, int $take = 50): array
    {
        // this seems redundant, but it is necessary to set the office code
        $this->setOffice($officeCode);

        $response = $this->getDeclarationService()->getDeclarationsSinceModified($officeCode, $modifiedSince, $skip, $take);

        return $this->processDeclarations($response);
    }

    public function getDeclarationsByYearAndSinceModified($officeCode, \DateTime $modifiedSince, $declarationYear = null, int $skip = 0, int $take = 50): array
    {
        // this seems redundant, but it is necessary to set the office code
        $this->setOffice($officeCode);

        $response = $this->getDeclarationService()->getDeclarationsByYearAndSinceModified($officeCode, $modifiedSince, $declarationYear, $skip, $take);

        return $this->processDeclarations($response);
    }

    private function processDeclarations(stdClass $response): array
    {
        $vatReturn = $this->getVatReturnResponse($response);

        if (!is_object($vatReturn) || !isset($vatReturn->DeclarationSummary)) {
            return [];
        }

        $declarations = $vatReturn->DeclarationSummary;

        // If there are multiple declarations, map them all
        if (is_array($declarations)) {
            return array_map(function ($declaration) {
                return DocumentMapper::map($declaration);
            }, $declarations);
        }

        // With XML, if there is only one declaration, it is not an array
        return [DocumentMapper::map($declarations)];
    }

    private function getVatReturnResponse(stdClass $response): ?stdClass
    {
        $vatReturn = $response->vatReturn ?? $response->VatReturn ?? null;

        return is_object($vatReturn) ? $vatReturn : null;
    }

    public function setApproved($officeCode, $documentId): bool
    {
        $this->setOffice($officeCode);

        $result = $this->getDeclarationService()->setApproved($documentId);

        var_dump($result);

        // @todo implement proper error / success handling

        return false;
    }

    public function setRejected($officeCode, $documentId, $reason): bool
    {
        $this->setOffice($officeCode);

        $result = $this->getDeclarationService()->setRejected($documentId, $reason);

        var_dump($result);

        // @todo implement proper error / success handling

        return false;
    }

    public function setSent($officeCode, $documentId): bool
    {
        $this->setOffice($officeCode);

        $result = $this->getDeclarationService()->setSent($documentId);

        var_dump($result);

        // @todo implement proper error / success handling

        return false;
    }
}
