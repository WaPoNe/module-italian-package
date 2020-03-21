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
use Magento\Tax\Model\TaxClass\Source\Customer as CustomerTaxClassSource;
use Magento\Tax\Model\TaxClass\Source\Product as ProductTaxClassSource;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Api\Data\TaxRuleInterfaceFactory;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\Data\TaxRateInterfaceFactory;
use Magento\Tax\Model\Calculation\RateFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Psr\Log\LoggerInterface;

/**
 * Add Tax for Italy.
 */
class AddTaxForItaly implements DataPatchInterface
{
    const TABLE_TAX_CALCULATION_RATE = 'tax_calculation_rate';
    const TAX_RATE_CODE = 'Italy IVA 22% (W1)';
    const TAX_RULE_CODE = 'Tax Rule for Italy (W1)';
    const ID_COLUMN_VALUE = 'value';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerTaxClassSource
     */
    private $customerTaxClassSource;

    /**
     * @var ProductTaxClassSource
     */
    private $productTaxClassSource;

    /**
     * @var TaxRuleRepositoryInterface
     */
    private $taxRuleRepository;

    /**
     * @var TaxRuleInterfaceFactory
     */
    private $ruleFactory;

    /**
     * @var TaxRateRepositoryInterface
     */
    protected $taxRateRepository;

    /**
     * @var TaxRateInterfaceFactory
     */
    protected $rateFactory;

    /**
     * @var RateFactory
     */
    private $taxRateFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerTaxClassSource $customerTaxClassSource
     * @param ProductTaxClassSource $productTaxClassSource
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     * @param TaxRuleInterfaceFactory $ruleFactory
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param TaxRateInterfaceFactory $rateFactory
     * @param RateFactory $taxRateFactory
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerTaxClassSource $customerTaxClassSource,
        ProductTaxClassSource $productTaxClassSource,
        TaxRuleRepositoryInterface $taxRuleRepository,
        TaxRuleInterfaceFactory $ruleFactory,
        TaxRateRepositoryInterface $taxRateRepository,
        TaxRateInterfaceFactory $rateFactory,
        RateFactory $taxRateFactory,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerTaxClassSource = $customerTaxClassSource;
        $this->productTaxClassSource = $productTaxClassSource;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->ruleFactory = $ruleFactory;
        $this->taxRateRepository = $taxRateRepository;
        $this->rateFactory = $rateFactory;
        $this->taxRateFactory = $taxRateFactory;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        try {
            $this->createTaxRatesForItaly();
            $this->createTaxRuleForItaly();
        }
        catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Create Tax Rates for Italy.
     *
     * @throws \Magento\Framework\Exception\InputException
     */
    private function createTaxRatesForItaly()
    {
        $italianTaxCalculationRate = [
            'tax_country_id' => 'IT',
            'tax_region_id' => 0,
            'tax_postcode' => '*',
            'code' => self::TAX_RATE_CODE,
            'rate' => 22.00
        ];
        // creating tax Italian rate
        $taxRate = $this->rateFactory->create();
        $taxRate->setCode($italianTaxCalculationRate['code'])
            ->setTaxCountryId($italianTaxCalculationRate['tax_country_id'])
            ->setTaxRegionId($italianTaxCalculationRate['tax_region_id'])
            ->setTaxPostcode($italianTaxCalculationRate['tax_postcode'])
            ->setRate($italianTaxCalculationRate['rate']);
        // saving tax Italian rate
        $this->taxRateRepository->save($taxRate);
    }

    /**
     * Create Tax Rule for Italy.
     *
     * @return void
     * @throws \Exception if something went wrong while saving the tax rule.
     */
    private function createTaxRuleForItaly()
    {
        $taxRuleForItaly = [
            'code'                  => self::TAX_RULE_CODE,
            'tax_rate'              => '',
            'tax_customer_class'    => $this->getCustomerTaxClassIds(),
            'tax_product_class'     => $this->getProductTaxClassIds(),
            'priority'              => 0,
            'calculate_subtotal'    => '',
            'position'              => 0
        ];

        // checking if the Tax Rule for Italy already exists
        $filter = $this->filterBuilder->setField('code')
            ->setConditionType('=')
            ->setValue($taxRuleForItaly['code'])
            ->create();
        $criteria = $this->criteriaBuilder->addFilters([$filter])->create();
        $existingRates = $this->taxRuleRepository->getList($criteria)->getItems();
        if (!empty($existingRates)) {
            throw new \Exception(self::TAX_RATE_CODE . ' already exists!');
        }

        // loading tax Italian rate
        $taxRate = $this->taxRateFactory->create()->loadByCode(self::TAX_RATE_CODE);
        //creating tax Italian rule
        $taxRule = $this->ruleFactory->create();
        $taxRule->setCode($taxRuleForItaly['code'])
            ->setTaxRateIds([$taxRate->getId()])
            ->setCustomerTaxClassIds($taxRuleForItaly['tax_customer_class'])
            ->setProductTaxClassIds($taxRuleForItaly['tax_product_class'])
            ->setPriority($taxRuleForItaly['priority'])
            ->setCalculateSubtotal($taxRuleForItaly['calculate_subtotal'])
            ->setPosition($taxRuleForItaly['position']);
        // saving tax Italian rule
        $this->taxRuleRepository->save($taxRule);
    }

    /**
     * Get all customer tax class ids
     *
     * @return array
     * @throws \Magento\Framework\Exception\StateException
     */
    private function getCustomerTaxClassIds()
    {
        return array_column($this->customerTaxClassSource->getAllOptions(), self::ID_COLUMN_VALUE);
    }

    /**
     * Get all product tax class ids
     *
     * @return array
     */
    private function getProductTaxClassIds()
    {
        $productTaxClassIds = array_column(
            $this->productTaxClassSource->getAllOptions(),
            self::ID_COLUMN_VALUE
        );
        // $this->productTaxClassSource->getAllOptions() gets also 0 value; Magento core bug???
        if (($key = array_search(0, $productTaxClassIds)) !== false) {
            unset($productTaxClassIds[$key]);
        }
        return $productTaxClassIds;
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
