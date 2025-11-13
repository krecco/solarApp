<?php

namespace App\Enums;

/**
 * Document Types for Customer Verification and Compliance
 *
 * These document types are used to track required documents for different customer types.
 * Each document type has specific requirements and validation rules.
 */
enum DocumentType: string
{
    // Identity Verification Documents
    case IDENTITY_CARD = 'identity_card';
    case PASSPORT = 'passport';
    case DRIVERS_LICENSE = 'drivers_license';

    // Address Verification Documents
    case PROOF_OF_ADDRESS = 'proof_of_address';
    case UTILITY_BILL = 'utility_bill';
    case BANK_STATEMENT = 'bank_statement';

    // Financial Documents
    case BANK_ACCOUNT_INFO = 'bank_account_info';
    case SEPA_MANDATE = 'sepa_mandate';
    case TAX_ID = 'tax_id';

    // Business Documents (for is_business = true)
    case BUSINESS_REGISTRATION = 'business_registration';
    case VAT_CERTIFICATE = 'vat_certificate';
    case COMPANY_ARTICLES = 'company_articles';
    case AUTHORIZED_SIGNATORY = 'authorized_signatory';

    // Investment-Specific Documents
    case SIGNED_CONTRACT = 'signed_contract';
    case RISK_ACKNOWLEDGMENT = 'risk_acknowledgment';
    case INVESTMENT_AGREEMENT = 'investment_agreement';

    // Plant Owner Documents
    case PROPERTY_OWNERSHIP = 'property_ownership';
    case ROOF_RIGHTS = 'roof_rights';
    case GRID_CONNECTION_PERMIT = 'grid_connection_permit';
    case BUILDING_PERMIT = 'building_permit';

    // General Documents
    case OTHER = 'other';

    /**
     * Get human-readable label for the document type
     */
    public function label(): string
    {
        return match($this) {
            self::IDENTITY_CARD => 'Identity Card',
            self::PASSPORT => 'Passport',
            self::DRIVERS_LICENSE => 'Driver\'s License',
            self::PROOF_OF_ADDRESS => 'Proof of Address',
            self::UTILITY_BILL => 'Utility Bill',
            self::BANK_STATEMENT => 'Bank Statement',
            self::BANK_ACCOUNT_INFO => 'Bank Account Information',
            self::SEPA_MANDATE => 'SEPA Direct Debit Mandate',
            self::TAX_ID => 'Tax Identification',
            self::BUSINESS_REGISTRATION => 'Business Registration',
            self::VAT_CERTIFICATE => 'VAT Certificate',
            self::COMPANY_ARTICLES => 'Articles of Incorporation',
            self::AUTHORIZED_SIGNATORY => 'Authorized Signatory Document',
            self::SIGNED_CONTRACT => 'Signed Contract',
            self::RISK_ACKNOWLEDGMENT => 'Risk Acknowledgment',
            self::INVESTMENT_AGREEMENT => 'Investment Agreement',
            self::PROPERTY_OWNERSHIP => 'Property Ownership Document',
            self::ROOF_RIGHTS => 'Roof Rights Document',
            self::GRID_CONNECTION_PERMIT => 'Grid Connection Permit',
            self::BUILDING_PERMIT => 'Building Permit',
            self::OTHER => 'Other Document',
        };
    }

    /**
     * Get description for the document type
     */
    public function description(): string
    {
        return match($this) {
            self::IDENTITY_CARD => 'Government-issued identity card (front and back)',
            self::PASSPORT => 'Valid passport (photo page)',
            self::DRIVERS_LICENSE => 'Valid driver\'s license (front and back)',
            self::PROOF_OF_ADDRESS => 'Document proving current residential address',
            self::UTILITY_BILL => 'Recent utility bill (electricity, water, gas) - max 3 months old',
            self::BANK_STATEMENT => 'Recent bank statement - max 3 months old',
            self::BANK_ACCOUNT_INFO => 'Bank account details for payments (IBAN)',
            self::SEPA_MANDATE => 'Signed SEPA direct debit authorization',
            self::TAX_ID => 'Tax identification number document',
            self::BUSINESS_REGISTRATION => 'Business registration certificate',
            self::VAT_CERTIFICATE => 'VAT registration certificate',
            self::COMPANY_ARTICLES => 'Company articles of incorporation',
            self::AUTHORIZED_SIGNATORY => 'Document authorizing signatory to act on behalf of company',
            self::SIGNED_CONTRACT => 'Signed investment or service contract',
            self::RISK_ACKNOWLEDGMENT => 'Signed risk disclosure acknowledgment',
            self::INVESTMENT_AGREEMENT => 'Signed investment agreement document',
            self::PROPERTY_OWNERSHIP => 'Document proving property ownership',
            self::ROOF_RIGHTS => 'Document proving rights to install solar panels on roof',
            self::GRID_CONNECTION_PERMIT => 'Permit for grid connection',
            self::BUILDING_PERMIT => 'Building permit for solar installation',
            self::OTHER => 'Other supporting document',
        };
    }

