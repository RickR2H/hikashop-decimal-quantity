<?php
/**
 * @package	R2H Decimal quantity plugin for Hikashop 3.x!
 * @version	2.0.0
 * @author	r2h.nl
 * @copyright	(C) 2010-2017 R2H B.V. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgSystemCustom_pricer2h extends JPlugin {
	protected $currencyClass = null;
	public $params = null;

	public function onBeforeCalculateProductPriceForQuantity(&$product)
	{
		if(empty($this->currencyClass))
		{
			$this->currencyClass = hikashop_get('class.currency');
		}

		// Quantity field value
		$quantity = @$product->cart_product_quantity;

		if(empty($this->params)) {
			$plugin = JPluginHelper::getPlugin('system', 'custom_price');
			if(version_compare(JVERSION,'2.5','<')){
				jimport('joomla.html.parameter');
				$this->params = new JParameter($plugin->params);
			} else {
				$this->params = new JRegistry($plugin->params);
			}
		}

		$taxes = $this->params->get('taxes',0);
		$column = $this->params->get('field','amount');
		if(empty($product->$column))
			return;

		// Debug custom field value
		//echo $product->$column;

		// Set field value to 1 when empty
		if (empty($product->$column))
		{
			$product->$column = '1.00';
		}

		// Remove spaces from the value
		$product->$column = str_replace(' ','',$product->$column);

		// Replace , by . in the value for calculation purposes
		$product->$column = str_replace(',','.',$product->$column);

		// Extract all numbers and . in the value
		$product->$column = preg_replace('/[^0-9_.\/]/', '', $product->$column);

		if(empty($product->prices))
		{
			$price= new stdClass();
			$price->price_currency_id = hikashop_getCurrency();
			$price->price_min_quantity = 1;
			$product->prices = array($price);
		}

		foreach($product->prices as $k => $price)
		{
			if($taxes && $product->product_type == 'variant' && empty($product->product_tax_id))
			{
				$productClass = hikashop_get('class.product');
				$main = $productClass->get($product->product_parent_id);
				$product->product_tax_id = $main->product_tax_id;
			}

			// Set the total product price including tax
			$r2h_new_price = $product->$column * $product->prices[$k]->price_value_with_tax;
			$r2h_prod_with_tax = $product->prices[$k]->price_value_with_tax;

			switch($taxes)
			{
				case 2:
					$product->prices[$k]->price_value          = $this->currencyClass->getUntaxedPrice($r2h_new_price, hikashop_getZone(), $product->product_tax_id);
					$product->prices[$k]->taxes                = $this->currencyClass->taxRates;
					$product->prices[$k]->price_value_with_tax = hikashop_toFloat($r2h_new_price);
					break;
				case 1:
					$product->prices[$k]->price_value          = hikashop_toFloat($r2h_new_price);
					$product->prices[$k]->price_value_with_tax = $this->currencyClass->getTaxedPrice($r2h_new_price, hikashop_getZone(), $product->product_tax_id);
					$product->prices[$k]->taxes                = $this->currencyClass->taxRates;
					break;
				case 0:
				default:
					$product->prices[$k]->price_value          = hikashop_toFloat($r2h_new_price);
					$product->prices[$k]->price_value_with_tax = hikashop_toFloat($r2h_new_price);
					break;
			}
		}

	}

	public function onAfterCartProductsLoad(&$cart)
	{
		//Debug info
		//echo '<pre>';
		//print_r($cart);
		//print_r($cart->products);
		//echo '</pre>';

		foreach ($cart->products as $product)
		{
			if (!empty($product->meter))
			{
				foreach ($product->prices as $price)
				{
					$totalunits =  $product->meter . ' ' . $product->product_dimension_unit;
					$unitprice = round($price->price_value_with_tax / $product->meter / $product->cart_product_total_quantity, 2);
					$unitprice = number_format($unitprice, 2, ',', '.') . ' /' . $product->product_dimension_unit;
					$cart->cart_products[$product->cart_product_id]->meter = $totalunits . ' - ' . $unitprice;
				}

			}
		}

	}
}
