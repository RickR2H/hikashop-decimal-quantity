<?php
/**
 * @HikaShop custom decimal field for Joomla!
 * @version	1.0.0
 * @author	rick@r2h.nl
 * @copyright	(C) 2010-2016 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
jimport('joomla.plugin.plugin');
class plgSystemCustom_pricer2h extends JPlugin{
}

if(!function_exists('hikashop_product_price_for_quantity_in_cart') && !function_exists('hikashop_product_price_for_quantity_in_order')) {
	function hikashop_product_price_for_quantity_in_cart(&$product){
		$currencyClass = hikashop_get('class.currency');
		$quantity = @$product->cart_product_quantity;


		$plugin = JPluginHelper::getPlugin('system', 'custom_pricer2h');
		if(version_compare(JVERSION,'2.5','<')){
			jimport('joomla.html.parameter');
			$params = new JParameter($plugin->params);
		} else {
			$params = new JRegistry($plugin->params);
		}

		$taxes = $params->get('taxes',0);

		$column = $params->get('field','amount');

		// Set quantity to 1 when field is empty
		if(empty($product->$column))
		{
			$product->$column = '1.00';
		}

		// Remove spaces
		$product->$column = str_replace(' ','',$product->$column);

		// Replace , by .
		$product->$column = str_replace(',','.',$product->$column);

		// Extract all numbers and .
		$product->$column = preg_replace('/[^0-9_.\/]/', '', $product->$column);

		// Set to 1 if string is empty
		if (empty($product->$column))
		{
			$product->$column = '1.00';
		}

		// Round off to two decimals
		$product->$column = number_format(round($product->$column,2), 2, '.', '');

		if(!empty($product->$column)){
			if(empty($product->prices)){
				$price= new stdClass();
				$price->price_currency_id = hikashop_getCurrency();
				$price->price_min_quantity = 1;
				$product->prices = array($price);
			}
			foreach($product->prices as $k => $price){
				if($taxes && $product->product_type=='variant' && empty($product->product_tax_id)){
					$productClass = hikashop_get('class.product');
					$main = $productClass->get($product->product_parent_id);
					$product->product_tax_id = $main->product_tax_id;
				}

								//Get the current price * Custom field quantity
				$product_price = $product->prices[$k]->price_value * $product->$column;
				$original_product_price = $product->prices[$k]->price_value_with_tax;

				switch($taxes){
					case 2:
						$product->prices[$k]->price_value = $currencyClass->getUntaxedPrice($product_price,hikashop_getZone(),$product->product_tax_id);
						$product->prices[$k]->taxes=$currencyClass->taxRates;
						$product->prices[$k]->price_value_with_tax = $product_price;
						break;
					case 1:
						$product->prices[$k]->price_value = $product_price;
						$product->prices[$k]->price_value_with_tax = $currencyClass->getTaxedPrice($product_price,hikashop_getZone(),$product->product_tax_id);
						$product->prices[$k]->taxes=$currencyClass->taxRates;
						break;
					case 0:
					default:
						$product->prices[$k]->price_value = $product->$column;
						$product->prices[$k]->price_value_with_tax = $product->$column;
						break;
				}
			}

			$product->$column = str_replace('.',',',$product->$column);
			$product->$column = $product->$column . ' m - &euro; ' . number_format(round($original_product_price, 2), 2, ',', ' ') .' / m1';

			$currencyClass->quantityPrices($product->prices,$quantity,$product->cart_product_total_quantity);
		}
	}
}
