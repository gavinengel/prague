inventory-api
=============

A basic one page REST app, using raw javascript/json/ajax exchanges with a thin LAMP stack on OpenShift.  The interface was created with mobile in mind.

A copy of the inventory api blueprint is included in inventoryblueprint.md  




#Summary

Inventory relies primarily on client side JavaScript to send HTTP GET, POST, PUT, DELETE requests to a PHP/MySQL backend.  

Client to server communications are sent through AJAX via x-www-form-urlencoded requests which are returned with PHP-generated JSON representing items in the database.  JSON.parse handles the responses.

On the server side, PHP reads the request parameters and performs the requested operation on MySQL using prepared PDO statments and uses json_encode to format the response data.

##Design Considerations

* The code is intentially raw, using only JavaScript/AJAX.

* Security employed is at the datbase interface level using PDO prepared statements and variable binding.  

* Decision was made to use PUT and DELETE methods as references for future implementations.  
 
* The cards interface was used in the style of Google Keep -- the boxes are designed to easily include thumbnail images or other properties for whatever kinds of objects might be stored.  

* The interface is meant to be a request generator and response processor.  The rendering of JSON responses is a stub for future development.  
 #Samples
 
 ## Client JavaScript/AJAX Request and Response
 
...

    var hr = new XMLHttpRequest();  
    var url = 'items/';
    hr.open("GET", url, true);  
    hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");  
    hr.onreadystatechange = function(){  
    if (hr.readyState == 4 && hr.status == 200){  
      
     var r = JSON.parse(hr.responseText);  

    hr.send();  
...


 
 ### A sample JSON response looks like the following: 
  
        {"311":{"item_id":"311","item_name":"coaster","quantity":"3","price":"1","item_desc":"picture of rhino","ts":"2014-08-15 09:55:45","url":"inventory-ktleary.rhcloud.com/items/311/"},
        {"312":{"item_id":"312","item_name":"chocolate bar","quantity":"10001","price":"333","item_desc":"dark 85%","ts":"2014-08-15 10:24:29","url":"inventory-ktleary.rhcloud.com/items/312/"},



## Server PHP Request Handling and Response

### Request

    if ($method == "POST") {
    //get values
    if (isset($_POST['name'])) {  
     $name=$_POST['name'];
    }
   
   ... 
   
    $last_id = createItem($name, $description);

### Database communications

    function createItem($name, $description){
    $dbh = connectDB();
   
    // prepare and insert item into items table (before item props)

    $stmt = $dbh->prepare('
        INSERT INTO items 
            (item_name, item_desc) 
        VALUES 
            (:item_name, :item_desc)
    ');
    $stmt->bindParam(':item_name', $name);
    $stmt->bindParam(':item_desc', $description);
    $stmt->execute();
    $last_id = $dbh->lastInsertId("item_id");

    return $last_id;
    }
      

### Response

'''

    //format response and add specified href  
    foreach ($data as $row){  
    $currentid = $row["item_id"];  
    $currentname = $row["item_name"];  
    $price = $row["price"];  
    $quantity = $row["quantity"];  
    $ts = $row["ts"];  
    $item_desc = $row["item_desc"];  
    //$currenturl = "$row->url";  
    //$currentimage = "$row->image";  
    $array[$currentid] = array('item_id'=>$currentid,'item_name'=>$currentname, 
    'quantity'=>$quantity, 'price'=>$price, 'item_desc'=>$item_desc, 'ts'=>$ts,
    'url'=> $_SERVER['HTTP_HOST']."/items/".$currentid."/");
    }  
    //send all results in json  
    header("Content-Type: application/json");  
    echo  json_encode($array);  
    '''
### HTML Form Card

    <form id="create" class="content float-left">
    <fieldset>
      <div class='content float-left'>
        <div class='cardform'>
          <div class='cardformtitle'>
            Create Item
          </div>
          <div class='cardformbody'>
            <label for="addname">Item Name*:</label>
            <input type="text" id="addname" name="addname" value="">
          </div>
          <div class='cardformbody'>
            <label for="adddescription">Description:</label> 
            <input type="text" id="adddescription" name="adddescription" value="">
          </div>
          <div class="cardformbody">
            <label>Quantity*:</label>
            <input type="text" id="addquantity" name="addquantity" value="" required>
          </div>
          <div class="cardformbody">
            <label for="addprice">Unit Price:</label>
            <input type="text" id="addprice" name="addprice" value="">
          </div>
          <div class='cardfooter'>
            <input type="button" class="button-teal" value="Create" onClick="javascript:addItem();">&nbsp;
            <input type="button" class="button-red" value="Cancel" onClick="javascript:document.getElementById('create').style.display='none'; return false;">
          </div>
        </div>
      </div>
      </fieldset>
      </form>

# Conclusion
This is a bare bones api which can be adapted for various frameworks and design requirements.
