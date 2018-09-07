<?php

class CurrencyConversion {

  /**
   * XML source URL
   *
   * @var string $url
   */
  protected $url = 'https://wikitech.wikimedia.org/wiki/Fundraising/tech/Currency_conversion_sample?ctype=text/xml&action=raw';


  //Database part should be separated out in actual application

  /**
   * DB connection information
   * 
   */
   protected   $hostname = '';
   protected   $db   = '';
   protected   $username = '';
   protected   $password = '';
   protected   $charset = 'utf8mb4';

  /**
   * Set up DB connection
   * 
   */
  public function __construct() {
      $dsn = "mysql:host=$this->hostname;dbname=$this->db;port=3306;charset=$this->charset";
      $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
      
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }         
  }

  /**
   * Get the XML data
   *
   * @return string $rawdata
   */
  public function getData() {
    //get the data from the url
    $rawdata = file_get_contents($this->url);

    return $rawdata;
  }

  /**
   * Update the currency data
   *
   * @param string $rawdata
   * @return void
   */
  public function updateData($rawdata) {
    $xml = new SimpleXMLElement($rawdata);
    //loop through the data
    foreach($xml->conversion as $data) {
      //check if the data is there already
      $select = "SELECT COUNT(currency) as count FROM currencies WHERE currency=:currency";
      $statement = $this->pdo->prepare($select);
      $statement->execute(['currency'=>$data->currency]);
      $result = $statement->fetch();

      if ($result['count'] == 0) {
        //insert the data
        $query = "INSERT INTO `currencies` (`currency`,`rate`,`created_at`) VALUES (:currency,:rate,NOW())";
      } else {
        //update the data
        $query = "UPDATE `currencies` SET rate=:rate, created_at=NOW() WHERE currency=:currency";
      }

      $statement = $this->pdo->prepare($query);
      $statement->execute(['currency'=>$data->currency,'rate'=>$data->rate]);
    }
  }

  /**
   * Convert currency
   *
   * @param string $from_currency
   * @return string $usd
   */
  public function convert($from_currency) {
    //input is "JPY 5000"
    //break into pieces
    $pieces = explode(' ',$from_currency);

    //check for bad inputs
    //depending on how this is used, could have a friendly error message
    if (count($pieces)!= 2) { 
      //return print 'Please enter a currency and amount. For example "JPY 5000"';
      return;
    }

    $from_currency = $pieces[0];
    $amount = $pieces[1];

    //grab from from db
    $select = "SELECT * FROM `currencies` WHERE `currency` = :from_currency";
    $statement = $this->pdo->prepare($select);
    $statement->execute(['from_currency'=>$from_currency]);
    $result = $statement->fetch();

    $usd = round($result['rate'] * $amount,2);
    $usd = 'USD '.$usd;
    
    return $usd;
  }

  /**
   * Convert currency
   *
   * @param array $array
   * @return array $result
   */
  public function convertMultiple($array) {
    //convert all currencies in array
    foreach($array as $currency) {
      $result[] = $this->convert($currency);
    }

    return $result;
  }

  /**
   * Test getData()
   *
   */  
  public function getDataTest() {
    $rawData = $this->getData();

    print $rawData;
  }

  /**
   * Test updateData()
   *
   */  
  public function updateDataTest() {
    $rawData = $this->getData();
    $this->updateData($rawData);
  }

  /**
   * Test convert()
   *
   */  
  public function convertTest() {
    $result = $this->convert('JPY 5000');

    print $result;
  }

  /**
   * Test convertMultiple()
   *
   */  
  public function convertMultipleTest() {
    $array = array('JPY 5000','CZK 62.5');
    $result = $this->convertMultiple($array);
    
    print_r($result);
  }
}