    /**
     * Check if this document type is required for identity verification
     */
    public function isIdentityDocument(): bool
    {
        return in_array($this, [
            self::IDENTITY_CARD,
            self::PASSPORT,
            self::DRIVERS_LICENSE,
        ]);
    }

    /**
     * Check if this document type is for address verification
     */
    public function isAddressDocument(): bool
    {
        return in_array($this, [
            self::PROOF_OF_ADDRESS,
            self::UTILITY_BILL,
            self::BANK_STATEMENT,
        ]);
    }

    /**
     * Check if this document type is for business entities
     */
    public function isBusinessDocument(): bool
    {
        return in_array($this, [
            self::BUSINESS_REGISTRATION,
            self::VAT_CERTIFICATE,
            self::COMPANY_ARTICLES,
            self::AUTHORIZED_SIGNATORY,
        ]);
    }

    /**
     * Check if this document type is for plant owners
     */
    public function isPlantOwnerDocument(): bool
    {
        return in_array($this, [
            self::PROPERTY_OWNERSHIP,
            self::ROOF_RIGHTS,
            self::GRID_CONNECTION_PERMIT,
            self::BUILDING_PERMIT,
        ]);
    }

    /**
     * Get all document types as array of strings
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get required documents for a customer type
     *
     * @param string $customerType 'investor', 'plant_owner', or 'both'
     * @param bool $isBusiness Whether the customer is a business entity
     * @return array<DocumentType>
     */
    public static function getRequiredDocuments(string $customerType, bool $isBusiness = false): array
    {
        $required = [];

        // All customers need identity verification
        $required[] = self::IDENTITY_CARD; // OR passport OR drivers_license (at least one)

        // All customers need address verification
        $required[] = self::PROOF_OF_ADDRESS; // OR utility_bill OR bank_statement (at least one)

        // Investors need financial documents
        if (in_array($customerType, ['investor', 'both'])) {
            $required[] = self::BANK_ACCOUNT_INFO;
            $required[] = self::SEPA_MANDATE;
            $required[] = self::SIGNED_CONTRACT;
            $required[] = self::RISK_ACKNOWLEDGMENT;
        }

        // Plant owners need property documents
        if (in_array($customerType, ['plant_owner', 'both'])) {
            $required[] = self::PROPERTY_OWNERSHIP;
            $required[] = self::ROOF_RIGHTS;
            $required[] = self::GRID_CONNECTION_PERMIT;
        }

        // Business entities need additional documents
        if ($isBusiness) {
            $required[] = self::BUSINESS_REGISTRATION;
            $required[] = self::VAT_CERTIFICATE;
            $required[] = self::AUTHORIZED_SIGNATORY;
        }

        return $required;
    }

    /**
     * Get optional documents for a customer type
     *
     * @param string $customerType 'investor', 'plant_owner', or 'both'
     * @param bool $isBusiness Whether the customer is a business entity
     * @return array<DocumentType>
     */
    public static function getOptionalDocuments(string $customerType, bool $isBusiness = false): array
    {
        $optional = [];

        // Optional identity documents (alternatives)
        $optional[] = self::PASSPORT;
        $optional[] = self::DRIVERS_LICENSE;

        // Optional address documents (alternatives)
        $optional[] = self::UTILITY_BILL;
        $optional[] = self::BANK_STATEMENT;

        if (in_array($customerType, ['investor', 'both'])) {
            $optional[] = self::TAX_ID;
            $optional[] = self::INVESTMENT_AGREEMENT;
        }

        if (in_array($customerType, ['plant_owner', 'both'])) {
            $optional[] = self::BUILDING_PERMIT;
        }

        if ($isBusiness) {
            $optional[] = self::COMPANY_ARTICLES;
        }

        $optional[] = self::OTHER;

        return $optional;
    }
}
