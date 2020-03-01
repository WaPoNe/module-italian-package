<?php
/**
 * WaPoNe
 *
 * @category   WaPoNe
 * @package    WaPoNe_ItalianPackage
 * @author     Michele Fantetti
 * @copyright  Copyright (c) 2020 WaPoNe (https://www.fantetti.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace WaPoNe\ItalianPackage\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add Config Values for Italy.
 */
class ConfigValuesForItaly implements DataPatchInterface
{
    const TABLE_CORE_CONFIG_DATA = 'core_config_data';
    const PATH_GENERAL_COUNTY_DEFAULT = 'general/country/default';
    const PATH_GENERAL_STOREINFORMATION_COUNTRYID = 'general/store_information/country_id';
    const PATH_SHIPPING_ORIGIN_COUNTRYID = 'shipping/origin/country_id';
    const PATH_TAX_DEFAULTS_COUNTRY = 'tax/defaults/country';


    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        // General Country Default
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable(self::TABLE_CORE_CONFIG_DATA),
            $this->getValueIT(),
            $this->getGeneralCountryDefault()
        );
        // General StoreInformation CountryId
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable(self::TABLE_CORE_CONFIG_DATA),
            $this->getValueIT(),
            $this->getGeneralStoreInformationCountryId()
        );
        // Shipping Origin CountryId
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable(self::TABLE_CORE_CONFIG_DATA),
            $this->getValueIT(),
            $this->getShippingOriginCountryId()
        );
        // Tax Defaults Country
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable(self::TABLE_CORE_CONFIG_DATA),
            $this->getValueIT(),
            $this->getTaxDefaultsCountry()
        );
    }

    /**
     * General Country Default
     *
     * @return array
     */
    private function getValueIT()
    {
        return [
            'value' => 'IT'
        ];
    }

    /**
     * General Country Default
     *
     * @return array
     */
    private function getGeneralCountryDefault()
    {
        return [
            'scope="default"',
            'scope_id=0',
            'path=?' => self::PATH_GENERAL_COUNTY_DEFAULT
        ];
    }

    /**
     * General StoreInformation CountryId
     *
     * @return array
     */
    private function getGeneralStoreInformationCountryId()
    {
        return [
            'scope="default"',
            'scope_id=0',
            'path=?' => self::PATH_GENERAL_STOREINFORMATION_COUNTRYID
        ];
    }

    /**
     * Shipping Origin CountryId
     *
     * @return array
     */
    private function getShippingOriginCountryId()
    {
        return [
            'scope="default"',
            'scope_id=0',
            'path=?' => self::PATH_SHIPPING_ORIGIN_COUNTRYID
        ];
    }

    /**
     * Tax Defaults Country
     *
     * @return array
     */
    private function getTaxDefaultsCountry()
    {
        return [
            'scope="default"',
            'scope_id=0',
            'path=?' => self::PATH_TAX_DEFAULTS_COUNTRY
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
