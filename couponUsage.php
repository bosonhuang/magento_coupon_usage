<?php
/*
 *
 * NOTICE OF LICENSE
 *
 * Ther is no license at all. Free to use.
 * Credit to Boson @ bosonhuang.com
 *
 *
 * USAGE
 *
 * This script generates table-viewed coupon usage output on screen.
 * 
 * 1. Put this file to root of your Magento installation folder.
 * 2. Type in URL http://www.example.com/couponUsage.php?coupon=YourCouponCodeHere
 * 3. DO NOT CHANGE OR MODIFY OTHER CODES.
 * 
 */
 
// define Magento root path & get Mage class 
define('MAGENTO', realpath(dirname(__FILE__)));
require_once MAGENTO . '/app/Mage.php';
Mage::app();

umask(0);

/* $couponCode = 'AbCdE12345'; */
$couponCode = htmlspecialchars($_GET['coupon']);

// get coupon model
$coupon     = Mage::getModel('salesrule/coupon')->loadByCode($couponCode);
$couponID   = $coupon->getId();

if($coupon->getCode() == $couponCode && $couponID) {
  $resource = Mage::getResourceModel('salesrule/coupon_usage');
  $read     = $resource->getReadConnection();
  $select   = $read->select()
              ->from($resource->getMainTable())
              ->where('coupon_id = ?', $couponID);
  $result   = $read->fetchAll($select);
  
  if($result > 0) {
    /*
     * Define table output head row elements,
     *
     * @var array
     */
    $headArray = array(
      '#',
      'Customer Name',
      'Customer Email',
      'Time of Used'
    );
    
    $itemArray = array();
    $index     = 1;
    
    foreach($result as $couponUsage) {
      // get customer id and time of usage per customer
      $customerId       = $couponUsage['customer_id'];
      $usagePerCustomer = $couponUsage['times_used'];
      
      // get customer name and email address
      $customer         = Mage::getModel('Customer/Customer')->load($customerId);
      $customerName     = $customer->getName();
      $customerEmail    = $customer->getEmail();
      
      /*
       * Define table output content row elements
       *
       * @var array
       */
      $couponArray      = array(
        $index,
        $customerName,
        $customerEmail,
        $usagePerCustomer
      );
      
      // add each product output array to table content row array
      array_push($itemArray, $couponArray);
        
      $index++;
    }
    
    // output configurable product table
    echo getTable($headArray, $itemArray);
  } else {
    echo 'Coupon: ' . $couponCode. ' has never been used.';
  }
} else {
  echo $couponCode. ' does not exit or set properly.';
}

/*
 * Generate table output
 *
 * @param array $headArray - table head elements
 * @param array $itemArray - table content elements
 * @return string
 */
function getTable($headArray, $itemArray) {
  $countHead   = itemCount($headArray);
  $countItem   = itemCount($itemArray);
  $tableString = '<table>';
  
  for($i = 0; $i < $countHead; $i++) {
    $tableString .= '<col width="auto">';
  }
  
  if($countHead > 0 && $countItem > 0) {
    $tableString .= getTableRow($headArray, 'th', $countHead);
    $tableString .= getTableRow($itemArray, 'td', $countHead);
  } else
    $tableString .= getTableRow(array());
  
  $tableString .= '</table>';
  
  return $tableString;
}

/*
 * Generate table rows output
 *
 * @param array $arrayList - table row elements
 * @param String $flag     - table row HTML tags
 * @param int $arrayCount  - table row elements size
 * @return string
 */
function getTableRow($arrayList, $flag = '', $arrayCount = 0) {
  if(empty($flag)) {
    $startTag = '<td align="right">';
    $endTag   = '</td>';
  } elseif($flag === 'th') {
    $startTag = '<th align="right">';
    $endTag   = '</th>';
  } elseif($flag === 'td') {
    $startTag = '<td align="right">';
    $endTag   = '</td>';
  }
  
  $tableHeadString = '';
  // output table head
  if(itemCount($arrayList) == $arrayCount) {
    $tableHeadString .= '<tr>';
    foreach($arrayList as $arrayItem) {
      $tableHeadString .= $startTag . $arrayItem . $endTag;
    }
    $tableHeadString .= '</tr>';
  }
  // output table content
  else {
    foreach($arrayList as $listItem) {
      $tableHeadString .= '<tr>';
      if(itemCount($listItem) == $arrayCount) {
        foreach($listItem as $item) {
          $tableHeadString .= $startTag . $item . $endTag;
        }
      }
      $tableHeadString .= '</tr>';
    }
  }
  
  return $tableHeadString;
}

/*
 * count row element size
 *
 * @param array $arrayList - table row elements
 * @return int
 */
function itemCount($arrayList) {
  if(is_array($arrayList) && !empty($arrayList))
    return count($arrayList);
  else
    return 0;
}
