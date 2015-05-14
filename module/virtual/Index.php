<?php
namespace Module\virtual;
class Index {
	public function __construct(){
		if(SELLER_LANG=='de-ge'){
			if(isset($_GET['transactionID']) && isset($_GET['transactionAmount'])){//通过img访问的地址
				
				//! TradeTracker Conversion-Tag.
				// Create session.
				session_start();
				
				// Define parameters.
				$campaignID = isset($_GET['campaignID']) ? $_GET['campaignID'] : '';
				$productID = isset($_GET['productID']) ? $_GET['productID'] : '';
				$conversionType = isset($_GET['conversionType']) ? $_GET['conversionType'] : '';
				$useHttps = isset($_GET['https']) && $_GET['https'] === '1';
				
				// Get tracking data from the session created on the redirect-page.
				$trackingData = isset($_SESSION['TT2_' . $campaignID]) ? $_SESSION['TT2_' . $campaignID] : '';
				$trackingType = '1';
				
				// If tracking data is empty.
				if (empty($trackingData))
				{
					// Get tracking data from the cookie created on the redirect-page.
					$trackingData = isset($_COOKIE['TT2_' . $campaignID]) ? $_COOKIE['TT2_' . $campaignID] : '';
					$trackingType = '2';
				}
				
				// Set transaction information.
				$transactionID = isset($_GET['transactionID']) ? $_GET['transactionID'] : ''; // Transaction identifier.
				$transactionAmount = isset($_GET['transactionAmount']) ? $_GET['transactionAmount'] : ''; // Transaction amount.
				$quantity = isset($_GET['quantity']) ? $_GET['quantity'] : ''; // Quantity (optional).
				$email = isset($_GET['email']) ? $_GET['email'] : ''; // Customer e-mail address if available (optional).
				$descriptionMerchant = isset($_GET['descrMerchant']) ? $_GET['descrMerchant'] : ''; // Transaction details for merchants (optional).
				$descriptionAffiliate = isset($_GET['descrAffiliate']) ? $_GET['descrAffiliate'] : ''; // Transaction details for affiliates (optional).
				
				// Set track-back URL.
				$trackBackURL = ($useHttps ? 'https' : 'http') . '://' . ($conversionType === 'lead' ? 'tl' : 'ts') . '.tradetracker.net/?cid=' . $campaignID . '&pid=' . $productID . '&data=' . urlencode($trackingData) . '&type=' . $trackingType . '&tid=' . urlencode($transactionID) . '&tam=' . urlencode($transactionAmount) . '&qty=' . urlencode($quantity) . '&eml=' . urlencode($email) . '&descrMerchant=' . urlencode($descriptionMerchant) . '&descrAffiliate=' . urlencode($descriptionAffiliate);
				
				// Register transaction.
				header('Location: ' . $trackBackURL);

			}else{//从平台第一次过来的访问
			
				//! Tradetracker Redirect-Page.
				// Set domain name on which the redirect-page runs, WITHOUT "www.".
				$domainName = 'milanoo.com';

				// Set the P3P compact policy.
				header('P3P: CP="ALL PUR DSP CUR ADMi DEVi CONi OUR COR IND"');

				// Define parameters.
				$canRedirect = true;

				// Set parameters.
				if (isset($_GET['campaignID']))
				{
					$campaignID = $_GET['campaignID'];
					$materialID = isset($_GET['materialID']) ? $_GET['materialID'] : '';
					$affiliateID = isset($_GET['affiliateID']) ? $_GET['affiliateID'] : '';
					$redirectURL = isset($_GET['redirectURL']) ? $_GET['redirectURL'] : '';
					$reference = '';
				}
				else if (isset($_GET['tt']))
				{
					$trackingData = explode('_', $_GET['tt']);

					$campaignID = isset($trackingData[0]) ? $trackingData[0] : '';
					$materialID = isset($trackingData[1]) ? $trackingData[1] : '';
					$affiliateID = isset($trackingData[2]) ? $trackingData[2] : '';
					$reference = isset($trackingData[3]) ? $trackingData[3] : '';

					$redirectURL = isset($_GET['r']) ? $_GET['r'] : '';
				}
				else
					$canRedirect = false;

				if ($canRedirect)
				{
					// Calculate MD5 checksum.
					$checkSum = md5('CHK_' . $campaignID . '::' . $materialID . '::' . $affiliateID . '::' . $reference);

					// Set session/cookie arguments.
					$cookieName = 'TT2_' . $campaignID;
					$cookieValue = $materialID . '::' . $affiliateID . '::' . $reference . '::' . $checkSum . '::' . time();
					
					// Create tracking cookie.
					setcookie($cookieName, $cookieValue, (time() + 31536000), '/', !empty($domainName) ? '.' . $domainName : null);
					
					// Create tracking session.
					session_start();

					// Set session data.
					$_SESSION[$cookieName] = $cookieValue;

					// Set track-back URL.
					$trackBackURL = 'http://tc.tradetracker.net/?c=' . $campaignID . '&m=' . $materialID . '&a=' . $affiliateID . '&r=' . urlencode($reference) . '&u=' . urlencode($redirectURL);

					// Redirect to TradeTracker.
					header('Location: ' . $trackBackURL, true, 301);
				}
			}
			
		}
	}
}