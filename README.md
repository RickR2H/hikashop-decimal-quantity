# Hikashop decimal quantity field
Use this plugin to show an extra field which can be used to enter a decimal value as quantity for a product.

## How to use the plugin
* Install and activate this plugin
* To sell something by the decimal quantity set "Taxes handling" to "already included in the amount"
* Set the "Column name of the field" to a value representing the decimal quantity (length, milliliters etc.). Default is "length"
* Go to Hikashop -> Display -> Custom Fields and click New
* Set the "Label" to "Length (m1)", "Table" to "item", "Column name" to "length", "Field type" to "text" "Default value" to "1,00"
* Save the plugin and then assign in the field settings the categories on which the plugin should be active
* Go to the Hikashop configuration or assigned menu item, and set the "Quantity input method" to none
* Now only the custom field is visible.
* A "," or "." can be used as digital separator when adding text to the field

NOTE: The plugin is based on the Hikashop custom_price plugin. That's why there is some hard coded stuff in the plugin, but this can easily be removed or changed.

If you have any problems, please let me know! This plugin is tested on Hikashop 3.0.1
