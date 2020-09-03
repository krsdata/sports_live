<?php
namespace davidjeddy;

use yii\base\Component;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\RedirectUrls;
use PayPal\Rest\ApiContext;

class Paypal extends Component {
    
    //region Mode (production/development)
    const MODE_SANDBOX = 'sandbox';
    const MODE_LIVE    = 'live';
    //endregion
    //region Log levels
    /*
     * Logging level can be one of FINE, INFO, WARN or ERROR.
     */
    const LOG_LEVEL_FINE  = 'FINE';
    const LOG_LEVEL_INFO  = 'INFO';
    const LOG_LEVEL_WARN  = 'WARN';
    const LOG_LEVEL_ERROR = 'ERROR';
    //endregion
    // class properties
    /**
     * [$config description]
     * @var array
     */
    public $config = [];
    public $clientId;
    public $clientSecret;
    public $isProduction;
    
    /** @var ApiContext */
    private $_apiContext = null;
    /**
     * @setConfig 
     * _apiContext in init() method
     */
    public function init()
    {
        $this->config = \Yii::$app->components['paypal'];
        // ### Api context
        // Use an ApiContext object to authenticate
        // API calls. The clientId and clientSecret for the
        // OAuthTokenCredential class can be retrieved from
        // developer.paypal.com
        $this->_apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->config['clientId'],
                $this->config['clientSecret']
            )
        );
        // Set log file name
//        $logFileName = \Yii::getAlias($this->config['log']['FileName']);
//        if ($logFileName) {
//            if (!file_exists($logFileName)) {
//                if (!touch($logFileName)) {
//                    throw new ErrorException('Can\'t create paypal.log file at: ' . $logFileName);
//                }
//            }
//        }
        return $this->_apiContext;
    }
    
    public function execPayment(array $pdata) {
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $amount = new Amount();
        $amount->setCurrency('INR')
        ->setTotal($pdata['amount']);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
        ->setDescription($pdata['description'])
        ->setInvoiceNumber(uniqid());

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($pdata['return_url'])
        ->setCancelUrl($pdata['return_url']);

        $payment = new Payment();
        $payment->setIntent("sale")
        ->setPayer($payer)
        ->setRedirectUrls($redirectUrls)
        ->setTransactions(array($transaction));

        $payment->create($this->_apiContext);
        return $payment;
    }

    public function getPaymentInfo($payment_id) {
        $payment = new Payment();
        $pInfo=$payment->get($payment_id, $this->_apiContext);
        return $pInfo;
    }
    
}
