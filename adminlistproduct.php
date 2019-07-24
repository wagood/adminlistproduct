<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminListProduct extends Module
{
    private $templateFile;

    public function __construct()
    {
        $this->name = 'adminlistproduct';
        $this->tab = 'others';
        $this->author = 'WAGOOD <wagood@yandex.ru>';
        $this->version = '0.1.0';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Extra Filter to Product List');
        $this->description = $this->l('add new fields to product list');
        $this->ps_versions_compliancy = array('min' => '1.7.3', 'max' => _PS_VERSION_);

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('adminlistproduct'))
            $this->warning = $this->l('No name provided');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayAdminCatalogTwigProductHeader')
            || !$this->registerHook('displayAdminCatalogTwigProductFilter')
            || !$this->registerHook('displayAdminCatalogTwigListingProductFields')
            || !$this->registerHook('actionAdminProductsListingFieldsModifier')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookDisplayAdminCatalogTwigProductHeader($params)
    {
        return $this->display(__FILE__, 'views/templates/hook/displayAdminCatalogTwigProductHeader.tpl');
    }

    public function hookDisplayAdminCatalogTwigProductFilter($params)
    {
        $manufacturers = Manufacturer::getManufacturers();
        $this->context->smarty->assign(
            [
                'filter_column_name_manufacturer' => Tools::getValue('filter_column_name_manufacturer', »),
                'manufacturers' => $manufacturers,
            ]
        );
        return $this->display(__FILE__, 'views/templates/hook/displayAdminCatalogTwigProductFilter.tpl');
    }

    public function hookDisplayAdminCatalogTwigListingProductFields($params)
    {
        $this->context->smarty->assign('product', $params['product']);
        return $this->display(__FILE__, 'views/templates/hook/displayAdminCatalogTwigListingProductFields.tpl');
    }


    public function hookActionAdminProductsListingFieldsModifier($params)
    {

        //Select sql
        $params['sql_select']['manufacturer'] = [
            'table' => 'm',
            'field' => 'name',
            'filtering' => \PrestaShop\PrestaShop\Adapter\Admin\AbstractAdminQueryBuilder::FILTERING_LIKE_BOTH
        ];
        //Table
        $params['sql_table']['m'] = [
            'table' => 'manufacturer',
            'join' => 'LEFT JOIN',
            'on' => 'p.`id_manufacturer` = m.`id_manufacturer`',
        ];

        //Filter
        $manufacturer_filter = Tools::getValue('filter_column_name_manufacturer',false);
        if ( $manufacturer_filter && $manufacturer_filter != '') {
            $params['sql_where'][] .= "p.id_manufacturer =".$manufacturer_filter;
        }
    }
}