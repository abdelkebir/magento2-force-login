<?php
namespace Godogi\ForceLogin\Observer;
use Magento\Framework\Event\ObserverInterface;
class CheckForce implements ObserverInterface
{
	protected $_customerSession;
	protected $_adminSession;
	protected $_messageManager;
	protected $_url;
	protected $_responseFactory;
	protected $_logger;
	public function __construct(
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\UrlInterface $url,
		\Magento\Framework\App\ResponseFactory $responseFactory,
      \Magento\Customer\Model\Session $customerSession,
      \Magento\Backend\Model\Auth\Session $adminSession,
      \Psr\Log\LoggerInterface $logger
	)
	{
		$this->_messageManager = $messageManager;
		$this->_url = $url;
		$this->_responseFactory = $responseFactory;
    	$this->_customerSession = $customerSession;
    	$this->_adminSession = $adminSession;
		$this->_logger = $logger;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$actionName = $observer->getEvent()->getRequest()->getFullActionName();
		/* This feature is not available if you are logged in to the back end */
		if($this->_adminSession->isLoggedIn()){
			return $this;
		}
		/* If you are logged in already we should be happy :) */
		if(!$this->_customerSession->isLoggedIn()) {
      	$this->_logger->notice('Godogi_ForceLogin'.$actionName);
      	/* Some pages should not be restricted */
			if($actionName == 'customer_account_create' || $actionName == 'customer_account_login' || $actionName == 'customer_account_createpost' || $actionName == 'customer_account_loginPost' || $actionName == 'customer_section_load') {
    			return $this;
			}else{
				$this->_messageManager->addWarningMessage('Kindly login to your account before proceeding.');
				$redirectionUrl = $this->_url->getUrl('customer/account/login');
        		$this->_responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
        		exit;	
			}
		}
		return $this;
	}
}