# Currency Conversion

## Setup
  - Run `createcurrenciestable.sql` to create currencies table
  - Change database parameters to your database

## Data Format

 XML data is expected in this format: 

 ```
 <response>
    <conversion>
        <currency>JPY</currency>
        <rate>0.013125</rate>
    </conversion>>
  </response>
 ```


 ## Use

 ### `getData`
   Gets the XML data
   ```
   $currency = new CurrencyConversion;
   $data = $currency->getData();
   ```

### `updateData`
   Parses the returned XML and inserts/updates it to the `currencies` table
   ```
   $currency = new CurrencyConversion;
   $data = $currency->getData();
   $currency->updateData($data);
   ```

### `convert`
  Using the `currencies` table, converts input currency to USD
  ```
  $currency = new CurrencyConversion;
  $result = $currency->convert('JPY 5000');
  //$result is USD 65.63 
  ```
 

### `convertMultiple`
  Using the `currencies` table, converts an array of input currencies to an array of USD
  ```
  $currency = new CurrencyConversion;
  $data_array = array('JPY 5000','CZK 62.5');
  $result = $currency->converMultiple($data_array);
  //$result is array('USD 65.63','USD 3.24')
  ```
