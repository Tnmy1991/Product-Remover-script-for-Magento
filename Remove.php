<?php 
	/* 
	 * This php script using for removing images from all disabled products inside the 
	 * current Magento store. After image removal has been done this script will delete 
	 * the particular disabled product from the Magento store.
	 *
	 * Author: Tonmoy Malik, Software Developer Trainee
	 *	
	 * NOTE: Before run this script ensure that script is unlocked. Please, using a break 
	 *       limit counter otherwise the script may break  *and creating an error. Here,  
	 *       this script having an break limit at 500, i.e. on each run it could delete  
	 *       500 disabled products from your Magento Store.
	 *
	 * Instructed by: Author
	 */
?>
<?php
	ini_set('max_execution_time', 0);
	echo $_SERVER['REMOTE_ADDR'];
	echo '</br>';
	if($_SERVER['REMOTE_ADDR']=="xxx.xxx.xxx.xxx") { //Enter your IP for scurity reason
		require_once 'app/Mage.php';
		Mage::app('admin');
		//Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		/**** LOCK *****/
		die; 
		/***************/
		
		$products = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED)); //Filter out the disabled products
		
		if(count($products) == 0) {
			Mage::log("No disable products found. Process Aborted..", null, 'RemoveDisableProducts.log'); //Log entry 
			echo 'No disable products found. Process Aborted..';
		}
		
		//$mediaApi = Mage::getModel("catalog/product_attribute_media_api");
		
		$counter = 0;
		
		foreach($products as $product) {
			$counter++;
				$model = Mage::getModel('catalog/product'); //getting product model
				$prodID = $product->getId(); //product id
				$_product = $model->load($prodID);
				$name = $_product->getName(); //product name
				$sku = $_product->getSku(); // product sku
				
				echo 'Process Initiated...</br>';
				echo $counter . '</br>';
				echo 'Product Name: ' . $name . '</br>';
				echo 'Product Id: ' . $prodID . '</br>';
				Mage::log("Process Initiated...", null, 'RemoveDisableProducts.log'); //Log entry
				Mage::log($counter, null, 'RemoveDisableProducts.log'); //Log entry
				Mage::log('Product Name: ' . $name, null, 'RemoveDisableProducts.log'); //Log entry
				Mage::log('Product Id: ' . $prodID, null, 'RemoveDisableProducts.log'); //Log entry
				
				$path = Mage::getModel('catalog/product')->load($product->getId())->getMediaGalleryImages(); //Getting gallery images
				try {
					foreach($path as $_image){ 
						$ImageLink = $_image->getPath(); //Getting image path
						unlink($ImageLink); //Deleting image from server
						Mage::log($ImageLink . ' - Image deleted form Server.', null, 'RemoveDisableProducts.log'); //Log entry
					}
					echo 'Images are deleted from server for the current products.</br>';
				} catch(Exception $e) {
					Mage::log('Error: Unable to delete file ' . $ImageLink, null, 'RemoveDisableProducts.log'); //Error log
					echo 'Error: Unable to delete file ' . $ImageLink . '</br>';
				}
				
				$targetpath= Mage::getBaseDir('var').DS.'tmp'.DS;
				$file = $targetpath . 'Removed-products-list.txt';
				if (!file_exists($file)) {
					fopen($targetpath . "Removed-products-list.txt", "w"); //Creating new file 
					echo "The file Removed-products-list.txt created.</br>";
					Mage::log("The file Removed-products-list.txt created.", null, 'RemoveDisableProducts.log'); //Log entry
					$current = file_get_contents($file);
					$current .= '*********************************************************************' . "\n";
					$current .= '******************* Removed disabled product List *******************' . "\n";
					$current .= '************* Created on ' . date("jS F Y h:i:s A") . ' *************' . "\n";
					$current .= '*********************************************************************' . "\n";
					$current .= 'Removed at                       Product Name and Product SKU' . "\n";
					file_put_contents($file, $current);
				}
				
				try {
					Mage::getModel("catalog/product")->load( $prodID  )->delete(); //Delete current disabled product
					Mage::log($name . ' deleted successfully.', null, 'RemoveDisableProducts.log'); //Log entry 
					echo $name . ' deleted successfully.</br>';
					$current = file_get_contents($file);
					$current .= date("jS F Y h:i:s A") .'   '. $name . ' - ' . $sku . "\n";
					file_put_contents($file, $current);
				} catch(Exception $e) {
					Mage::log('Error: Unable to delete ' . $name, null, 'RemoveDisableProducts.log'); //Error log 
					echo 'Error: Unable to delete ' . $name . '</br>';
				}
			if( $counter >= 500) { break; }
		}
	}
?>
